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
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * @var string $valid_image_types
   */
  private $valid_image_types;

  /**
   * @var string $valid_image_types
   */
  private $valid_image_mimetypes;

  /**
   * @var array $token_storage
   */
  private $token_storage;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var object $repoValidate
   */
  private $repoValidate;

  /**
  * Constructor
  * @param object  $u  Utility functions object
  */
  public function __construct(TokenStorageInterface $token_storage, Connection $conn)
  {
    // Usage: $this->u->dumper($variable);
    $this->u = new AppUtilities();
    $this->token_storage = $token_storage;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->repoValidate = new RepoValidateData($conn);
    // TODO: move this to parameters.yml and bind in services.yml.
    $ds = DIRECTORY_SEPARATOR;
    // $this->uploads_directory = $ds . 'web' . $ds . 'uploads' . $ds . 'repository' . $ds;
    $this->uploads_directory = __DIR__ . '' . $ds . '..' . $ds . '..' . $ds . '..' . $ds . 'web' . $ds . 'uploads' . $ds . 'repository' . $ds;
    // Valid image types.
    $this->valid_image_types = array(
      'tif' => image_type_to_mime_type(IMAGETYPE_TIFF_MM),
      'tiff' => image_type_to_mime_type(IMAGETYPE_TIFF_MM),
      'jpg' => image_type_to_mime_type(IMAGETYPE_JPEG),
      'jpeg' => image_type_to_mime_type(IMAGETYPE_JPEG),
      'cr2' => image_type_to_mime_type(IMAGETYPE_JPEG),
      'dng' => image_type_to_mime_type(IMAGETYPE_JPEG),
    );
    // Valid image mime types.
    $this->valid_image_mimetypes = array(
      image_type_to_mime_type(IMAGETYPE_TIFF_MM) => array('image/tif', 'image/tiff'),
      image_type_to_mime_type(IMAGETYPE_JPEG) => array('image/jpg', 'image/jpeg'),
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
    $job_status = 'metadata ingest in progress';
    $localpath = !empty($params['localpath']) ? $params['localpath'] : false;

    // Set an error if the job directory path parameter doesn't exist.
    if (!$localpath) $data[0]['errors'][] = 'Job directory missing from parameters. Please provide a job directory path.';
    // Set an error if the job directory doesn't exist.
    if (!is_dir($localpath)) $data[0]['errors'][] = 'The Job directory doesn\'t exist';

    // If the job directory exists, proceed with validating images.
    if (is_dir($localpath)) {
      // Get the job ID, so errors can be logged to the database.
      $dir_array = explode(DIRECTORY_SEPARATOR, $localpath);
      $uuid = array_pop($dir_array);
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
            $this->repoValidate->logErrors(
              array(
                'job_id' => $job_data['job_id'],
                'user_id' => 0,
                'job_log_label' => 'Image Validation',
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
            $this->repoValidate->logErrors(
              array(
                'job_id' => $job_data['job_id'],
                'user_id' => 0,
                'job_log_label' => 'Image Validation',
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
      $finder->path('data')->name('/\.jpg|\.tif|\.cr2|\.dng$/');
      $finder->in($localpath);

      $i = 0;
      foreach ($finder as $file) {

        if (!$file->isDir()) {

          if (!$file->isFile()) $data[$i]['errors'][] = 'File is not a valid image.';

          if ($file->isFile()) {

            $data[$i]['errors'] = array();

            // $file->getMTime() — Gets the last modified time
            // $this->u->dumper($file->getMTime());

            if($file->getSize() === 0) {
              $data[$i]['errors'][] = 'Zero byte file. No further checks will be performed - ' . $file->getFilename();
              continue;
            }

            // Validate the mime type.
            $mime_type = $this->get_mime_type($file->getPathname());

            // $this->u->dumper($mime_type,0);

            // Check if this is a valid image according to our hard-coded arrays above.
            if(array_key_exists($mime_type, $this->valid_image_mimetypes)) {
              // Check if the image file extensions matches the actual mime type.
              $mime_type_for_extension = $this->valid_image_types[strtolower($file->getExtension())];
              if($mime_type_for_extension !== $mime_type) {
                $data[$i]['errors'][] = 'File extension does not match the image type - ' . $file->getFilename();
              }
            } else {
              $data[$i]['errors'][] = 'File is not a valid image - ' . $file->getFilename();
            }

          }

          $data[$i]['file_name'] = $file->getFilename();
          $data[$i]['file_size'] = $file->getSize();
          $data[$i]['file_extension'] = $file->getExtension();
          $data[$i]['file_mime_type'] = $this->get_mime_type($file->getPathname());

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

    // // If no data is passed, set a message.
    // if (empty($data)) $return['messages'][] = 'No image pairs to validate. Please provide an array of files to validate.';

    // If data is passed, go ahead and perform the validation.
    if (!empty($data)) {

      // Create an array of all of the file extensions.
      $all_file_extensions = array();
      foreach($data as $key => $value) {
        array_push($all_file_extensions, $value['file_extension']);
      }

      $image_pair_type = null;
      if (in_array('tif', $all_file_extensions)) $image_pair_type = 'tif';
      if (in_array('cr2', $all_file_extensions)) $image_pair_type = 'cr2';
      if (in_array('dng', $all_file_extensions)) $image_pair_type = 'dng';

      if (!empty($image_pair_type)) {

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

            switch($fvalue) {
              case 'jpg':
                // Set an error if a corresponding tif, cr2, dng, etc. doesn't exist.
                if (!array_key_exists($file_basename . '.' . $image_pair_type, $all_files)) {
                  $return[]['errors'] = 'Corresponding ' . strtoupper($image_pair_type) . ' not found for JPG: ' . $file_basename . '.jpg';
                }
                break;
              default:
                // Set an error if a corresponding jpg doesn't exist.
                if (!array_key_exists($file_basename . '.jpg', $all_files)) {
                  $return[]['errors'] = 'Corresponding JPG not found for ' . strtoupper($fvalue) . ': ' . $fkey;
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
  public function get_mime_type($filename = null) {

    if (!empty($filename)) {
      $buffer = file_get_contents($filename);
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      return $finfo->buffer($buffer);
    }

  }

}
