<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;

use AppBundle\Service\RepoValidateData;
use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Utils\AppUtilities;

class RepoFileTransfer implements RepoFileTransferInterface {

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
   * @var string $external_file_storage_path
   */
  private $external_file_storage_path;

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
   * Constructor
   * @param object  $kernel  Symfony's kernel object
   * @param string  $uploads_directory  Uploads directory path
   * @param string  $external_file_storage_path  External file storage path
   * @param string  $conn  The database connection
   */
  public function __construct(KernelInterface $kernel, string $uploads_directory, string $external_file_storage_path, \Doctrine\DBAL\Connection $conn)
  {
    $this->u = new AppUtilities();
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    $this->external_file_storage_path = $external_file_storage_path;
    $this->conn = $conn;
    $this->repoValidate = new RepoValidateData($conn);
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    // Model extensions.
    $this->model_extensions = array(
      'obj',
      'ply',
      // 'gltf',
      'glb',
    );
  }

  /**
   * Working with Flysystem
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   *
   * Create Directories
   * $response = $filesystem->createDir('data');
   * $this->u->dumper($response);
   *
   * Check if a file exists
   * $exists = $filesystem->has('data');
   * $this->u->dumper($exists);
   *
   * createDir
   * write
   * read
   * update
   * listContents
   * delete
   * getTimestamp
   */

  /**
   * @param $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return mixed array containing success/fail value, and any messages.
   */
  public function checkExternalStorage($job_uuid, $filesystem = null)
  {
    $data = array();
    // Get the job's data via job_uuid.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($job_uuid));
    // Absolute external path.
    $uuid = $this->u->createUuid(); // (append a unique ID to the file name)
    $path_external = $this->external_file_storage_path . '_checker/' . $uuid . '_robots.txt';
    // Local file to be written.
    $stream = fopen($this->project_directory . 'web/robots.txt', 'r+');

    // Write the file.
    try {
      $result = $filesystem->writeStream($path_external, $stream);
      // Before calling fclose($stream) on the resource, check if it’s still valid using is_resource.
      if (is_resource($stream)) fclose($stream);
      // Remove the uploaded test file.
      if ($result) $filesystem->delete($path_external);
    }
    // Catch the error.
    catch(\Sabre\HTTP\ClientException $e) {
      $data[]['errors'][] = 'External Storage Error - ' . $e->getMessage();
    }

    // If this is a check related to a job, log the errors to the database.
    if (!empty($data[0]['errors']) && !empty($job_data)) {
      // Log the error to the database.
      $this->repoValidate->logErrors(
        array(
          'job_id' => $job_data['job_id'],
          'user_id' => 0,
          'job_log_label' => 'File Transfer',
          'errors' => $data[0]['errors'],
        )
      );
      // Update the 'job_status' in the 'job' table accordingly.
      $this->repo_storage_controller->execute('setJobStatus', 
        array(
          'job_id' => $job_uuid, 
          'status' => 'failed',
          'date_completed' => date('Y-m-d h:i:s')
        )
      );
    }
    
    return $data;
  }

  /**
   * @param $target_directory The directory which contains files to be transferred.
   * @param $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return mixed array containing success/fail value, and any messages.
   */
  public function transferFiles($target_directory = null, $filesystem = null)
  {
    $data = array();
    $job_status = 'complete';

    // Absolute local path.
    $path = $this->project_directory . $this->uploads_directory . $target_directory;
    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($target_directory));

    if (!is_dir($path)) {
      $data[0]['errors'][] = 'Target directory not found - ' . $path;
    }

    if (is_dir($path)) {

      $finder = new Finder();
      $finder->in($path);
      $finder->sortByName();

      // Traverse the local path and upload files.
      $i = 0;
      foreach ($finder as $file) {

        // Make sure the asset is a file and not a directory (directories are automatically detected by WebDAV).
        if ($file->isFile()) {

          // Remove the absolute local path to format into the absolute external path.
          $path_external = str_replace($this->project_directory . $this->uploads_directory, $this->external_file_storage_path, $file->getPathname());

          // Write the file.
          try {
            $stream = fopen($file->getPathname(), 'r+');
            $filesystem->writeStream($path_external, $stream);
            // TODO: writeStream doesn't overwrite, but updateStream does overwrite, which is probably what we want for now?
            // $filesystem->updateStream($path_external, $stream);
            // Before calling fclose on the resource, check if it’s still valid using is_resource.
            if (is_resource($stream)) fclose($stream);
            // Log the file to metadata storage.
            $this->logFileToMetadataStorage($file);
          }
          // Catch the error.
          catch(\League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
            $data[$i]['errors'][] = $e->getMessage();
          }
          catch(\League\Flysystem\FileExistsException $e) {

            $stream = fopen($file->getPathname(), 'r+');
            $filesystem->updateStream($path_external, $stream);
            // Before calling fclose on the resource, check if it’s still valid using is_resource.
            if (is_resource($stream)) fclose($stream);
            // Log the file to metadata storage.
            $this->logFileToMetadataStorage($file);

          }

          // Return some information about the file.
          $data[$i]['file_name'] = $file->getFilename();
          $data[$i]['file_size'] = $file->getSize();
          $data[$i]['file_extension'] = $file->getExtension();

          if (!empty($data[$i]['errors'])) {
            // Set the job_status to 'failed', if not already set.
            if ($job_status !== 'failed') $job_status = 'failed';
            // Log the errors to the database.
            $this->repoValidate->logErrors(
              array(
                'job_id' => $job_data['job_id'],
                'user_id' => 0,
                'job_log_label' => 'File Transfer',
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

  /**
   * @param $target_directory The directory which contains files to be transferred.
   * @param $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return mixed array containing success/fail value, and any messages.
   */
  public function removeFiles($target_directory = null, $filesystem = null)
  {
    $data = array();

    // Absolute external path.
    $path = $this->external_file_storage_path . $target_directory;

    // Remove file(s).
    try {
      $response = $filesystem->delete($path);
    }
    // Catch the error.
    catch(\League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
      $data[0]['errors'][] = $e->getMessage();
    }
    
    return $data;
  }

  /**
   * Log Files to Metadata Storage
   *
   * @param string $file The file object
   * @return array
   */
  public function logFileToMetadataStorage($file = null)
  {
    $data = array();
    $record_id = '';

    // Get the job's UUID.
    $job_directory = str_replace($this->project_directory . $this->uploads_directory, '', $file->getPathname());
    $job_directory_parts = explode(DIRECTORY_SEPARATOR, $job_directory);
    $job_uuid = $job_directory_parts[0];

    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($job_uuid));

    $full_path = str_replace($this->project_directory, '', $file->getPathname());
    $full_path = str_replace('web', '', $full_path);

    // Query the metadata storage for the file to update the record, and avoid duplicates.
    $existing_file = $this->repo_storage_controller->execute('getRecords', array(
        'base_table' => 'file_upload',
        'fields' => array(),
        'limit' => 1,
        'search_params' => array(
          0 => array('field_names' => array('file_upload.file_path'), 'search_values' => array($full_path), 'comparison' => '='),
        ),
        'search_type' => 'AND',
        'omit_active_field' => true,
      )
    );

    if (!empty($existing_file)) {
      $record_id = $existing_file[0]['file_upload_id'];
    }

    $this_id = $this->repo_storage_controller->execute('saveRecord', array(
      'base_table' => 'file_upload',
      'record_id' => $record_id,
      'user_id' => $job_data['created_by_user_account_id'],
      'values' => array(
        'job_id' => $job_data['job_id'],
        'parent_record_id' => $job_data['project_id'],
        'parent_record_type' => 'project',
        'file_name' => $file->getBasename(),
        'file_path' => $full_path,
        'file_size' => filesize($file->getPathname()),
        'file_type' => $file->getExtension(), // $file->getMimeType()
        'file_hash' => md5($file->getBasename()),
      )
    ));

    if (in_array($file->getExtension(), $this->model_extensions)) {

      // $this->u->dumper($file->getExtension());

      $model_id = $this->logModelAssets($file);
    )

    return $data;
  }

  /**
   * Log Model Assets
   *
   * NOTE: going to need to parse through the report generated by the processing service
   * to get certain pieces of metadata we'll need for the metadata storage inserts.
   * Most of the metadata will probably be in *-report.json and *-item.json.
   * See RepoImport.php for an existing example - look for $finder->files()->name('*-web-hd-report.json');
   *
   * @param string $file The file object
   * @return array
   */
  public function logModelAssets($file = null)
  {
    $data = array();
    $record_id = '';

    $full_path = str_replace($this->project_directory, '', $file->getPathname());
    $full_path = str_replace('web', '', $full_path);

    $this->u->dumper($full_path);

    // // Query the metadata storage for the file to update the record, and avoid duplicates.
    // $existing_file = $this->repo_storage_controller->execute('getRecords', array(
    //     'base_table' => 'file_upload',
    //     'fields' => array(),
    //     'limit' => 1,
    //     'search_params' => array(
    //       0 => array('field_names' => array('file_upload.file_path'), 'search_values' => array($full_path), 'comparison' => '='),
    //     ),
    //     'search_type' => 'AND',
    //     'omit_active_field' => true,
    //   )
    // );

    // if (!empty($existing_file)) {
    //   $record_id = $existing_file[0]['file_upload_id'];
    // }

    // $this_id = $this->repo_storage_controller->execute('saveRecord', array(
    //   'base_table' => 'file_upload',
    //   'record_id' => $record_id,
    //   'user_id' => $job_data['created_by_user_account_id'],
    //   'values' => array(
    //     'job_id' => $job_data['job_id'],
    //     'parent_record_id' => $job_data['project_id'],
    //     'parent_record_type' => 'project',
    //     'file_name' => $file->getBasename(),
    //     'file_path' => $full_path,
    //     'file_size' => filesize($file->getPathname()),
    //     'file_type' => $file->getExtension(), // $file->getMimeType()
    //     'file_hash' => md5($file->getBasename()),
    //   )
    // ));

    return $data;
  }

}