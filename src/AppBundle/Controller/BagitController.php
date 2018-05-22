<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use AppBundle\Controller\RepoStorageHybridController;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

/**
 * Route(service="app.bagit_controller")
 */
class BagitController extends Controller
{
  /**
   * @var string $bagit_path
   */
  public $bagit_path;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var array $bag_files
   */
  private $bag_files;

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

    $this->u = new AppUtilities();
    $this->repo_storage_controller = new RepoStorageHybridController;
    $this->token_storage = $token_storage;

    $this->bagit_path = __DIR__ . '/../../../vendor/scholarslab/bagit/lib/bagit.php';

    // TODO: move this to parameters.yml and bind in services.yml.
    $this->uploads_directory = __DIR__ . '/../../../web/uploads/repository/';

    $this->bag_files = array(
      'bag-info.txt',
      'bagit.txt',
      'manifest-sha1.txt',
      'tagmanifest-sha1.txt',
    );
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

  /**
   * @param array $params Parameters sent to the function.
   * @return array Results from the BagIt creation process.
   *
   * Parameters include:
   *
   * localpath
   * job_id
   * overwrite_manifest
   * create_data_dir
   * flag_warnings_as_errors
   */
  public function bagit_create($params = array(), $container) {

    $data = (object)[];
    $return = array();
    $manifest_contents = NULL;

    // Include the BagIt PHP library
    require_once $this->bagit_path;

    $data->localpath = !empty($params['localpath']) ? $params['localpath'] : false;
    $data->job_id = !empty($params['job_id']) ? $params['job_id'] : false;
    $data->overwrite_manifest = !empty($params['overwrite_manifest']) ? $params['overwrite_manifest'] : false;
    $data->create_data_dir = !empty($params['create_data_dir']) ? $params['create_data_dir'] : true;
    $data->flag_warnings_as_errors = !empty($params['flag_warnings_as_errors']) ? $params['flag_warnings_as_errors'] : false;

    // If there is no directory path provided, return early.
    if(!$data->localpath) {
      $return['errors'][] = 'Error: Directory path not provided.';
      return $return;
    }

    // If there is no job ID provided, return early.
    if(!$data->job_id) {
      $return['errors'][] = 'Error: Job ID not provided.';
      return $return;
    }

    // Make sure bagit manifest does not exist already.
    if(file_exists($data->localpath . '/manifest-sha1.txt')) {
      if(!$data->overwrite_manifest) {
        $return['errors'][] = 'A manifest file exists at this path.';
      }
      else  {
        $return['warnings'][] = 'A manifest file exists at this path and will be overwritten.';
      }
    }

    // Make sure file contents are inside a folder named "data" - create folder and move them if need be.
    if(!file_exists($data->localpath . '/data')) {
      if($data->create_data_dir) {
        $this->create_datadir_move_files($data->localpath);
      }
      else {
        $return['errors'][] = 'The data directory for this package is missing.';
      }
    }
    else {
      $package_data_files = $this->get_package_data_files($data->localpath . '/data');
      if(count($package_data_files) == 0) {
        $return['warnings'][] = 'There are no files in the data directory for this package.';
      }
    }

    if(array_key_exists('errors', $return) && count($return['errors']) > 0) {
      return $return;
    }

    // Instantiate the BagIt class
    $bag = new \BagIt($data->localpath);
    $bag->update();

    $manifest_filename = $bag->manifest->fileName;
    $manifest_contents = $bag->manifest->read($manifest_filename);

    // Is the manifest empty? If so, return this as a warning.
    if(empty($manifest_contents)) {
      $return['errors'][] = 'The Bagit manifest is empty.';
    }

    if(NULL !== $manifest_contents) {
      $tag_manifest = $bag->tagManifest;
      $tag_manifest->tag_manifest_contents = $bag->tagManifest->read($tag_manifest->fileName);
      $return['manifest'] = array(
        'bagit_package_path' => $bag->manifest->pathPrefix,
        'hash_encoding' => $bag->manifest->hashEncoding,
        'file_encoding' => $bag->manifest->fileEncoding,
        'manifest_filename' => $bag->manifest->fileName,
        'manifest_contents' => $manifest_contents,
      );
      $return['tag_manifest'] = (array)$tag_manifest;

      // Let's get crazy- hash the tagmanifest file too(!)
      $return['tag_manifest']['tag_manifest_hash'] = $bag->manifest->calculateHash($tag_manifest->fileName);
    }

    if(!array_key_exists('errors', $return) || count($return['errors']) == 0) {
      if($data->flag_warnings_as_errors) {
        if(!array_key_exists('warnings', $return) || count($return['warnings']) == 0) {
          $return['result'] = 'success';
        }
        else {
          $return['result'] = 'fail';
        }
      }
      else {
        $return['result'] = 'success';
      }
    }
    else {
      $return['result'] = 'fail';
    }

    // Log errors to the database.
    if(isset($return['errors']) && !empty($return['errors'])) {
      $this->log_errors(
        array(
          'job_id' => $data->job_id,
          'user_id' => 0,
          'errors' => $return['errors'],
        ),
        $container
      );
    }

    return $return;
  }

  /**
   * @param array $params Parameters sent to the function.
   * @param object $container The container.
   * @return array Results from the BagIt validation process.
   *
   * Parameters include:
   *
   * localpath
   * flag_warnings_as_errors
   */
  public function bagit_validate($params = array(), $container) {

    $data = (object)[];
    $return = array();
    $manifest_contents = NULL;
    $data->job_status = 'complete';

    // Include the BagIt PHP library
    require_once $this->bagit_path;

    $data->localpath = !empty($params['localpath']) ? $params['localpath'] : false;
    $data->flag_warnings_as_errors = !empty($params['flag_warnings_as_errors']) ? $params['flag_warnings_as_errors'] : false;

    // If there is no directory path provided, return early.
    if(!$data->localpath) {
      $return['errors'][] = 'Error: Directory path not provided.';
      return $return;
    }

    // Get the job ID, so errors can be logged to the database.
    $dir_array = explode('/', $data->localpath);
    $data->job_id = array_pop($dir_array);

    // Validate that all of the BagIt files exist.
    // (bag-info.txt, bagit.txt, manifest-sha1.txt, tagmanifest-sha1.txt)
    $validate_folder = $this->validate_folder($data->localpath);

    // If no 'path_to_bag' is returned, something went wrong. Return early.
    // This occured during development when the 'job_status' field in the 'job' database table  
    // and filesystem state were out of sync. ('job_status' was 'in progress', but filesystem had no bag)
    // OR ---
    // Should the bag be automatically created???
    if(!isset($validate_folder['path_to_bag'])) {

      // // Scenario #1: Return error.
      // $return['errors'][] = 'Error: Path to bag not found.';
      // return $return;

      // Scenario #2: Create the bag.
      $params = array(
        'localpath' => $data->localpath,
        'job_id' => $data->job_id,
      );
      $bag_results = $this->bagit_create($params, $container);

      if(isset($bag_results['result']) && ($bag_results['result'] !== 'success')) {
        // If the bagit_create method failed, return the errors the method produced.
        foreach ($bag_results['errors'] as $bkey => $bvalue) {
          $return['errors'][] = $bvalue;
        }
      } else {
        // If all 4 BagIt-based files were missing, they were just created, so go ahead and unset the 'missing_files' variable.
        if(isset($validate_folder['missing_files']) && (count($validate_folder['missing_files']) === 4)) {
          unset($validate_folder['missing_files']);
        }
        // Set the path to the bag (without the trailing forward slash).
        $return['path_to_bag'] = substr($bag_results['manifest']['bagit_package_path'], 0, -1);
      }

    } else {
      // The directory path to the bag.
      $return['path_to_bag'] = $validate_folder['path_to_bag'];
    }

    // If there are missing BagIt files, add them to $return['errors'].
    if(isset($validate_folder['missing_files']) && (count($validate_folder['missing_files']) > 0)) {
      $return['errors'][] = $validate_folder['missing_files'];
    }

    // If we have the manifest and other files, validate the manifest.
    if (!array_key_exists('errors', $return) || count($return['errors']) == 0) {

      // Instantiate the BagIt class
      $bag = new \BagIt($return['path_to_bag']);
      $validation = $bag->validate();

      if (count($validation) > 0) {
        // We can only validate if we have a manifest.
        foreach ($validation as $message) {
          $full_message = 'Missing file: ';
          foreach ($message as $k => $value) {
            $full_message .= ' ' . $value;
          }
          $return['errors'][] = $full_message;
        }
      }

      $manifest_filename = $bag->manifest->fileName;
      $manifest_contents = $bag->manifest->read($manifest_filename);

      $package_data_files = array();

      // Bagit->validate should be checking this anyway.
      if(!file_exists($return['path_to_bag'] . '/data')) {
        $return['errors'][] = 'The data directory for this package is missing.';
      }
      else {
        $package_data_files = $this->get_package_data_files($return['path_to_bag'] . '/data');
      }

      // Is the manifest empty? If so, return this as a warning.
      if(empty($manifest_contents)) {
        if(count($package_data_files) > 0) {
          $return['errors'][] = 'The Bagit manifest is empty but the data directory for the package is not.';
        }
      }
      else {
        // Are there other files in the package that aren't Bagit files and aren't in the manifest?
        // Let the user know.
        if(count($manifest_contents) > 0 && count($manifest_contents) !== count($package_data_files)) {
          foreach($package_data_files as $pdfilename) {
            if(!array_key_exists('data/' . $pdfilename, $manifest_contents)) {
              $return['warnings'][] = 'File ' . $pdfilename
                . ' is not included in the manifest, but exists in the data directory for the Bagit package ('
                . $data->localpath . '/data/' . $pdfilename . ').';
            }
          }
        }
      }
    } // If we have the basic files we need to perform validation (tagmanifest, manifest, info).

    if(NULL !== $manifest_contents) {
      $tag_manifest = $bag->tagManifest;
      $tag_manifest->tag_manifest_contents = $bag->tagManifest->read($tag_manifest->fileName);
      $return['manifest'] = array(
        'bagit_package_path' => $bag->manifest->pathPrefix,
        'hash_encoding' => $bag->manifest->hashEncoding,
        'file_encoding' => $bag->manifest->fileEncoding,
        'manifest_filename' => $bag->manifest->fileName,
        'manifest_contents' => $manifest_contents,
      );
      $return['tag_manifest'] = (array)$tag_manifest;

      // Let's get crazy- hash the tagmanifest file too(!)
      $tagmanifest_hash = $bag->manifest->calculateHash($tag_manifest->fileName);
      $return['tag_manifest']['tag_manifest_hash'] = $tagmanifest_hash;
    }

    if(!array_key_exists('errors', $return) || count($return['errors']) == 0) {
      if($data->flag_warnings_as_errors) {
        if(!array_key_exists('warnings', $return) || count($return['warnings']) == 0) {
          $return['result'] = 'success';
        }
        else {
          $return['result'] = 'fail';
        }
      }
      else {
        $return['result'] = 'success';
      }
    }
    else {
      $return['result'] = 'fail';
    }

    // Log errors to the database.
    if(isset($return['errors']) && !empty($return['errors'])) {
      $this->log_errors(
        array(
          'job_id' => $data->job_id,
          'user_id' => 0,
          'errors' => $return['errors'],
        ),
        $container
      );
      // Set the job_status to 'failed'.
      $data->job_status = 'failed';
    }

    // Update the 'job_status' in the 'job' table accordingly.
    $this->repo_storage_controller->setContainer($container);
    $this->repo_storage_controller->execute('saveRecord', array(
      'base_table' => 'job',
      'record_id' => $data->job_id,
      'user_id' => 0,
      'values' => array(
        'job_status' => $data->job_status,
        'date_completed' => date('Y-m-d h:i:s'),
        'qa_required' => 0,
        'qa_approved_time' => null,
      )
    ));

    return $return;
  }

  /**
   * @param array $localpath Path to bag .txt files.
   * @return array Missing files.
   */
  public function validate_folder($localpath, $message_prefix = 'Missing file: ') {

    $bag_files_found = [];
    $data = [];

    $finder = new Finder();
    $finder->files()->in($localpath . '/');
    $finder->files()->name('*.txt');

    // Create an array of all .txt files found.
    foreach ($finder as $file) {
      // Since it's allowed to upload recursive directory structures, a bag can be found anywhere.
      // Get the path to the bag.
      $path_to_bag = str_replace($localpath . '/', '', $file->getPathname());
      $data['path_to_bag'] = $localpath . '/' . str_replace(basename($file->getRealPath()), '', $path_to_bag);
      // Create an array of all BagIt .txt files found.
      $bag_files_found[] = basename($file->getRealPath());
    }

    // Create an array of missing BagIt .txt files.
    foreach ($this->bag_files as $key => $value) {
      if(!in_array($value, $bag_files_found)) {
        $data['missing_files'][] = $message_prefix . $value;
      }
    }

    return $data;
  }

  /**
   * @param array $package_data_dir Path to the package's data directory.
   * @return array The package's data files.
   */
  public function get_package_data_files($package_data_dir) {

    $package_data_files = scandir($package_data_dir);

    if(count($package_data_files) > 1) {
      $key = array_keys($package_data_files, '.');
      if(count($key) > 0) {
        unset($package_data_files[$key[0]]);
      }

      $key = array_keys($package_data_files, '..');
      if(count($key) > 0) {
        unset($package_data_files[$key[0]]);
      }

      $key = array_keys($package_data_files, '.DS_Store');
      if(count($key) > 0) {
        unset($package_data_files[$key[0]]);
      }

    }

    return $package_data_files;
  }

  /**
   * @param array $package_dir Path to the package's directory.
   * @return null
   */
  public function create_datadir_move_files($package_dir) {

    $package_files = $this->get_package_data_files($package_dir);

    // Create 'data' directory.
    mkdir($package_dir . '/data', 0775);

    // Shuffle into it anything that isn't a BagIt-based file or CSV file.
    foreach($package_files as $filename) {
      if(!in_array($filename, $this->bag_files) && !strstr($filename, '.csv')) {
        rename($package_dir . '/' . $filename, $package_dir . '/data/' . $filename);
      }
    }

  }

  /**
   * @param array $params Parameters for inserting into the database.
   * @param object $container The container.
   * @return null
   */
  public function log_errors($params = array(), $container) {

    if(!empty($params)) {
      foreach ($params['errors'] as $ekey => $error) {
        $this->repo_storage_controller->setContainer($container);
        $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job_log',
          'user_id' => $params['user_id'],
          'values' => array(
            'job_id' => $params['job_id'],
            'job_log_status' => 'error',
            'job_log_label' => 'BagIt Validation',
            'job_log_description' => $error,
          )
        ));
      }
    }

  }

}