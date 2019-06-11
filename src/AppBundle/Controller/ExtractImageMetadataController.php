<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpKernel\KernelInterface;
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
   * @var object $kernel
   */
  public $kernel;
  
  /**
   * @var string $project_directory
   */
  private $project_directory;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(TokenStorageInterface $token_storage, Connection $conn, KernelInterface $kernel)
  {
    // Usage: $this->u->dumper($variable);
    $this->u = new AppUtilities();
    $this->token_storage = $token_storage;
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
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
   * Extract Metadata from Images
   *
   * @param array  $params  Parameters. For now, only 'localpath' is being sent.
   * @return array
   */
  public function extractMetadata($params = array())
  {

    $data = array();
    $job_status = 'extracting image metadata';
    $localpath = !empty($params['localpath']) ? $params['localpath'] : false;

    // Throw an exception if the job record doesn't exist.
    if (!$localpath) throw $this->createNotFoundException('The Job directory doesn\'t exist - extract_metadata()');

    // Get the job ID, so errors can be logged to the database.
    //@todo- on windows $localpath is being stored as C:\xampp\htdocs\dporepo_dev\web/uploads/repository/[UUID]
    // Temp hack for this
    $dir_array = explode(DIRECTORY_SEPARATOR, $localpath);
    //$uuid = array_pop($dir_array);
    $uuid_path = array_pop($dir_array);
    $uuid_path_array = explode("/", $uuid_path);
    $uuid = array_pop($uuid_path_array);

    $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid));

    // Search for the data directory.
    $finder = new Finder();
    $finder->path('data')->name('/\.jpg|\.JPG|\.tif|\.TIF$/');
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
        $mime_type = $this->getMimeType($file->getPathname());

        // Check if this is a valid image according to our hard-coded arrays above.
        if(array_key_exists($mime_type, $this->valid_image_mimetypes)) {

          // $this->u->dumper('array_key_exists($mime_type, $this->valid_image_mimetypes)');

          // Set metadata, warnings or errors.
          $data[$i] = $this->getMetadataFromImage($file->getPathname());

          // Write the metadata for this file.
          if(array_key_exists('metadata', $data[$i])) {

            // TODO: Remove dumps once tests on Windows are confirmed to work.
            // $this->u->dumper($this->project_directory,0);
            // $this->u->dumper($file->getPath(),0);

            // Create the clean directory path to the file, since that's how it's stored in the database.
            // Turns this: /Users/gor/Documents/Sites/dporepo/web/uploads/repository/3df_5bd21fc4ccd253.95102447/testupload06_usnm_160/data/-s03-
            // Into this: /uploads/repository/3df_5bd21fc4ccd253.95102447/testupload06_usnm_160/data/-s03-
            $file_path = str_replace($this->project_directory . 'web', '', $file->getPath());

            // TODO: Remove dumps once tests on Windows are confirmed to work.
            // $this->u->dumper($file_path);

            // Windows file path fix.
            // Turns this: /uploads/repository/3df_5bd21fc4ccd253.95102447/testupload06_usnm_160/data/-s03-
            // Into this: \uploads\repository\3df_5bd21fc4ccd253.95102447\testupload06_usnm_160\data\-s03-
            $file_path = str_replace('/', DIRECTORY_SEPARATOR, $file_path);

            // Get the file's id in the database.
            $file_data = $this->repo_storage_controller->execute('getRecords', array(
              'base_table' => 'file_upload',
              'fields' => array(),
              'limit' => 1,
              'search_params' => array(
                array('field_names' => array('file_path'), 'search_values' => array($file_path . DIRECTORY_SEPARATOR . $file->getBasename()), 'comparison' => '='),
              ),
              'search_type' => 'AND',
              'omit_active_field' => true,
              )
            );

            // Log an error if the file is not found within the database.
            if (empty($file_data)) $data[$i]['errors'][] = 'Extract Image Metadata - file not found in the database: ' . $file_path . DIRECTORY_SEPARATOR . $file->getBasename();

            // If the record is found, save the metadata to the record.
            if (!empty($file_data) && (count($file_data) === 1)) {
              $id = $this->repo_storage_controller->execute('saveRecord', array(
                'base_table' => 'file_upload',
                'record_id' => $file_data[0]['file_upload_id'],
                'user_id' => 0,
                'values' => array(
                  'metadata' => json_encode($data[$i]['metadata'])
                )
              ));
            }
          }

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
            'job_id' => $job_data['job_id'],
            'uuid' => $job_data['uuid'],
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
        'job_id' => $job_data['job_id'],
        'status' => $job_status,
        'date_completed' => date('Y-m-d H:i:s')
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
  private function getMimeType($filename = null) {

    if (!empty($filename)) {
      $buffer = file_get_contents($filename);
      $finfo = new finfo(FILEINFO_MIME_TYPE);
      return $finfo->buffer($buffer);
    }

  }

  private function getMetadataFromImage($filename = null) {

    $image_type = exif_imagetype($filename);
    if(false === $image_type) {
      return array('errors' => array('Unknown image type - ' . $image_type));
    }

    // Can also ask for EXIF, which is a sub-set of IFD0.
    $sections = 'FILE,COMPUTED,IFD0,THUMBNAIL,EXIF';

    $image_data = @exif_read_data($filename, $sections, true, false);

    // TODO: Extract Exif from XMP
    //
    // Reason:
    // The nmnh-USNM_PAL_00033475-pg-group_01-cam_1-col_cor-0001.jpg file may contain XMP data, rather than EXIF
    // See: https://stackoverflow.com/a/8864064/1298317
    //
    // Error during testing:
    // console.ERROR: Error thrown while running command "app:validate-assets "3df_5ba94c6e6a13a2.78828007" 2 791 subject".
    // Message: "Warning: exif_read_data(nmnh-USNM_PAL_00033475-pg-group_01-cam_1-col_cor-0001.jpg): 
    // Incorrect APP1 Exif Identifier Code" {"exception":"[object] (Symfony\\Component\\Debug\\Exception\\ContextErrorException(code: 0): Warning: 
    // exif_read_data(nmnh-USNM_PAL_00033475-pg-group_01-cam_1-col_cor-0001.jpg):
    // Incorrect APP1 Exif Identifier Code at C:\\xampp\\htdocs\\dporepo_test\\src\\AppBundle\\Controller\\ExtractImageMetadataController.php:254)",
    // "command":"app:validate-assets \"3df_5ba94c6e6a13a2.78828007\" 2 791 subject",
    // "message":"Warning: exif_read_data(nmnh-USNM_PAL_00033475-pg-group_01-cam_1-col_cor-0001.jpg): Incorrect APP1 Exif Identifier Code"}

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
