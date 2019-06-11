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
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function validateModels($uuid = null, $filesystem)
  {

    $data = array();
    $job_status = 'model processing in progress';
    $job_failed_message = 'The job has failed. Exiting model validation process.';

    // Absolute local path.
    $path = $this->project_directory . $this->uploads_directory . $uuid;
    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid, 'validateModels'));

    // Throw an error if the job record doesn't exist.
    if (empty($job_data)) {
      $return['errors'][] = 'The Job record doesn\'t exist - validateModels() - ' . $uuid;
      return $return;
    }

    // Don't perform the model validation if the job_status has been set to 'failed'.
    if ($job_data['job_status'] === 'failed') {
      $return['errors'][] = $job_failed_message;
      return $return;
    }

    if (!is_dir($path)) {
      $return['errors'][] = 'Target directory not found - ' . $path;
      return $return;
    }

    if (is_dir($path)) {

      // Create a new inspect-mesh job and run.
      $processing_job = $this->runValidateModels($path, $job_data, $filesystem);

      // Check the job's status to insure that the job_status hasn't been set to 'failed'.
      $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid, 'generateModelAssets'));
      // If the job status has been set to 'failed', return with the error message.
      if ($job_data['job_status'] === 'failed') {
        $return['errors'][] = $job_failed_message;
        return $return;
      }

      // Continue only if job_ids are returned.
      if (!empty($processing_job) && !empty($processing_job['job_ids'])) {

        // Check to see if jobs are running. Don't pass "Go" until all jobs are finished.
        while ($this->processing->are_jobs_running($processing_job['job_ids'])) {
          $this->processing->are_jobs_running($processing_job['job_ids']);
          sleep(5);
        }

        // Retrieve all of the logs produced by the processing service.
        foreach ($processing_job['job_ids'] as $job_id_value) {
          $processing_assets = $this->processing->get_processing_assets($filesystem, $job_id_value);

          if (!empty($processing_assets) && array_key_exists('errors', $processing_assets)) {
            $return['errors'][] = $processing_assets['errors'];
            return $return;
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
          'date_completed' => date('Y-m-d H:i:s')
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
  public function runValidateModels($path = null, $job_data = array(), $filesystem) {

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
          $recipe = $this->processing->getRecipeByName('inspect-mesh');

          // Error handling
          if (isset($recipe['error']) && !empty($recipe['error'])) $data[$i]['errors'][] = $recipe['error'];

          if (!isset($recipe['error'])) {

            // Create a timestamp for the procesing job name.
            $job_name = str_replace('+00:00', 'Z', gmdate('c', strtotime('now')));
            // Post a new job.
            $params = array(
              'meshFile' => $file->getFilename()
            );
            $result = $this->processing->postJob($recipe['id'], $job_name, $params);

            // Error handling
            if ($result['httpcode'] !== 201) $data[$i]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];

            if ($result['httpcode'] === 201) {

              // Get the job data.
              $job = $this->processing->getJobByName($job_name);

              // Error handling
              if (isset($job['error']) && !empty($job['error'])) $data[$i]['errors'][] = $job['error'];

              // Log job data to the metadata storage
              if (isset($job['error']) && empty($job['error'])) {
                $processing_job_id = $this->repo_storage_controller->execute('saveRecord', array(
                  'base_table' => 'processing_job',
                  'user_id' => $this->user_id,
                  'values' => array(
                    'ingest_job_uuid' => $job_data['uuid'],
                    'processing_service_job_id' => $job['id'],
                    'recipe' =>  $job['recipe']['name'],
                    'job_json' => json_encode($job),
                    'state' => $job['state'],
                    'asset_path' => $file->getPathname(),
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
                $result = $this->processing->runJob($job['id']);

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
                'uuid' => $job_data['uuid'],
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
// $result = $this->processing->getRecipes(); // good
// Empty array when a job is created.
// postJob($recipe_id, $job_name, $file_name)
// $result = $this->processing->postJob('ee77ee05-d832-4729-9914-18a96939f205', 'Gor test: ' . str_replace('+00:00', 'Z', gmdate('c', strtotime('now'))), 'model.ply');

// $result = $this->processing->getJob($job_id); // good
// $result = $this->processing->runJob($job_id);

// $result = $this->processing->cancelJob($job_id);
// $result = $this->processing->deleteJob($job_id);
// $result = $this->processing->getJobs(); // good

// $result = $this->processing->machine_state(); // good

// if (isset($result['httpcode']) && ($result['httpcode'] !== 200) && ($result['httpcode'] !== 201)) {
  // $this->u->dumper('Error: HTTP Code ' . $result['httpcode'],0);
  // $this->u->dumper('Response Header: ' . $result['response_header']);
// }

// if(isset($result['result']) && ($result['httpcode'] === 200) || ($result['httpcode'] === 201)) {
// $this->u->dumper($result);
// }