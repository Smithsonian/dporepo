<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use finfo;

use AppBundle\Controller\RepoStorageHybridController;

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
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var array $token_storage
   */
  private $token_storage;

  /**
  * Constructor
  * @param object  $u  Utility functions object
  */
  public function __construct(TokenStorageInterface $token_storage)
  {
    // Usage: $this->u->dumper($variable);
    $this->u = new AppUtilities();
    $this->repo_storage_controller = new RepoStorageHybridController;
    $this->token_storage = $token_storage;
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
   * @param int  $job_id  The job ID
   * @return array 
   */
  public function validate($params = array(), $container)
  {

    $data = array();
    $localpath = !empty($params['localpath']) ? $params['localpath'] : false;

    // Throw an exception if the job record doesn't exist.
    if (!$localpath) throw $this->createNotFoundException('The Job directory doesn\'t exist');

    // Search for the data directory.
    $finder = new Finder();
    $finder->path('data')->name('/\.jpg|\.tif$/');
    $finder->in($localpath);

    $i = 0;
    foreach ($finder as $file) {

      // $this->u->dumper($file->getPathname());

      if (!$file->isFile()) $data[$i]['errors'][] = 'File is not a valid image.';

      if ($file->isFile()) {

        $data[$i]['errors'] = array();

        // $file->getMTime() — Gets the last modified time
        // $this->u->dumper($file->getMTime());

        if($file->getSize() === 0) {
          $data[$i]['errors'][] = 'Zero byte file. No further checks will be performed.';
          continue;
        }

        // Validate the mime type.
        $mime_type = $this->get_mime_type($file->getPathname());

        // Check if this is a valid image according to our hard-coded arrays above.
        if(array_key_exists($mime_type, $this->valid_image_mimetypes)) {
          // Check if the image file extensions matches the actual mime type.
          $mime_type_for_extension = $this->valid_image_types[strtolower($file->getExtension())];
          if($mime_type_for_extension !== $mime_type) {
            $data[$i]['errors'][] = 'File extension does not match the image type.';
          }
        } else {
          $data[$i]['errors'][] = 'File is not a valid image.';
        }

      }

      $data[$i]['file_name'] = $file->getFilename();
      $data[$i]['file_size'] = $file->getSize();
      $data[$i]['file_extension'] = $file->getExtension();
      $data[$i]['file_mime_type'] = $this->get_mime_type($file->getPathname());

      $i++;
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

  /**
   * @param object $container The container.
   * @return array The next directory to validate.
   */
  public function needs_validation_checker($container) {

    $directory = null;

    // Check the database to find the next job which hasn't had a BagIt validation performed against it.
    $this->repo_storage_controller->setContainer($container);
    $data = $this->repo_storage_controller->execute('getRecords', array(
        'base_table' => 'job',
        'fields' => array(),
        'search_params' => array(
          0 => array(
            'field_names' => array(
              'job_status'
            ),
            'search_values' => array(
              'in progress'
            ),
            'comparison' => '='
          )
        ),
        'search_type' => 'AND',
        'sort_fields' => array(
          0 => array('field_name' => 'date_created')
        ),
        'limit' => array('limit_start' => 1),
        'omit_active_field' => true,
      )
    );

    if(!empty($data)) {
      $directory = $this->uploads_directory . $data[0]['job_id'];
    }

    return $directory;
  }

}
