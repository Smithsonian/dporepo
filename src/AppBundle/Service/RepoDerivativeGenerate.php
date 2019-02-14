<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;

use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Utils\AppUtilities;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class RepoDerivativeGenerate {

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
      $file_path = $cd_image_file['file_path'];
      $file_name = $cd_image_file['file_name'];
      $job_id = $cd_image_file['job_id']; // Redundant, natch

      // Absolute local path.
      // For each image make full path using the file_path column.
      $path = $this->project_directory . "web" . $file_path;
      $path = str_replace("//", "/", str_replace("\\", "/", $path));

      if (!is_file($path)) {
        $errors = array('Target file not found - ' . $path);
      }
      else {
        $errors = array();
        $file_info = array();

        // Generate the derivatives.
        // php bin/console app:derivative-image [file_path] [desired_width] [desired_height] [desired_filename]

        $new_thumb_file_name = str_replace('.jpg', '_thumb.jpg', $file_name);
        $new_thumb_path = str_replace('.jpg', '_thumb.jpg', $path);

        $res = $utils->resizeImage($path, 200, NULL, $new_thumb_file_name);

        // Write the derivative records to the capture_data_file and file_upload tables.
        // $cd_image_file contains all of the original capture_data_file and file_upload values.
        if(file_exists($new_thumb_path)) {
          $new_capture_data_file = $cd_image_file;
          $new_capture_data_file['variant_type'] = isset($cd_image_file['variant_type']) ? $cd_image_file['variant_type'] . ' thumb' : 'thumb';
          $new_capture_data_file['capture_data_file_name'] = $new_capture_data_file['file_name'] = $new_thumb_file_name;
          $new_capture_data_file['file_path'] = $new_thumb_path;
          $new_capture_data_file['file_size'] = filesize($new_thumb_path);

          $ret = $this->repo_storage_controller->execute('createCaptureDatasetImageDerivatives', $new_capture_data_file);

          $file_info[$new_thumb_file_name] = $res;
        }
        else {
          $errors[] = "Unable to generate thumbnail $new_thumb_path";
        }

        $new_midsize_file_name = str_replace('.jpg', '_mid.jpg', $file_name);
        $new_midsize_path = str_replace('.jpg', '_mid.jpg', $path);

        $res = $utils->resizeImage($path, 800, 0, $new_midsize_file_name);

        if(file_exists($new_midsize_path)) {
          $new_capture_data_file = $cd_image_file;
          $new_capture_data_file['variant_type'] = isset($cd_image_file['variant_type']) ? $cd_image_file['variant_type'] . ' thumb' : 'thumb';
          $new_capture_data_file['capture_data_file_name'] = $new_capture_data_file['file_name'] = $new_midsize_file_name;
          $new_capture_data_file['file_path'] = $new_midsize_path;
          $new_capture_data_file['file_size'] = filesize($new_midsize_path);

          $ret = $this->repo_storage_controller->execute('createCaptureDatasetImageDerivatives', $new_capture_data_file);

          $file_info[$new_midsize_file_name] = $res;
        }
        else {
          $errors[] = "Unable to generate midsize image $new_midsize_path";
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