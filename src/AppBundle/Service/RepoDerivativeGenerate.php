<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;

use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\FileHelperService;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RepoDerivativeGenerate {

  /**
   * @var object $u
   */
  public $u;

  public $f;

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
  public function __construct(KernelInterface $kernel, string $uploads_directory, string $external_file_storage_path, \Doctrine\DBAL\Connection $conn, FileHelperService $f)
  {
    $this->u = new AppUtilities();
    $this->f = $f;
    $this->kernel = $kernel;
    $project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->project_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $project_directory) : $project_directory;
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    $this->external_file_storage_path = $external_file_storage_path;
    $this->conn = $conn;
    $this->repoValidate = new RepoValidateData($conn);
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
  }


  /**
   * @param $uuid The UUID of the job.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function generateCaptureDatasetDerivatives($uuid = null)
  {
    // Generate thumbnails to display in the capture dataset UI.

    $data = array();
    $utils = new AppUtilities();

    // For this job_id, find corresponding capture dataset images.
    $cd_data = $this->repo_storage_controller->execute('getImportedCaptureDatasetImages', array('job_uuid' => $uuid));

    // $cd_data now contains an array of the first 100 images of each capture dataset
    // For each image generate a 200px wide thumb and a 800px "fullscreen" image.
    foreach($cd_data as $cd_image_file) {
      // Absolute local path.
      $file_path = $this->project_directory . 'web' . $cd_image_file['file_path'];
      $file_name = $cd_image_file['file_name'];
      $job_id = $cd_image_file['job_id']; // Redundant, natch

      // For each image make full path using the file_path column.
      $path_data = $this->f->getAlternateFilePaths($file_path, true);
      // if($path_data['incoming_path_type'] !== 'unknown') {
      //   $path = $path_data['alternate_paths']['local_uploads_directory'];
      // }
      // else {
      //   //@todo- flag this as an issue, we can't grok the incoming $file_path
      //   continue;
      // }

      // $this->u->dumper($path_data);

      if (!is_file($file_path)) {
        $errors = array('Target file not found - ' . $file_path);
      }
      else {
        $errors = array();
        $file_info = array();

        // Generate the derivatives.
        // php bin/console app:derivative-image [file_path] [desired_width] [desired_height] [desired_filename]

        $file_parts = explode('.', $file_name);
        if(count($file_parts) > 1) {

          $last = count($file_parts) - 1;
          $file_ext = $file_parts[$last];
          unset($file_parts[$last]);
          $file_base_name = implode('.', $file_parts);

          $new_thumb_file_name = $file_base_name . '_thumb.' . $file_ext;
          $new_thumb_path = str_replace($file_name, $new_thumb_file_name, $file_path);

          $res = $utils->resizeImage($file_path, 200, NULL, $new_thumb_file_name);

          // Get the image height.
          $height = 0;
          $width = 200;
          // $res will be [width]x[height]
          $dimensions_array = explode('x', $res);
          if(count($dimensions_array) == 2) {
            $width = $dimensions_array[0]; // Should be 200
            $height = $dimensions_array[1];
          }

          // Write the derivative records to the capture_data_file and file_upload tables.
          // $cd_image_file contains all of the original capture_data_file and file_upload values.
          if(file_exists($new_thumb_path)) {
            $new_capture_data_file = $cd_image_file;
            $new_capture_data_file['derivative_file_type'] = 'thumb';
            $new_capture_data_file['capture_data_file_name'] = $new_thumb_file_name;
            $new_capture_data_file['file_name'] = $new_thumb_file_name;
            // Path should start with '/uploads/repository/'.
            //@todo instead use fileservicehelper
            $new_capture_data_file['file_path'] = str_replace($file_name, $new_thumb_file_name, $file_path);
            $new_capture_data_file['file_path'] = str_replace($this->project_directory . 'web', '', $new_capture_data_file['file_path']);
            if(substr($new_capture_data_file['file_path'], 0, 1) !== DIRECTORY_SEPARATOR) {
              $new_capture_data_file['file_path'] = DIRECTORY_SEPARATOR . $new_capture_data_file['file_path'];
            }

            $new_capture_data_file['file_size'] = filesize($new_thumb_path);
            $new_capture_data_file['file_hash'] = md5_file($new_thumb_path);
            $new_capture_data_file['image_width'] = $width;
            $new_capture_data_file['image_height'] = $height;

            $ret = $this->repo_storage_controller->execute('createCaptureDatasetImageDerivatives', $new_capture_data_file);

            $file_info[$new_thumb_file_name] = $res;
          }
          else {
            $errors[] = "Unable to generate thumbnail $new_thumb_path";
          }

          $new_midsize_file_name = $file_base_name . '_mid.' . $file_ext;
          $new_midsize_path = str_replace($file_name, $new_midsize_file_name, $file_path);

          $res = $utils->resizeImage($file_path, 800, 0, $new_midsize_file_name);

          // Get the image height.
          $height = 0;
          $width = 800;
          // $res will be [width]x[height]
          $dimensions_array = explode('x', $res);
          if(count($dimensions_array) == 2) {
            $width = $dimensions_array[0]; // Should be 800
            $height = $dimensions_array[1];
          }

          if(file_exists($new_midsize_path)) {
            $new_capture_data_file = $cd_image_file;
            $new_capture_data_file['derivative_file_type'] = 'midsize';
            $new_capture_data_file['capture_data_file_name'] = $new_midsize_file_name;
            $new_capture_data_file['file_name'] = $new_midsize_file_name;
            //@todo instead use fileservicehelper
            $new_capture_data_file['file_path'] = str_replace($file_name, $new_midsize_file_name, $file_path);
            $new_capture_data_file['file_path'] = str_replace($this->project_directory . 'web', '', $new_capture_data_file['file_path']);
            if(substr($new_capture_data_file['file_path'], 0, 1) !== DIRECTORY_SEPARATOR) {
              $new_capture_data_file['file_path'] = DIRECTORY_SEPARATOR . $new_capture_data_file['file_path'];
            }

            $new_capture_data_file['file_size'] = filesize($new_midsize_path);
            $new_capture_data_file['file_hash'] = md5_file($new_midsize_path);
            $new_capture_data_file['image_width'] = $width;
            $new_capture_data_file['image_height'] = $height;

            $ret = $this->repo_storage_controller->execute('createCaptureDatasetImageDerivatives', $new_capture_data_file);

            $file_info[$new_midsize_file_name] = $res;
          }
          else {
            $errors[] = "Unable to generate midsize image $new_midsize_path";
          }

        }

        if(!empty($file_info)) {
          $data[$file_name] = $file_info;
        }

      }
      if (is_array($errors) && !empty($errors)) {
        $data[$file_name]['errors'] = $errors;

        // Log the errors to the database.
        $this->repoValidate->logErrors(
          array(
            'job_id' => $job_id,
            'user_id' => 0,
            'job_log_label' => 'Create dataset derivative images',
            'errors' => $errors,
          )
        );

      }

    }

    // Return array that looks like:
    /*
     * [0] => array($original_file_name => [0] => array($derivative_filename => $derivative_dimensions), [1] => array($, $), etc.)
     *
     * Alternatively:
     * [0] => array('errors' => array($e1, $e2, ...)), [1] => array('errors' => array()), ...
     *
     * Or mixed
     */

    return $data;
  }

}