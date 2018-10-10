<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;

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
   * Constructor
   * @param object  $kernel  Symfony's kernel object
   * @param string  $uploads_directory  Uploads directory path
   * @param string  $processing  Repo Processing Service class.
   * @param string  $conn  The database connection
   */
  public function __construct(KernelInterface $kernel, string $uploads_directory, RepoProcessingService $processing, \Doctrine\DBAL\Connection $conn)
  {
    $this->u = new AppUtilities();
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    $this->conn = $conn;
    $this->repoValidate = new RepoValidateData($conn);
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->processing = $processing;
  }

  /**
   * @param $target_directory The directory which contains model(s) to be validated and processed.
   * @param $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function validate_models($target_directory = null, $filesystem)
  {
    // $job_ids = array(
    //   '8A058D13-469F-F1D4-4236-3CC131B7C986',
    //   '8C405869-1BB4-1D99-4CC6-FCAA182B2D44',
    // );
    // foreach ($job_ids as $key => $value) {
    //   $this->processing->delete_job($value);
    // }
    // $this->u->dumper('done deleting job(s)');

    // $this->u->dumper($target_directory);
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

    $data = array();
    $job_status = 'models validated';

    // Absolute local path.
    $path = $this->project_directory . $this->uploads_directory . $target_directory;
    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($target_directory));

    if (!is_dir($path)) {
      $data[0]['errors'][] = 'Target directory not found - ' . $path;
    }

    if (is_dir($path)) {

      $finder = new Finder();
      $finder->path('data')->name('/\.obj|\.ply$/');
      $finder->in($path);

      // Traverse the local path and validate models.
      $i = 0;
      foreach ($finder as $file) {

        // Make sure the asset is a file and not a directory (directories are automatically detected by WebDAV).
        if ($file->isFile()) {

          $recipe = $this->processing->get_recipe_by_name('inspect-mesh');

          // TODO: Error handling
          // if (isset($recipe['error'])) $data[$i]['errors'][] = '';

          if (!isset($recipe['error'])) {

            $timestamp = str_replace('+00:00', 'Z', gmdate('c', strtotime('now')));

            // Post a new job.
            $result = $this->processing->post_job($recipe['id'], $timestamp, $file->getFilename());

            // TODO: Error handling
            // if ($recipes['httpcode'] !== 201) $data[$i]['errors'][] = '';

            if ($result['httpcode'] === 201) {

              $job = $this->processing->get_job_by_name($timestamp);

              // TODO: Log job data to the metadata storage

              // TODO: Error handling
              // if (isset($job['error'])) $data[$i]['errors'][] = '';

              // The external path.
              $path_external = $job['id'] . '/' . $file->getFilename();

              // Transfer the file to the processing service via WebDAV.
              try {

                $stream = fopen($file->getPathname(), 'r+');
                $filesystem->writeStream($path_external, $stream);
                // Before calling fclose on the resource, check if it’s still valid using is_resource.
                if (is_resource($stream)) fclose($stream);

                // Run the job
                $result = $this->processing->run_job($job['id']);

                // TODO: Error handling
                // if ($recipes['httpcode'] !== 202) $data[$i]['errors'][] = '';

              }
              // Catch the error.
              catch(\League\Flysystem\FileExistsException | \League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
                $data[$i]['errors'][] = $e->getMessage();
              }

              // $this->u->dumper('-- job id --',0);
              // $this->u->dumper($job['id'],0);
              // $this->u->dumper('-- data --',0);
              // $this->u->dumper($data,0);
              // $this->u->dumper('File transfer completed');
            }

          }

          if (!empty($data[$i]['errors'])) {
            // Set the job_status to 'failed', if not already set.
            if ($job_status !== 'failed') $job_status = 'failed';
            // Log the errors to the database.
            $this->repoValidate->logErrors(
              array(
                'job_id' => $job_data['job_id'],
                'user_id' => 0,
                'job_log_label' => 'Validate Model',
                'errors' => $data[$i]['errors'],
              )
            );
          }

          $i++;
        }

      }

    }

    // Update the 'job_status' in the 'job' table accordingly.
    $this->repo_storage_controller->execute('setJobStatus', 
      array(
        'job_id' => $job_data['uuid'], 
        'status' => $job_status, 
        'date_completed' => date('Y-m-d h:i:s')
      )
    );

    return $data;
  }

}