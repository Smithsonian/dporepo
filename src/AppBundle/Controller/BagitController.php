<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;

use AppBundle\Controller\RepoStorageHybridController;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class BagitController extends Controller
{

  /**
   * @Route("/bagit/create", name="bagit_create", methods={"POST","GET"})
   */
  public function bagit_create(Request $request) {

    $localpath = $request->request->get('localpath');
    if(empty($localpath)) {
      $localpath = $request->query->get('localpath');
    }

    $overwrite_manifest = $request->request->get('overwrite_manifest');
    if(empty($overwrite_manifest)) {
      $overwrite_manifest = $request->query->get('overwrite_manifest');
    }
    if(empty($overwrite_manifest)) {
      $overwrite_manifest = false;
    }

    $create_data_dir = $request->request->get('create_data_dir');
    if(empty($create_data_dir)) {
      $create_data_dir = $request->query->get('create_data_dir');
    }
    if(empty($create_data_dir)) {
      $create_data_dir = true; // By default, create the data dir for the user, and move files into it.
    }

    $flag_warnings_as_errors = $request->request->get('flag_warnings_as_errors');
    if(empty($flag_warnings_as_errors)) {
      $flag_warnings_as_errors = $request->query->get('flag_warnings_as_errors');
    }
    if(empty($flag_warnings_as_errors)) {
      $flag_warnings_as_errors = false;
    }

    $return = array();

    $manifest_contents = NULL;

    require $this->get('kernel')->getRootDir() . "/../vendor/scholarslab/bagit/lib/bagit.php";

    // Make sure bagit manifest does not exist already.
    if(file_exists($localpath . '/manifest-sha1.txt')) {
      if(!$overwrite_manifest) {
        $return['errors'][] = 'A manifest file exists at this path.';
      }
      else  {
        $return['warnings'][] = 'A manifest file exists at this path and will be overwritten.';
      }
    }

    // Make sure file contents are inside a folder named "data"- create folder and move them if need be.
    if(!file_exists($localpath . '/data')) {
      if($create_data_dir) {
        $this->create_datadir_move_files($localpath);
      }
      else {
        $return['errors'][] = 'The data directory for this package is missing.';
      }
    }
    else {
      $package_data_files = $this->get_package_data_files($localpath . '/data');
      if(count($package_data_files) == 0) {
        $return['warnings'][] = 'There are no files in the data directory for this package.';
      }
    }

    if(array_key_exists('errors', $return) && count($return['errors']) > 0) {
      return new JsonResponse($return);
    }

    $bag = new \BagIt($localpath);
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
      $tagmanifest_hash = $bag->manifest->calculateHash($tag_manifest->fileName);
      $return['tag_manifest']['tag_manifest_hash'] = $tagmanifest_hash;
    }

    if(!array_key_exists('errors', $return) || count($return['errors']) == 0) {
      if($flag_warnings_as_errors) {
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

    return new JsonResponse($return);

  }

  /**
   * @Route("/bagit/validate", name="bagit_validate", methods={"POST","GET"})
   */
  public function bagit_validate(Request $request) {

    $localpath = $request->request->get('localpath');
    if(empty($localpath)) {
      $localpath = $request->query->get('localpath');
    }

    $flag_warnings_as_errors = $request->request->get('flag_warnings_as_errors');
    if(empty($flag_warnings_as_errors)) {
      $flag_warnings_as_errors = $request->query->get('flag_warnings_as_errors');
    }
    if(empty($flag_warnings_as_errors)) {
      $flag_warnings_as_errors = false;
    }

    $return = array();

    $manifest_contents = NULL;

    require $this->get('kernel')->getRootDir() . "/../vendor/scholarslab/bagit/lib/bagit.php";
    $missing_bagit_files = $this->validate_folder($localpath);
    if(count($missing_bagit_files) > 0) {
      $return['errors'] = $missing_bagit_files;
    }

    // If we have the manifest and other files, validate the manifest.
    if (!array_key_exists('errors', $return) || count($return['errors']) == 0) {
      $bag = new \BagIt($localpath);
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
      if(!file_exists($localpath . '/data')) {
        $return['errors'][] = 'The data directory for this package is missing.';
      }
      else {
        $package_data_files = $this->get_package_data_files($localpath . '/data');
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
                . $localpath . '/data/' . $pdfilename . ').';
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
      if($flag_warnings_as_errors) {
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

    return new JsonResponse($return);
  }

  function validate_folder($localpath, $message_prefix = 'Missing file: '){
    $missingFiles = [];
    // Bagit doesn't actually care about this file, but it's included in the tag manifest.
    if (!file_exists($localpath . '/bag-info.txt')) {
      $missingFiles[] = $message_prefix . 'Bag Info';
    }
    if (!file_exists($localpath . '/manifest-sha1.txt') && !file_exists($localpath . '/manifest-md5.txt')) {
      $missingFiles[] = $message_prefix . 'Manifest';
    }
    if (!file_exists($localpath . '/bagit.txt')) {
      $missingFiles[] = $message_prefix . 'Bagit';
    }
    if (!file_exists($localpath . '/tagmanifest-sha1.txt')) {
      $missingFiles[] = $message_prefix . 'Tag Manifest';
    }

    return $missingFiles;
  }

  function get_package_data_files($package_data_dir) {

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

  function create_datadir_move_files($package_dir) {

    $package_files = $this->get_package_data_files($package_dir);

    // Create 'data' directory.
    mkdir($package_dir . '/data', 0775);

    // Shuffle into it anything that isn't a bagit file.
    foreach($package_files as $filename) {
      if($filename !== 'bag-info.txt'
        && $filename !== 'manifest-sha1.txt'
        && $filename !== 'bagit.txt'
        && $filename !== 'tagmanifest-sha1.txt') {
        rename($package_dir . '/' . $filename, $package_dir . '/data/' . $filename);
      }
    }

  }

}