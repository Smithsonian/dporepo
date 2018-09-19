<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use finfo;

use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Service\RepoValidateData;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class ValidateImagesController extends Controller
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
    );
    // Valid image mime types.
    $this->valid_image_mimetypes = array(
      image_type_to_mime_type(IMAGETYPE_TIFF_MM) => array('image/tif', 'image/tiff'),
      image_type_to_mime_type(IMAGETYPE_JPEG) => array('image/jpg', 'image/jpeg'),
    );
  }

  /**
   * Validate Images
   *
   * Leveraging PHP's SplFileInfo class
   * See: http://php.net/manual/en/class.splfileinfo.php
   *
   * @param array  $params  Parameters. For now, only 'localpath' is being sent.
   * @return array 
   */
  public function validate($params = array())
  {

    $data = array();
    $job_status = 'metadata ingest in progress';
    $localpath = !empty($params['localpath']) ? $params['localpath'] : false;

    // Throw an exception if the job record doesn't exist.
    if (!$localpath) $data[0]['errors'][] = 'Job directory missing from parameters. Please provide a job directory path.';
    // Set an error if the job record doesn't exist.
    if (!is_dir($localpath)) $data[0]['errors'][] = 'The Job directory doesn\'t exist';

    // If the job directory exists, proceed with validating images.
    if (is_dir($localpath)) {
      // Get the job ID, so errors can be logged to the database.
      $dir_array = explode(DIRECTORY_SEPARATOR, $localpath);
      $job_id = array_pop($dir_array);

      // Search for the data directory.
      $finder = new Finder();
      $finder->path('data')->name('/\.jpg|\.tif$/');
      $finder->in($localpath);

      $i = 0;
      foreach ($finder as $file) {

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

        if (!empty($data[$i]['errors'])) {
          // Set the job_status to 'failed', if not already set.
          if ($job_status !== 'failed') $job_status = 'failed';
          // Log the errors to the database.
          $this->repoValidate->logErrors(
            array(
              'job_id' => $job_id,
              'user_id' => 0,
              'job_log_label' => 'Image Validation',
              'errors' => $data[$i]['errors'],
            )
          );
        }

        $i++;
      }

      // Update the 'job_status' in the 'job' table accordingly.
      $this->repo_storage_controller->execute('setJobStatus', 
        array(
          'job_id' => $job_id, 
          'status' => $job_status, 
          'date_completed' => date('Y-m-d h:i:s')
        )
      );
    }


    return $data;
  }

  /**
   * Get Mime Type
   *
   * @param string  $filename  The file name
   * @return string
   */
  private function get_mime_type($filename = null) {

    if (!empty($filename)) {
      $buffer = file_get_contents($filename);
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      return $finfo->buffer($buffer);
    }

  }

}
