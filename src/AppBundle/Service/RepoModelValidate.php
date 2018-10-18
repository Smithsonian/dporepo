<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use AppBundle\Service\RepoValidateData;
use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Service\RepoProcessingService;
use AppBundle\Utils\AppUtilities;

class RepoModelValidate implements RepoModelValidateInterface {

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
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
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
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function validate_models($uuid = null, $filesystem)
  {

    $data = array();
    $job_status = 'metadata ingest in progress';

    // Absolute local path.
    $path = $this->project_directory . $this->uploads_directory . $uuid;
    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid));

    // Throw an error if the job record doesn't exist.
    if (!$job_data) {
      $return['errors'][] = 'The Job record doesn\'t exist';
      return $return;
    }

    // Don't perform the model validation if the job_status has been set to 'failed'.
    if ($job_data['job_status'] === 'failed') {
      $return['errors'][] = 'The job has failed. Exiting model validation process.';
      return $return;
    }

    if (!is_dir($path)) {
      $data[0]['errors'][] = 'Target directory not found - ' . $path;
    }

    if (is_dir($path)) {

      // Create a new inspect-mesh job and run.
      $processing_job = $this->run_validate_models($path, $job_data, $filesystem);

      // Continue only if job_ids are returned.
      if (!empty($processing_job)) {

        // Check to see if jobs are running. Don't pass "Go" until all jobs are finished.
        while ($this->processing->are_jobs_running($processing_job['job_ids'])) {
          $this->processing->are_jobs_running($processing_job['job_ids']);
          sleep(5);
        }

        // // Spoof the job_ids array (for testing).
        // $processing_job['job_ids'] = array('60444498-23B8-8B29-F74B-E60C0BA9FE1A');

        // Retrieve all of the logs produced by the processing service.
        $processing_assets = $this->processing->get_processing_asset_logs($processing_job['job_ids'], $filesystem);

        // Insert processing-based logs into the metadata storage service.
        if (!empty($processing_assets)) {
          // Loop through the processing-based logs.
          foreach ($processing_assets as $asset) {
            // Insert one processing-based log.
            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'processing_job_file',
              'user_id' => $this->user_id,
              'values' => $asset,
            ));
          }
        }

        // Update the main job record with the results of the processing job.
        foreach ($processing_job['job_ids'] as $job_key => $job_id) {

          // Get the processing job from the processing service.
          $job = $this->processing->get_job($job_id);

          // Error handling
          if ($job['httpcode'] !== 200) $data[$job_key]['errors'][] = 'The processing service returned HTTP code ' . $job['httpcode'];

          if ($job['httpcode'] === 200) {

            $processing_job = json_decode($job['result'], true);
            // If there's an error, set the repository's upload/ingest job status to 'failed'.
            $job_status = ($processing_job['state'] === 'error') ? 'failed' : $job_status;

            // Query the database for the corresponding processing job record,
            // so we can use the repository's ID (processing_job_id) to update the repository's processing job record.
            $repo_processing_job_data = $this->repo_storage_controller->execute('getRecords', array(
              'base_table' => 'processing_job',
              'fields' => array(),
              'limit' => 1,
              'search_params' => array(
                0 => array('field_names' => array('processing_job.processing_service_job_id'), 'search_values' => array($job_id), 'comparison' => '='),
              ),
              'search_type' => 'AND',
              'omit_active_field' => true,
              )
            );

            if ($repo_processing_job_data) {

              // Update one job record. 
              $processing_job_id = $this->repo_storage_controller->execute('saveRecord', array(
                'base_table' => 'processing_job',
                'record_id' => $repo_processing_job_data[0]['processing_job_id'],
                'user_id' => $this->user_id,
                'values' => array(
                  'job_json' => json_encode($job['result']), 
                  'state' => $processing_job['state']
                )
              ));


              // Log the errors to the database.
              if ($processing_job['state'] === 'error') {

                // Get the model's file name
                foreach ($processing_assets as $asset) {
                  if ($asset['file_name'] === 'model-report.json') {
                    $report = json_decode($asset['file_contents'], true);
                  }
                }
                $this->repoValidate->logErrors(
                  array(
                    'job_id' => $job_data['job_id'],
                    'user_id' => $this->user_id,
                    'job_log_label' => 'Asset Validation',
                    'errors' => array($processing_job['error'] . ' (Processing job ID: ' . $processing_job['id'] . ', Model file name: ' . $report['parameters']['meshFile'] . ')'),
                  )
                );
              }

            }

          }
        }

      }

    }

    // Update the 'job_status' in the 'job' table accordingly.
    if (!empty($job_status)) {
      $this->repo_storage_controller->execute('setJobStatus', 
        array(
          'job_id' => $job_data['uuid'], 
          'status' => $job_status, 
          'date_completed' => date('Y-m-d h:i:s')
        )
      );
    }

    return $data;
  }

  /**
   * @param string $path The directory which contains model(s) to be validated and processed.
   * @param array $job_data The repository job's data.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function run_validate_models($path = null, $job_data = array(), $filesystem) {

    $data = array();

    if (!empty($path) && !empty($job_data)) {

      // Traverse the local path and validate models.
      $finder = new Finder();
      $finder->path('data')->name('/\.obj|\.ply$/');
      $finder->in($path);
      $i = 0;
      foreach ($finder as $file) {

        // Make sure the asset is a file and not a directory (directories are automatically detected by WebDAV).
        if ($file->isFile()) {

          // Get the ID of the recipe, so it can be passed to processing service's job creation endpoint (post_job).
          $recipe = $this->processing->get_recipe_by_name('inspect-mesh');

          // Error handling
          if (isset($recipe['error']) && !empty($recipe['error'])) $data[$i]['errors'][] = $recipe['error'];

          if (!isset($recipe['error'])) {

            // Create a timestamp for the procesing job name.
            $job_name = str_replace('+00:00', 'Z', gmdate('c', strtotime('now')));
            // Post a new job.
            $result = $this->processing->post_job($recipe['id'], $job_name, $file->getFilename());

            // Error handling
            if ($result['httpcode'] !== 201) $data[$i]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];

            if ($result['httpcode'] === 201) {

              // Get the job data.
              $job = $this->processing->get_job_by_name($job_name);

              // Error handling
              if (isset($job['error']) && !empty($job['error'])) $data[$i]['errors'][] = $job['error'];

              // Log job data to the metadata storage
              if (isset($job['error']) && empty($job['error'])) {
                $processing_job_id = $this->repo_storage_controller->execute('saveRecord', array(
                  'base_table' => 'processing_job',
                  'user_id' => $this->user_id,
                  'values' => array(
                    'job_id' => $job_data['job_id'],
                    'processing_service_job_id' => $job['id'], 
                    'recipe' =>  $job['recipe']['name'], 
                    'job_json' => json_encode($job), 
                    'state' => $job['state']
                  )
                ));
              }

              // Collect the job IDs so we can query against the processing service for job statuses.
              $data['job_ids'][] = $job['id'];

              // echo 'done inserting processing job... id = ' . $processing_job_id . "\n";
              // die();

              // The external path.
              $path_external = $job['id'] . '/' . $file->getFilename();

              // Transfer the file to the processing service via WebDAV.
              try {

                $stream = fopen($file->getPathname(), 'r+');
                $filesystem->writeStream($path_external, $stream);
                // Before calling fclose on the resource, check if it’s still valid using is_resource.
                if (is_resource($stream)) fclose($stream);

                // Now that the file has been transferred, go ahead and run the job.
                $result = $this->processing->run_job($job['id']);

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
                'user_id' => $this->user_id,
                'job_log_label' => 'Validate Model',
                'errors' => $data[$i]['errors'],
              )
            );
          }

          $i++;
        }

      }

    }

    return $data;
  }

}

// Temporary
////////////////

// $this->u->dumper($uuid);
// $result = $this->processing->get_recipes(); // good
// Empty array when a job is created.
// post_job($recipe_id, $job_name, $file_name)
// $result = $this->processing->post_job('ee77ee05-d832-4729-9914-18a96939f205', 'Gor test: ' . str_replace('+00:00', 'Z', gmdate('c', strtotime('now'))), 'model.ply');

// $result = $this->processing->get_job($job_id); // good
// $result = $this->processing->run_job($job_id);

// $result = $this->processing->cancel_job($job_id);
// $result = $this->processing->delete_job($job_id);
// $result = $this->processing->get_jobs(); // good

// $result = $this->processing->machine_state(); // good

// if (isset($result['httpcode']) && ($result['httpcode'] !== 200) && ($result['httpcode'] !== 201)) {
  // $this->u->dumper('Error: HTTP Code ' . $result['httpcode'],0);
  // $this->u->dumper('Response Header: ' . $result['response_header']);
// }

// if(isset($result['result']) && ($result['httpcode'] === 200) || ($result['httpcode'] === 201)) {
// $this->u->dumper($result);
// }