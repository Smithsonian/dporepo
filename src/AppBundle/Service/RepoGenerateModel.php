<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use AppBundle\Service\RepoValidateData;
use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Service\RepoProcessingService;
use AppBundle\Utils\AppUtilities;

class RepoGenerateModel implements RepoGenerateModelInterface {

  /**
   * @var object $u
   */
  public $u;

  /**
   * @var object $tokenStorage
   */
  public $tokenStorage;

  /**
   * @var object $kernel
   */
  public $kernel;

  /**
   * @var string $project_directory
   */
  private $project_directory;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * @var object $conn
   */
  private $conn;

  /**
   * @var object $repoValidate
   */
  private $repoValidate;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var object $processing
   */
  private $processing;

  /**
   * @var int $user_id
   */
  private $user_id;

  /**
   * Constructor
   * @param object  $kernel  Symfony's kernel object
   * @param string  $uploads_directory  Uploads directory path
   * @param string  $processing  Repo Processing Service class.
   * @param string  $conn  The database connection
   */
  public function __construct(TokenStorageInterface $tokenStorage, KernelInterface $kernel, string $uploads_directory, RepoProcessingService $processing, \Doctrine\DBAL\Connection $conn)
  {
    $this->u = new AppUtilities();
    $this->tokenStorage = $tokenStorage;
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $uploads_directory) : $uploads_directory;
    $this->conn = $conn;
    $this->repoValidate = new RepoValidateData($conn);
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->processing = $processing;

    // Get user data.
    if( method_exists($this->tokenStorage, 'getUser') ) {
      $user = $this->tokenStorage->getToken()->getUser();
      $this->user_id = $user->getId();
    } else {
      $this->user_id = 0;
    }
  }

  /**
   * @param string $uuid The directory (UUID) which contains model(s) to be validated and processed.
   * @param string $recipe_name The processing recipe name.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function generateModelAssets($uuid = null, $recipe_name = null, $filesystem)
  {

    $data = array();
    $job_status = 'metadata ingest in progress';
    $job_failed_message = 'The job has failed. Exiting model assets generation process.';

    // If uuid doesn't exist, then this is being called by a scheduled task / cron job to run a 'web-multi' recipe (multi-level web assets).
    if (empty($uuid)) {
      // Look for:
      // 1) a 'workflow' which has a 'step_type' of 'auto'
      // 2) a 'workflow' which has a 'step_id' of 'web-multi'
      // 3) a 'processing_job' which has a 'step' of 'created'.
      $workflow = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'workflow',
          'fields' => array(
            array(
              'table_name' => 'workflow',
              'field_name' => 'ingest_job_uuid',
            ),
          ),
          'limit' => 1,
          // Joins
          'related_tables' => array(
            array(
              'table_name' => 'processing_job',
              'table_join_field' => 'processing_service_job_id',
              'join_type' => 'LEFT JOIN',
              'base_join_table' => 'workflow',
              'base_join_field' => 'processing_job_id',
            )
          ),
          'search_params' => array(
            0 => array('field_names' => array('workflow.step_type'), 'search_values' => array('auto'), 'comparison' => '='),
            1 => array('field_names' => array('workflow.step_id'), 'search_values' => array('web-multi'), 'comparison' => '='),
            2 => array('field_names' => array('processing_job.state'), 'search_values' => array('created'), 'comparison' => '='),
          ),
          'search_type' => 'AND',
          'omit_active_field' => true,
        )
      );

      if (empty($workflow)) {
        $return['errors'][] = 'No workflow jobs found. All workflow jobs are completed.';
        return $return;
      }

      if (!empty($workflow)) {
        $uuid = $workflow[0]['ingest_job_uuid'];
        $recipe_name = 'web-multi';
      }
    }

    // Absolute local path.
    $path = $this->project_directory . $this->uploads_directory . $uuid;
    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid, 'generateModelAssets'));
    // Set the workflow step, qc-hd or qc-web.
    $workflow_step = ($recipe_name === 'web-hd') ? 'qc-hd' : 'qc-web';

    // Throw an error if the job record doesn't exist.
    if (empty($job_data)) {
      $return['errors'][] = 'The Job record doesn\'t exist - generateModelAssets() - ' . $uuid;
      return $return;
    }

    // Don't perform the model generation if the job_status has been set to 'failed'.
    if ($job_data['job_status'] === 'failed') {
      $return['errors'][] = $job_failed_message;
      return $return;
    }

    if (!is_dir($path)) {
      $return['errors'][] = 'Target directory not found - ' . $path;
      return $return;
    }

    if (is_dir($path)) {

      // Run the processing job.
      if ($recipe_name === 'web-hd') {
        $processing_job = $this->runWebHd($path, $job_data, $recipe_name, $filesystem);
      } else {
        $processing_job = $this->runWebMulti($path, $job_data, $recipe_name, $filesystem);
      }

      // Check the job's status to insure that the job_status hasn't been set to 'failed'.
      $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid, 'generateModelAssets'));
      // If the job status has been set to 'failed', return with the error message.
      if ($job_data['job_status'] === 'failed') {
        $return['errors'][] = $job_failed_message;
        return $return;
      }

      // Continue only if job_ids are returned.
      if (!empty($processing_job) && ($processing_job[0]['return'] === 'success')) {

        // Check to see if jobs are running. Don't pass "Go" until all jobs are finished.
        while ($this->processing->are_jobs_running( array($processing_job[0]['workflow']['processing_job_id']) )) {
          $this->processing->are_jobs_running( array($processing_job[0]['workflow']['processing_job_id']) );
          sleep(5);
        }

        // Retrieve all of the logs produced by the processing service.
        $data = $this->processing->get_processing_assets($filesystem, $processing_job[0]['workflow']['processing_job_id']);

        // Update the workflow record. Set the step state to NULL and step-id to qc-hd or qc-web.
        $query_params = array(
          'workflow_id' => $processing_job[0]['workflow']['workflow_id'],
          'step_state' => NULL,
          'step_id' => $workflow_step,
          'step_type' => 'manual',
        );
        $this->repo_storage_controller->execute('updateWorkflow', $query_params);
      }

    }

    // Update the 'job_status' in the 'job' table accordingly (only for the web-hd recipe).
    if (!empty($job_status) && ($recipe_name === 'web-hd')) {
      $this->repo_storage_controller->execute('setJobStatus',
        array(
          'job_id' => $job_data['uuid'], 
          'status' => $job_status, 
          'date_completed' => date('Y-m-d H:i:s')
        )
      );
    }

    return $data;
  }

  /**
   * @param string $path The directory which contains model(s) to be processed.
   * @param array $job_data The repository job's data.
   * @param string $recipe_name The processing recipe name.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function runWebHd($path = null, $job_data = array(), $recipe_name = null, $filesystem)
  {

    $data = $model_types = array();
    $models_csv = null;

    if (!empty($path) && !empty($job_data)) {

      // Parse the models.csv to get model file names and model purposes.
      $finder = new Finder();
      $finder->name('models.csv');
      $finder->in($path);
      $i = 0;
      foreach ($finder as $file) {
        $models_csv = $file->getContents();
      }

      if (!empty($models_csv)) {
        
        $model_types = $this->getModelsAndModelPurpose($models_csv);

        // Traverse the local path and validate models.
        $finder = new Finder();
        $finder->path('data')->name('/\.obj|\.ply$/');
        $finder->in($path);
        $i = 0;
        foreach ($finder as $file) {

          // Make sure the asset is a file and not a directory (directories are automatically detected by WebDAV).
          // Make sure that the model_purpose is master.
          if ($file->isFile() && array_key_exists($file->getFilename(), $model_types) && ($model_types[$file->getFilename()] === 'master')) {

            $file_path = str_replace($this->project_directory . $this->uploads_directory, DIRECTORY_SEPARATOR, $file->getPathname());

            // Get the ID of the recipe, so it can be passed to processing service's job creation endpoint (post_job).
            $recipe = $this->processing->getRecipeByName( $recipe_name );

            // Error handling
            if (isset($recipe['error']) && !empty($recipe['error'])) $data[$i]['errors'][] = $recipe['error'];

            if (!isset($recipe['error'])) {

              // Create a timestamp for the procesing job name.
              $job_name = str_replace('+00:00', 'Z', gmdate('c', strtotime('now')));
              // Post a new job.
              $params = array(
                'highPolyMeshFile' => $file->getFilename()
              );
              $result = $this->processing->postJob($recipe['id'], $job_name, $params);

              // Error handling
              if ($result['httpcode'] !== 201) $data[$i]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];

              if ($result['httpcode'] === 201) {

                // Get the processing_job data.
                $processing_job = $this->processing->getJobByName($job_name);

                // Error handling
                if (isset($processing_job['error']) && !empty($processing_job['error'])) $data[$i]['errors'][] = $processing_job['error'];

                // Log processing_job data to the metadata storage
                if (isset($processing_job['error']) && empty($processing_job['error'])) {

                  $processing_job_id = $this->repo_storage_controller->execute('saveRecord', array(
                    'base_table' => 'processing_job',
                    'user_id' => $job_data['created_by_user_account_id'],
                    'values' => array(
                      'ingest_job_uuid' => $job_data['uuid'],
                      'processing_service_job_id' => $processing_job['id'],
                      'recipe' =>  $processing_job['recipe']['name'],
                      'job_json' => json_encode($processing_job),
                      'state' => $processing_job['state'],
                      'asset_path' => $file->getPathname(),
                    )
                  ));

                  // Create the workflow.
                  $query_params = array(
                    'ingest_job_uuid' => $job_data['uuid'],
                    'processing_job_id' => $processing_job['id'],
                    'workflow_recipe_id' => 'test_v1',
                    'step_state' => 'processing',
                    'user_id' => $job_data['created_by_user_account_id'],
                  );

                  // TODO: error handling
                  $data[$i] = $this->repo_storage_controller->execute('createWorkflow', $query_params);
                }

                // The external path - on the processing service side.
                $path_external = $processing_job['id'] . '/' . $file->getFilename();

                // Transfer the file to the processing service via WebDAV.
                try {

                  $stream = fopen($file->getPathname(), 'r+');
                  $filesystem->writeStream($path_external, $stream);
                  // Before calling fclose on the resource, check if it’s still valid using is_resource.
                  if (is_resource($stream)) fclose($stream);

                  // Now that the file has been transferred, go ahead and run the processing job.
                  $result = $this->processing->runJob($processing_job['id']);

                  // Error handling
                  if ($result['httpcode'] !== 202) $data[$i]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];

                }
                // Catch the error.
                catch(\League\Flysystem\FileExistsException | \League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
                  $data[$i]['errors'][] = $e->getMessage();
                }

              }

            }

            if (!empty($data[$i]['errors'])) {
              // Log the errors to the database.
              $this->repoValidate->logErrors(
                array(
                  'job_id' => $job_data['job_id'],
                  'user_id' => $job_data['created_by_user_account_id'],
                  'job_log_label' => 'Validate Model',
                  'errors' => $data[$i]['errors'],
                )
              );
            }

            $i++;
          }

        }

      }

    }

    return $data;
  }

  /**
   * @param string $path The directory which contains model(s) to be processed.
   * @param array $job_data The repository job's data.
   * @param string $recipe_name The processing recipe name.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function runWebMulti($path = null, $job_data = array(), $recipe_name = null, $filesystem)
  {

    $data = array();

    // $this->u->dumper($path,0);
    // $this->u->dumper($job_data,0);
    // $this->u->dumper($recipe_name);

    if (!empty($path) && !empty($job_data)) {

      // Query the workflow table for workflow data.
      $workflow = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'workflow',
          'fields' => array(),
          'limit' => 1,
          'search_params' => array(
            0 => array('field_names' => array('workflow.ingest_job_uuid'), 'search_values' => array($job_data['uuid']), 'comparison' => '='),
          ),
          'search_type' => 'AND',
          'omit_active_field' => true,
        )
      );

      if (!empty($workflow)) {

        // Structure the workflow array to match the output of $this->repo_storage_controller->execute('createWorkflow'
        $data[0]['workflow'] = $workflow[0];

        // Get the processing_service_job_id and model file path (asset_path) from the processing_job metadata storage.
        $processing_job = $this->repo_storage_controller->execute('getRecords', array(
            'base_table' => 'processing_job',
            'fields' => array(
              array(
                'table_name' => 'processing_job',
                'field_name' => 'processing_service_job_id',
              ),
              array(
                'table_name' => 'processing_job',
                'field_name' => 'asset_path',
              ),
            ),
            'limit' => 1,
            'search_params' => array(
              0 => array('field_names' => array('processing_job.ingest_job_uuid'), 'search_values' => array($workflow[0]['ingest_job_uuid']), 'comparison' => '='),
              1 => array('field_names' => array('processing_job.recipe'), 'search_values' => array('web-multi'), 'comparison' => '='),
              2 => array('field_names' => array('processing_job.state'), 'search_values' => array('created'), 'comparison' => '='),
            ),
            'search_type' => 'AND',
            'omit_active_field' => true,
          )
        );

        if (count($processing_job) > 1) {
          $data[0]['errors'][] = 'More than one processing job found in metadata storage. Please contact the administrator for assistance.';
        }

        // If the model file path is found, continue.
        if (!empty($processing_job)) {

          // $directory = pathinfo($path[0]['asset_path'], PATHINFO_DIRNAME);
          $full_file_name = pathinfo($processing_job[0]['asset_path'], PATHINFO_BASENAME);

          // The external path - on the processing service side - on the processing service side.
          $path_external = $processing_job[0]['processing_service_job_id'] . '/' . $full_file_name;

          // Transfer the file to the processing service via WebDAV.
          try {

            $stream = fopen($processing_job[0]['asset_path'], 'r+');
            $filesystem->writeStream($path_external, $stream);
            // Before calling fclose on the resource, check if it’s still valid using is_resource.
            if (is_resource($stream)) fclose($stream);

            // Now that the file has been transferred, go ahead and run the processing job.
            $result = $this->processing->runJob($processing_job[0]['processing_service_job_id']);

            // Error handling
            if ($result['httpcode'] !== 202) {
              $data[0]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];
              // Matching the output of $this->repo_storage_controller->execute('createWorkflow'
              $data[0]['return'] = 'error';
            } else {
              // Matching the output of $this->repo_storage_controller->execute('createWorkflow'
              $data[0]['return'] = 'success';
            }

          }
          // Catch the error.
          catch(\League\Flysystem\FileExistsException | \League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
            $data[0]['errors'][] = $e->getMessage();
          }

        }
 
      }

    }

    return $data;
  }

  /**
   * @param string $models_csv The models.csv file.
   * @return array
   */
  public function getModelsAndModelPurpose($models_csv = null)
  {
    $data = array();

    if (!empty($models_csv)) {
      // Convert the CSV to JSON.
      $array = array_map('str_getcsv', explode("\n", $models_csv));
      $json = json_encode($array);
      // Convert the JSON to a PHP array.
      $json_array = json_decode($json, false);
      // Read the first key from the array, which is the column headers.
      $target_fields = $json_array[0];
      // Remove the column headers from the array.
      array_shift($json_array);
      foreach ($json_array as $key => $value) {
        // Replace numeric keys with field names.
        if (is_numeric($key)) {
          foreach ($value as $k => $v) {
            $field_name = $target_fields[$k];
            unset($json_array[$key][$k]);
            // Set the value of the field name.
            $json_array[$key][$field_name] = $v;
          }
          // If an array of data contains 1 or fewer keys, then it means the row is empty.
          // Unset the empty row, so it doesn't get inserted into the database.
          if (count(array_keys((array)$json_array[$key])) > 1) {
            $models[] = $json_array[$key];
          }
        }
      }
      // Create an array containing model file names as keys, and the model_purpose as the values.
      foreach ($models as $mk => $mv) {
        $model_file_name = pathinfo($mv['file_path'], PATHINFO_BASENAME);
        $data[$model_file_name] = $mv['model_purpose'];
      }
    }

    return $data;
  }

}
