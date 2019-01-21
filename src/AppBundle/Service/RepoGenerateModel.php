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

    // Throw an error if the uuid doesn't exist.
    if (empty($uuid)) {
      $return['errors'][] = 'The UUID is missing';
      return $return;
    }

    // Absolute local path.
    $path = $this->project_directory . $this->uploads_directory . $uuid;
    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid, 'generateModelAssets'));

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

      // Create a new processing job and run.
      $processing_job = $this->runProcessingJob($path, $job_data, $recipe_name, $filesystem);

      // $this->u->dumper($processing_job[0]['workflow']['job_id']);

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
        while ($this->processing->are_jobs_running( array($processing_job[0]['workflow']['job_id']) )) {
          $this->processing->are_jobs_running( array($processing_job[0]['workflow']['job_id']) );
          sleep(5);
        }

        // Retrieve all of the logs produced by the processing service.
        $data = $this->processing->get_processing_assets($filesystem, $processing_job[0]['workflow']['job_id']);

        // Update the workflow record. Set the step state to NULL and step-id to 'qc-hd.
        $query_params = array(
          'workflow_id' => $processing_job[0]['workflow']['workflow_id'],
          'step_state' => NULL,
          'step_id' => 'qc-hd',
          'step_type' => 'manual',
        );
        $this->repo_storage_controller->execute('updateWorkflow', $query_params);
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
   * @param string $path The directory which contains model(s) to be processed.
   * @param array $job_data The repository job's data.
   * @param string $recipe_name The processing recipe name.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function runProcessingJob($path = null, $job_data = array(), $recipe_name = null, $filesystem) {

    $data = array();

    if (!empty($path) && !empty($job_data)) {

      // Traverse the local path and validate models.
      $finder = new Finder();
      $finder->path('data')->name('/-master\.obj|\-master.ply$/');
      $finder->in($path);
      $i = 0;
      foreach ($finder as $file) {

        // Make sure the asset is a file and not a directory (directories are automatically detected by WebDAV).
        if ($file->isFile()) {

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

              // The external path.
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

    return $data;
  }

}
