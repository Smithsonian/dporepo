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
   * @var object $repo_import
   */
  private $repo_import;

  /**
   * @var int $user_id
   */
  private $user_id;

  /**
   * @var object $edan
   */
  private $edan;

  /**
   * Constructor
   * @param object  $kernel  Symfony's kernel object
   * @param string  $uploads_directory  Uploads directory path
   * @param string  $processing  Repo Processing Service class.
   * @param string  $conn  The database connection
   */
  public function __construct(TokenStorageInterface $tokenStorage, KernelInterface $kernel, string $uploads_directory, RepoProcessingService $processing, RepoImport $repo_import, \Doctrine\DBAL\Connection $conn)
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
    $this->repo_import = $repo_import;

    // Check for the presence of the EDAN bundle.
    $bundles = $this->kernel->getBundles();
    $this->edan = array_key_exists('DpoEdanBundle', $bundles);

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

    // Manual tests
    // Just replace the $uuid,
    // and hit the "// Faking" comments in this file
    // and in src/AppBundle/Service/RepoProcessingService.php

    // $uuid = 'E2DC1828-73B4-E97B-9148-83A7A3A99B67';
    // $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid));
    // $path = $this->project_directory . $this->uploads_directory . $uuid;
    // $data = $this->manualTestRunWebHd($uuid, $job_data, $path, $filesystem);
    // $data = $this->manualTestRunWebDerivative($uuid, $job_data, $path, $filesystem);
    // $data = $this->manualTestGetProcessingAssets($filesystem);
    // return $data;

    $data = $processing_job = array();
    $recipe_query = array();
    $job_status = 'metadata ingest in progress';
    $job_failed_message = 'The job has failed. Exiting model assets generation process.';

    if (!empty($recipe_name)) {
      $recipe_query = array($recipe_name);
    }

    // If uuid doesn't exist, then this is being called by a scheduled task / cron job to run a 'web-thumb' or 'web-multi' recipe.
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
            array(
              'table_name' => 'processing_job',
              'field_name' => 'recipe',
            ),
            array(
              'table_name' => 'processing_job',
              'field_name' => 'processing_service_job_id',
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
            1 => array('field_names' => array('processing_job.state'), 'search_values' => array('created'), 'comparison' => '='),
            2 => array('field_names' => array('workflow.step_id'), 'search_values' => $recipe_query, 'comparison' => '='),
          ),
          'search_type' => 'AND',
          'omit_active_field' => true,
        )
      );

      if (empty($workflow)) {
        $return['messages'][] = 'No workflow jobs found. All workflow jobs have completed.';
        return $return;
      }

      // If the processing job is currently running, return with a message.
      if (!empty($workflow)) {
        $is_processing_job_running = $this->processing->are_jobs_running( array($workflow[0]['processing_service_job_id']));
        if ($is_processing_job_running) {
          $return['messages'][] = 'Processing job is running (processing job ID: ' . $workflow[0]['processing_service_job_id'] . ')';
          return $return;
        }
      }

      if (!empty($workflow)) {
        $uuid = $workflow[0]['ingest_job_uuid'];
        $recipe_name = $workflow[0]['recipe'];
      }
    }

    // Absolute local path.
    $path = $this->project_directory . $this->uploads_directory . $uuid;
    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid, 'generateModelAssets'));

    // Set the workflow step.
    switch ($recipe_name) {
      case 'web-hd':
        $workflow_step = 'qc-hd';
        break;
      case 'web-thumb':
        $workflow_step = 'qc-web-thumb';
        break;
      case 'web-multi':
        $workflow_step = 'qc-web';
        break;
    }

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
      $this->repoValidate->logErrors(
        array(
          'job_id' => $job_data['job_id'],
          'uuid' => $job_data['uuid'],
          'user_id' => $job_data['created_by_user_account_id'],
          'job_log_label' => 'Process model',
          'errors' => $return['errors'],
        )
      );
      return $return;
    }

    if (is_dir($path)) {

      // Run processing job.
      switch($recipe_name) {
        // web-thumb, web-multi
        case 'web-thumb':
        case 'web-multi':
          // Run the processing job.
          $processing_job = $this->runWebDerivative($path, $job_data, $recipe_name, $filesystem);
          break;
        case 'web-hd':
          // Query the workflow table for workflow data.
          $query_params = array('ingest_job_uuid' => $job_data['uuid']);
          $workflow = $this->repo_storage_controller->execute('getWorkflows', $query_params);

          // $this->u->dumper($query_params);

          // Prevent the web-hd job from being run more than once.
          if (!empty($workflow) && !empty($workflow[0])) {
            // Set the message and return.
            $data['messages'][] = 'Workflow already exists. Ingest job ID: ' . $job_data['uuid'] . ', Processing job ID: ' . $workflow[0]['processing_job_id'];
            return $data;
          } else {
            // Run the processing job.
            $processing_job = $this->runWebHd($path, $job_data, $recipe_name, $filesystem);
          }
      }

      // Log processing job errors.
      foreach ($processing_job as $pkey => $pvalue) {
        if (array_key_exists('errors', $pvalue)) {
          $this->repoValidate->logErrors(
            array(
              'job_id' => $job_data['job_id'],
              'uuid' => $job_data['uuid'],
              'user_id' => $job_data['created_by_user_account_id'],
              'job_log_label' => 'Process model',
              'errors' => $pvalue['errors'],
            )
          );
          return $pvalue;
        }
      }

      // Check the job's status to insure that the job_status hasn't been set to 'failed'.
      $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid, 'generateModelAssets'));
      // If the job status has been set to 'failed', return with the error message.
      if ($job_data['job_status'] === 'failed') {
        $return['errors'][] = $job_failed_message;
        return $return;
      }

      // Log errors and return.
      if (array_key_exists(0, $processing_job) && array_key_exists('errors', $processing_job[0])) {
        $this->repoValidate->logErrors(
          array(
            'job_id' => $job_data['job_id'],
            'user_id' => $job_data['created_by_user_account_id'],
            'job_log_label' => 'Process model',
            'errors' => $processing_job[0]['errors'],
          )
        );
      }

      // Continue only if job_ids are returned.
      if (!empty($processing_job) && is_array($processing_job) && array_key_exists(0, $processing_job)
        && array_key_exists('return', $processing_job[0]) && ($processing_job[0]['return'] === 'success')) {

        // Check to see if jobs are running. Don't pass "Go" until all jobs are finished.
        while ($this->processing->are_jobs_running( array($processing_job[0]['workflow']['processing_job_id']) )) {
          $this->processing->are_jobs_running( array($processing_job[0]['workflow']['processing_job_id']) );
          sleep(5);
        }

        // Retrieve all of the logs and assets produced by the processing service.
        $data = $this->processing->get_processing_assets($filesystem, $processing_job[0]['workflow']['processing_job_id']);

        // Log errors and return.
        if (array_key_exists('errors', $data)) {
          $this->repoValidate->logErrors(
            array(
              'job_id' => $job_data['job_id'],
              'uuid' => $job_data['uuid'],
              'user_id' => $job_data['created_by_user_account_id'],
              'job_log_label' => 'Process model',
              'errors' => $data['errors'],
            )
          );
          return $data;
        }

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
    if (!empty($job_status) && !empty($processing_job) && ($recipe_name === 'web-hd')) {
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
    $models_csv = $uv_map = null;

    if (!empty($path) && !empty($job_data)) {

      // Parse the models.csv to get model file names and model purposes.
      $finder = new Finder();
      $finder->name('models.csv');
      $finder->in($path);
      $i = 0;
      foreach ($finder as $file) {
        $models_csv = $file->getContents();
      }

      // Get the UV map.
      $finder = new Finder();
      $finder->path('data')->name('/-diffuse\.png|-diffuse\.jpg$/');
      $finder->in($path);
      $i = 0;
      foreach ($finder as $file) {
        $uv_map = $file;
      }

      // If the UV map isn't found, return an error.
      if (!empty($uv_map) && !is_file($uv_map->getPathname())) {
        $data[0]['errors'][] = 'UV map not found in the repository\'s file system. Path: ' . $uv_map->getPathname();
        return $data;
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

            // Faking
            // $recipe_name = 'fake-recipe';

            // Get the ID of the recipe, so it can be passed to processing service's job creation endpoint (post_job).
            $recipe = $this->processing->getRecipeByName( $recipe_name );

            // Error handling
            if (isset($recipe['error']) && !empty($recipe['error'])) {
              $data[$i]['errors'][] = $recipe['error'] . '. (Recipe name: ' . $recipe_name . ')';
              return $data;
            }

            if (!isset($recipe['error'])) {

              // Create a timestamp for the procesing job name.
              $job_name = str_replace('+00:00', 'Z', gmdate('c', strtotime('now')));
              // Parameters.
              $params = array(
                'highPolyMeshFile' => $file->getFilename()
              );
              // Add the UV map to the parameters.
              if (!empty($uv_map) && is_file($uv_map->getPathname())) $params['highPolyDiffuseMapFile'] = $uv_map->getFilename();

              // Post a new job.
              $result = $this->processing->postJob($recipe['id'], $job_name, $params);

              // Faking
              // $result['httpcode'] = '500';

              // Error handling
              if ($result['httpcode'] !== 201) {
                $data[$i]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'] . '. Ingest job ID: ' . $job_data['uuid'];
                return $data;
              }

              if ($result['httpcode'] === 201) {

                // Faking
                // $job_name = 'fake job name';

                // Get the processing_job data.
                $processing_job = $this->processing->getJobByName($job_name);

                // Error handling
                if (isset($processing_job['error']) && !empty($processing_job['error'])) {
                  $data[$i]['errors'][] = $processing_job['error'] . ' (Job name: ' . $job_name . ')';
                  return $data;
                }

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

                // Transfer the file(s) to the processing service via WebDAV.
                try {

                  // Model file.
                  // The external path - on the processing service side.
                  $path_external_model = $processing_job['id'] . '/' . $file->getFilename();
                  $stream = fopen($file->getPathname(), 'r+');
                  $filesystem->writeStream($path_external_model, $stream);
                  // Before calling fclose on the resource, check if it’s still valid using is_resource.
                  if (is_resource($stream)) fclose($stream);

                  // UV map file.
                  if (is_file($uv_map->getPathname())) {
                    // The external path - on the processing service side.
                    $path_external_map = $processing_job['id'] . '/' . $uv_map->getFilename();
                    $stream_uv = fopen($uv_map->getPathname(), 'r+');
                    $filesystem->writeStream($path_external_map, $stream_uv);
                    // Before calling fclose on the resource, check if it’s still valid using is_resource.
                    if (is_resource($stream_uv)) fclose($stream_uv);
                  }

                  // After transferring file(s), run the processing job.
                  $result = $this->processing->runJob($processing_job['id']);

                  // Error handling
                  if ($result['httpcode'] !== 202) $data[$i]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];

                }
                // Catch the error.
                catch(\League\Flysystem\FileExistsException | \League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
                  $data[$i]['errors'][] = 'Processing Service: ' . $e->getMessage();
                }

              }

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
  public function runWebDerivative($path = null, $job_data = array(), $recipe_name = null, $filesystem)
  {

    $data = $workflow = array();
    $uv_map = null;

    // $this->u->dumper($path,0);
    // $this->u->dumper($job_data,0);
    // $this->u->dumper($recipe_name);

    if (!empty($path) && !empty($job_data)) {

      // Query the workflow table for workflow data.
      $query_params = array('ingest_job_uuid' => $job_data['uuid']);
      $workflow = $this->repo_storage_controller->execute('getWorkflows', $query_params);

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
              1 => array('field_names' => array('processing_job.recipe'), 'search_values' => array($recipe_name), 'comparison' => '='),
              2 => array('field_names' => array('processing_job.state'), 'search_values' => array('created'), 'comparison' => '='),
            ),
            'search_type' => 'AND',
            'omit_active_field' => true,
          )
        );

        if (count($processing_job) > 1) {
          $data[0]['errors'][] = 'More than one processing job found in metadata storage. Please contact the administrator for assistance.';
        }

        // Faking
        // $processing_job[0] = array();
        // $processing_job[0]['asset_path'] = '/fake_directory';

        // If the model file path is found, continue.
        if (!empty($processing_job)) {

          // If the asset_path isn't found, return an error.
          if (!is_file($processing_job[0]['asset_path'])) {
            $data[0]['errors'][] = 'Model asset not found in the repository\'s file system. Path: ' . $processing_job[0]['asset_path'];
            return $data;
          }

          $directory = pathinfo($processing_job[0]['asset_path'], PATHINFO_DIRNAME);
          $base_model_file_name = pathinfo($processing_job[0]['asset_path'], PATHINFO_BASENAME);
          $model_file_name = pathinfo($processing_job[0]['asset_path'], PATHINFO_FILENAME);
          $document_json_file_name = $model_file_name . '-document.json';
          
          // Get the UV map.
          $uv_map = $this->processing->getUvMap($processing_job[0]['asset_path']);

          // If the UV map isn't found, return an error.
          if (empty($uv_map)) {
            $data[0]['errors'][] = 'UV map not found in the repository\'s file system. Path to model directory: ' . $directory;
            return $data;
          } else {
            $base_uv_file_name = pathinfo($uv_map, PATHINFO_BASENAME);
          }

          // Transfer the file to the processing service via WebDAV.
          try {

            // Model file
            // The external path - on the processing service side.
            $path_external_model = $processing_job[0]['processing_service_job_id'] . '/' . $base_model_file_name;
            $stream = fopen($processing_job[0]['asset_path'], 'r+');
            $filesystem->writeStream($path_external_model, $stream);
            // Before calling fclose on the resource, check if it’s still valid using is_resource.
            if (is_resource($stream)) fclose($stream);

            // UV map file.
            if (!empty($uv_map) && is_file($directory . DIRECTORY_SEPARATOR . $base_uv_file_name)) {
              // The external path - on the processing service side.
              $path_external_map = $processing_job[0]['processing_service_job_id'] . '/' . $base_uv_file_name;
              $stream_uv = fopen($directory . DIRECTORY_SEPARATOR . $base_uv_file_name, 'r+');
              $filesystem->writeStream($path_external_map, $stream_uv);
              // Before calling fclose on the resource, check if it’s still valid using is_resource.
              if (is_resource($stream_uv)) fclose($stream_uv);
            }

            // document.json file
            if (is_file($directory . DIRECTORY_SEPARATOR . $document_json_file_name)) {
              // The external path - on the processing service side.
              $path_external_document_json = $processing_job[0]['processing_service_job_id'] . '/' . $document_json_file_name;
              $stream_document_json = fopen($directory . DIRECTORY_SEPARATOR . $document_json_file_name, 'r+');
              $filesystem->writeStream($path_external_document_json, $stream_document_json);
              // Before calling fclose on the resource, check if it’s still valid using is_resource.
              if (is_resource($stream_document_json)) fclose($stream_document_json);
            }

            // Get subject record from EDAN to inject tombstone information into document.json.
            if ($this->edan && ($recipe_name === 'web-thumb')) {

              // Query EDAN.
              $edan_json = $this->repo_import->addEdanDataToJson($workflow[0]['item_id']);
              // Error or set the metaDataFile parameter for the Cook.
              if (is_array($edan_json) && array_key_exists('error', $edan_json)) {
                // If an error is returned, set the error.
                $data[0]['errors'][] = $edan_json['error'];
              } else {
                // Send EDAN metadata to the Cook to inject into the document.json file via metaDataFile.json.
                if (!empty($edan_json)) {
                  // The external path - on the processing service side.
                  $path_external_meta = $processing_job[0]['processing_service_job_id'] . '/metaDataFile.json';
                  // Create a temporary file.
                  $temp = tmpfile();
                  fwrite($temp, $edan_json);
                  // Open the temporary file.
                  $path = stream_get_meta_data($temp)['uri'];
                  $stream_meta = fopen($path, 'r+');
                  // Transfer the file.
                  $filesystem->writeStream($path_external_meta, $stream_meta);
                  // Before calling fclose on the resource, check if it’s still valid using is_resource.
                  if (is_resource($stream_meta)) fclose($stream_meta);
                  // Remove the temporary file.
                  if (is_resource($temp)) fclose($temp);
                }
              }

            }

            // Now that the file has been transferred, go ahead and run the processing job.
            $result = $this->processing->runJob($processing_job[0]['processing_service_job_id']);

            // Error handling
            if ($result['httpcode'] !== 202) {
              $data[0]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'] . '. Processing job ID: ' . $processing_job[0]['processing_service_job_id'];
              // Matching the output of $this->repo_storage_controller->execute('createWorkflow'
              $data[0]['return'] = 'error';
            } else {
              // Matching the output of $this->repo_storage_controller->execute('createWorkflow'
              $data[0]['return'] = 'success';
            }

          }
          // Catch the error.
          catch(\League\Flysystem\FileExistsException | \League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
            $data[0]['errors'][] = 'Processing Service: ' . $e->getMessage();
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

  public function manualTestRunWebHd($uuid, $job_data, $path, $filesystem) {
    // Run
    $processing_job = $this->runWebHd($path, $job_data, 'web-hd', $filesystem);
    // Log errors to the job_log table.
    foreach ($processing_job as $pkey => $pvalue) {
      if (array_key_exists('errors', $pvalue)) {
        $this->repoValidate->logErrors(
          array(
            'job_id' => $job_data['job_id'],
            'uuid' => $job_data['uuid'],
            'user_id' => $job_data['created_by_user_account_id'],
            'job_log_label' => 'Process model',
            'errors' => $pvalue['errors'],
          )
        );
        return $pvalue;
      }
    }
  }

  public function manualTestRunWebDerivative($uuid, $job_data, $path, $filesystem) {
    // Run
    $processing_job = $this->runWebDerivative($path, $job_data, 'web-thumb', $filesystem);
    // Log errors to the job_log table.
    foreach ($processing_job as $key => $data) {
      if (array_key_exists('errors', $data)) {
        $this->repoValidate->logErrors(
          array(
            'job_id' => $job_data['job_id'],
            'uuid' => $job_data['uuid'],
            'user_id' => $job_data['created_by_user_account_id'],
            'job_log_label' => 'Process model',
            'errors' => $data['errors'],
          )
        );
        return $data;
      }
    }
  }

  public function manualTestGetProcessingAssets($filesystem) {
    // Replace with a valid processing job uuid.
    $processing_job_uuid = '2B97C2A3-13C6-F731-138B-86FD06CABE01';
    // Run
    $data = $this->processing->get_processing_assets($filesystem, $processing_job_uuid);
    // Log errors to the job_log table.
    if (array_key_exists('errors', $data)) {
      $this->repoValidate->logErrors(
        array(
          'job_id' => $job_data['job_id'],
          'uuid' => $job_data['uuid'],
          'user_id' => $job_data['created_by_user_account_id'],
          'job_log_label' => 'Process model',
          'errors' => $data['errors'],
        )
      );
      return $data;
    }
  }

}
