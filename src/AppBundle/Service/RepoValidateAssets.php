<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Doctrine\DBAL\Driver\Connection;
use finfo;

use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Service\RepoValidateData;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class RepoValidateAssets implements RepoValidateAssetsInterface
{
  /**
   * @var object $u
   */
  public $u;

  /**
   * @var string $valid_image_types
   */
  private $valid_image_types;

  /**
   * @var string $valid_image_mimetypes
   */
  private $valid_image_mimetypes;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var object $repo_validate
   */
  private $repo_validate;

  /**
  * Constructor
  * @param object  $u  Utility functions object
  */
  public function __construct(Connection $conn)
  {
    // Usage: $this->u->dumper($variable);
    $this->u = new AppUtilities();
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->repo_validate = new RepoValidateData($conn);
    // Valid image types.
    $this->valid_image_types = array(
      'tif' => image_type_to_mime_type(IMAGETYPE_TIFF_MM),
      'tiff' => image_type_to_mime_type(IMAGETYPE_TIFF_MM),
      'jpg' => image_type_to_mime_type(IMAGETYPE_JPEG),
      'jpeg' => image_type_to_mime_type(IMAGETYPE_JPEG),
      'cr2' => 'image/x-canon-cr2',
      'dng' => image_type_to_mime_type(IMAGETYPE_TIFF_MM),
    );
    // Valid image mime types.
    $this->valid_image_mimetypes = array(
      image_type_to_mime_type(IMAGETYPE_TIFF_MM) => array('image/tif', 'image/tiff'),
      image_type_to_mime_type(IMAGETYPE_JPEG) => array('image/jpg', 'image/jpeg'),
      'image/x-canon-cr2' => array('image/x-canon-cr2'),
    );
  }

  /**
   * Validate Assets
   *
   * Leveraging PHP's SplFileInfo class
   * See: http://php.net/manual/en/class.splfileinfo.php
   *
   * @param array  $params  Parameters. For now, only 'localpath' is being sent.
   * @return array 
   */
  public function validate_assets($params = array())
  {

    $data = array();
    $job_status = 'model validation in progress';
    $localpath = !empty($params['localpath']) ? $params['localpath'] : false;

    // Set an error if the job directory path parameter doesn't exist.
    if (!$localpath) $data[0]['errors'][] = 'Job directory missing from parameters. Please provide a job directory path.';
    // Set an error if the job directory doesn't exist.
    if (!is_dir($localpath)) $data[0]['errors'][] = 'The Job directory doesn\'t exist';

    // If the job directory exists, proceed with validating images.
    if (is_dir($localpath)) {
      // Get the job ID, so errors can be logged to the database.
      //@todo- on windows $localpath is being stored as C:\xampp\htdocs\dporepo_dev\web/uploads/repository/[UUID]
      // Temp hack for this
      $dir_array = explode(DIRECTORY_SEPARATOR, $localpath);
      //$uuid = array_pop($dir_array);
      $uuid_path = array_pop($dir_array);
      $uuid_path_array = explode("/", $uuid_path);
      $uuid = array_pop($uuid_path_array);
      $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid));

      // Set an error if the job record doesn't exist.
      if (empty($job_data)) {
        $data[0]['errors'][] = 'The job record doesn\'t exist in the database. UUID: ' . $uuid;
        return $data;
      }

      // Validate images.
      $result = $this->validate_images($localpath);

      if (!empty($result)) {
        foreach ($result as $rkey => $rvalue) {
          // Log the errors to the database.
          if (!empty($result[$rkey]['errors'])) {
            // Set the job_status to 'failed'.
            $job_status = 'failed';
            $this->repo_validate->logErrors(
              array(
                'job_id' => $job_data['job_id'],
                'user_id' => 0,
                'job_log_label' => 'Asset Validation',
                'errors' => $result[$rkey]['errors'],
              )
            );
          }
        }
      }

      // Validate image pairs (if the job hasn't failed).
      if ($job_status !== 'failed') {
        // Run the image pairs validation.
        $result_pairs = $this->validate_image_pairs($result, $job_status);
        // Log the errors to the database.
        if (!empty($result_pairs)) {
          // Set the job_status to failed.
          if ($job_status !== 'failed') $job_status = 'failed';
          foreach ($result_pairs as $rkey => $rvalue) {
            $this->repo_validate->logErrors(
              array(
                'job_id' => $job_data['job_id'],
                'user_id' => 0,
                'job_log_label' => 'Asset Validation',
                'errors' => $rvalue,
              )
            );
          }
        }
      }

      // Update the 'job_status' in the 'job' table accordingly.
      $res = $this->repo_storage_controller->execute('setJobStatus', 
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
   * Validate Images
   * @param array $localpath The local path to uploaded assets..
   * @return array containing success/fail value, and any messages.
   */
  public function validate_images($localpath)
  {

    $data = array();

    if (!empty($localpath)) {
      // Search for the data directory.
      $finder = new Finder();
      $finder->path('data/');
      // For some reason, regex is not reliable. Commented-out for now.
      # $finder->path('data')->name('/\.jpg|\.tif|\.cr2|\.dng$/');
      $finder->in($localpath);

      $i = 0;
      foreach ($finder as $file) {

        if (!$file->isDir()) {

          if (!$file->isFile()) $data[$i]['errors'][] = 'File is not a valid image.';

          // If this is 1) a file, and 2) the file extension exists in the $this->valid_image_types array, process.
          if ($file->isFile() && array_key_exists(strtolower($file->getExtension()), $this->valid_image_types)) {

            $data[$i]['errors'] = array();

            // $file->getMTime() — Gets the last modified time
            // $this->u->dumper($file->getMTime());

            if($file->getSize() === 0) {
              $data[$i]['errors'][] = 'Zero byte file. No further checks will be performed - ' . $file->getFilename();
              continue;
            }

            // Validate the mime type.
            $mime_type = $this->getMimeType($file->getPathname());

            // $this->u->dumper($mime_type,0);

            // Check if this is a valid image according to our hard-coded arrays above.
            if(array_key_exists($mime_type, $this->valid_image_mimetypes)) {
              // Check if the image file extensions matches the actual mime type.
              $mime_type_for_extension = $this->valid_image_types[strtolower($file->getExtension())];
              if($mime_type_for_extension !== $mime_type) {
                $data[$i]['errors'][] = 'File extension does not match the image type - ' . $file->getFilename();
              }
            } else {
              $data[$i]['errors'][] = 'File is not a valid image 2 - ' . $file->getFilename();
            }

          }

          $data[$i]['file_name'] = strtolower($file->getFilename());
          $data[$i]['file_size'] = $file->getSize();
          $data[$i]['file_extension'] = strtolower($file->getExtension());
          $data[$i]['file_mime_type'] = $this->getMimeType($file->getPathname());

          $i++;
        }
      }

    }

    return $data;
  }

  /**
   * Validate Image Pairs
   * @param array $data The data to validate.
   * @return array containing success/fail value, and any messages.
   */
  public function validate_image_pairs($data = array(), $job_status = '') {

    $return = array();
    $exclude_model_extensions = array('obj', 'ply', 'gltf', 'glb');

    // // If no data is passed, set a message.
    // if (empty($data)) $return['messages'][] = 'No image pairs to validate. Please provide an array of files to validate.';

    // If data is passed, go ahead and perform the validation.
    if (!empty($data)) {

      // Create an array of all of the file extensions.
      $all_file_extensions = array();
      foreach($data as $key => $value) {
        // Exclude the 'file_name_map.csv' file, and ignore model file extensions.
        if (($value['file_name'] !== 'file_name_map.csv') && !in_array($value['file_extension'], $exclude_model_extensions)) {
          array_push($all_file_extensions, $value['file_extension']);
        }
      }

      $unique_file_extensions = array_unique($all_file_extensions);

      // If there are more than 2 unique file types present, set an error.
      // if (count($unique_file_extensions) > 2) {
      //   $return[]['errors'] = 'More than 2 file types detected';
      // }

      $image_pair_type = null;
      if (in_array('jpg', $all_file_extensions)) $image_pair_type = 'jpg';
      if (in_array('tif', $all_file_extensions)) $image_pair_type = 'tif';

      if (!empty($image_pair_type) && (count($unique_file_extensions) === 2)) {

        // Create an array of all of the files, with the file names as the keys, and the extensions as the values.
        $all_files = array();
        foreach($data as $key => $value) {
          $all_files[$value['file_name']] = $value['file_extension'];
        }

        // Validate for image pairs.
        if (count($all_files)) {
          foreach($all_files as $fkey => $fvalue) {

            // The file's base name (without the extension).
            $file_basename = pathinfo($fkey, PATHINFO_FILENAME);

            if ($fkey !== 'file_name_map.csv') {
              switch($fvalue) {
                case 'cr2':
                case 'dng':

                  // $this->u->dumper($image_pair_type,0);
                  // $this->u->dumper($file_basename . '.' . $image_pair_type,0);
                  // $this->u->dumper($all_files);

                  // Set an error if a corresponding jpg or tif file doesn't exist.
                  if (!array_key_exists($file_basename . '.' . $image_pair_type, $all_files)) {
                    $return[]['errors'] = 'Corresponding ' . strtoupper($image_pair_type) . ' not found for ' . strtoupper($fvalue) . ': ' . $fkey;
                  }
                  break;
                default:

                  // We want to check against the "other" file type present.
                  // So, remove the $image_pair_type file extension from the $unique_file_extensions array.
                  $key = array_search($image_pair_type, $unique_file_extensions);
                  if (false !== $key) unset($unique_file_extensions[$key]);
                  $unique_file_extensions = array_values($unique_file_extensions);

                  // Set an error if a corresponding cr2 or dng doesn't exist.
                  if (!array_key_exists($file_basename . '.' . $unique_file_extensions[0], $all_files)) {
                    $return[]['errors'] = 'Corresponding ' . strtoupper($unique_file_extensions[0]) . ' not found for ' . strtoupper($fvalue) . ': ' . $fkey;
                  }
              }
            }

          }
        }
      }

    }

    return $return;
  }

  /**
   * Get Mime Type
   *
   * @param string  $filename  The file name
   * @return string
   */
  public function getMimeType($filename = null) {

    if (!empty($filename)) {
      $buffer = file_get_contents($filename);
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      return $finfo->buffer($buffer);
    }

  }

}
