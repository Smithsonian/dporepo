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

class ExtractImageMetadataController extends Controller
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
   * Extract Metadata from Images
   *
   * @param array  $params  Parameters. For now, only 'localpath' is being sent.
   * @return array
   */
  public function extract_metadata($params = array())
  {

    $data = array();
    $job_status = 'metadata ingest in progress';
    $localpath = !empty($params['localpath']) ? $params['localpath'] : false;

    // Throw an exception if the job record doesn't exist.
    if (!$localpath) throw $this->createNotFoundException('The Job directory doesn\'t exist');

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

        if($file->getSize() === 0) {
          $data[$i]['errors'][] = 'Zero byte file. No metadata will be extracted - ' . $file->getFilename();
          continue;
        }

        // Validate the mime type.
        $mime_type = $this->get_mime_type($file->getPathname());

        // Check if this is a valid image according to our hard-coded arrays above.
        if(array_key_exists($mime_type, $this->valid_image_mimetypes)) {

          // Set metadata, warnings or errors.
          $data[$i] = $this->get_metadata_from_image($file);

        } else {
          $data[$i]['errors'][] = 'File is not a valid image - ' . $file->getFilename();
        }

      }

      if (!empty($data[$i]['errors'])) {
        // Set the job_status to 'failed', if not already set.
        if ($job_status !== 'failed') $job_status = 'failed';
        // Log the errors to the database.
        $this->repoValidate->logErrors(
          array(
            'job_id' => $job_id,
            'user_id' => 0,
            'job_log_label' => 'Image Metadata Extraction',
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

  private function get_metadata_from_image($filename = null) {

    $image_type = exif_imagetype($filename);
    if(false === $image_type) {
      return array('errors' => array('Unknown image type - ' . $image_type));
    }

    // Can also ask for EXIF, which is a sub-set of IFD0.
    $sections = 'FILE,COMPUTED,IFD0,THUMBNAIL,EXIF';

    $image_data = exif_read_data($filename, $sections, true, false);

    // Read EXIF data.
    if(false === $image_data) {
      return array('warnings' => array('No EXIF data found for ' . $image_type));
    }
    else {
      $metadata_field_values = array();
      $metadata_fields = array(
        'filename' => 'FILE.FileName',
        'file size' => 'FILE.FileSize',
        'file timestamp' => 'FILE.FileDateTime', // UNIX timestamp
        'height' => 'COMPUTED.Height',
        'width' => 'COMPUTED.Width',
        'aperture' => 'COMPUTED.ApertureFNumber',
        'camera make' => 'IFD0.Make',
        'camera model' => 'IFD0.Model',
        'timestamp' => 'IFD0.DateTime',
        'original timestamp' => 'EXIF.DateTimeOriginal',
        'digitized timestamp' => 'EXIF.DateTimeDigitized',
        'exposure' => 'EXIF.ExposureTime',
        'focal length' => 'EXIF.FocalLength',
        'ISO speed' => 'EXIF.ISOSpeedRatings',
        'camera serial' => 'EXIF.UndefinedTag:0xA431',
        'lens model' => 'EXIF.UndefinedTag:0xA434',
        'lens serial' => 'EXIF.UndefinedTag:0xA435'
      );

      foreach ($image_data as $key => $section) {
        foreach ($section as $name => $val) {
          $k1 = "$key.$name";
          // Save values for our desired fields.
          if(in_array($k1, $metadata_fields)) {
            $j = array_keys($metadata_fields, $k1);
            $k0 = $j[0];
            $metadata_field_values[$k0] = print_r($val, true);
          }
        }
      }
      return array('metadata' => $metadata_field_values);
    }
  }

}
