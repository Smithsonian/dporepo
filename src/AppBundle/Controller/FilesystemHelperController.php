<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpKernel\KernelInterface;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class FilesystemHelperController extends Controller
{
  /**
   * @var object $u
   */
  public $u;

  /**
   * @var object $kernel
   */
  public $kernel;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

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
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(KernelInterface $kernel, string $uploads_directory, string $external_file_storage_path, Connection $conn)
  {
    // Usage: $this->u->dumper($variable);
    $this->u = new AppUtilities();
    $this->repo_storage_controller = new RepoStorageHybridController($conn);

    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $uploads_directory) : $uploads_directory;
    $this->external_file_storage_path = $external_file_storage_path;
  }

  /**
   * @Route("/admin/get_directory_contents/{job_id}", name="get_directory_contents", methods="GET", defaults={"job_id" = null})
   *
   * Get Directory Contents
   *
   * Examples:
   * http://127.0.0.1:8000/admin/get_directory_contents/2&id=#
   * http://127.0.0.1:8000/admin/get_directory_contents/false?path=/testupload05/data/&record_id=2&id=#
   *
   * @param object  $request  Request object
   * @return string
   */
  public function getDirectoryContents(Request $request) {

    $data = $job_data = $parent_job_data = [];
    $data_directory_path = '';

    // The uploads directory path.
    $project_dir = $this->project_directory . $this->uploads_directory;
    // URL attributes
    $job_id = (!empty($request->attributes->get('job_id')) && ($request->attributes->get('job_id') !== 'false')) ? $request->attributes->get('job_id') : false;
    $id = (!empty($request->get('id')) && ($request->get('id') !== '#')) ? DIRECTORY_SEPARATOR . $request->get('id') : '';
    $directory_path = !empty($request->get('path')) ? $request->get('path') : '';
    $record_id = !empty($request->get('record_id')) ? $request->get('record_id') : false;

    if(!empty($directory_path) && !empty($record_id)) {      
      // Get the job ID so it can be added to the uploaded files path.
      $job_data = $this->repo_storage_controller->execute('getRecords', array(
        'base_table' => 'job_import_record',
        'fields' => array(),
        'limit' => 1,
        'search_params' => array(
          0 => array('field_names' => array('job_import_record.record_id'), 'search_values' => array((int)$record_id), 'comparison' => '='),
          1 => array('field_names' => array('job_import_record.record_table'), 'search_values' => array('capture_dataset'), 'comparison' => '=')
        ),
        'search_type' => 'AND',
        'omit_active_field' => true,
        )
      );

      if (!empty($job_data)) {
        $parent_job_data = $this->repo_storage_controller->execute('getRecord', array(
            'base_table' => 'job',
            'id_field' => 'job_id',
            'id_value' => $job_data[0]['job_id'],
            'omit_active_field' => true,
          )
        );
      }
    }

    // Overwrite the $job_id if pulling data from the 'job_import_record' table.
    $job_id = (!empty($job_data) && !empty($parent_job_data)) ? $parent_job_data['uuid'] : $job_id;
    // Set the directory opened state.
    $directory_opened_state = (empty($job_data) && !empty($request->get('id')) && ($request->get('id') === '#')) ? true : false;

    // Retrieve a specific listing from what's been entered in the directory_path field.
    if ($job_data && !empty($job_data) && empty($id)) {
      $search_directory = $project_dir . $job_id . DIRECTORY_SEPARATOR;

      // Search for the data directory.
      $finder = new Finder();
      $finder->path('data');
      $finder->in($search_directory);

      foreach ($finder as $file) {
        if (is_dir($file->getPathname()) && ($file->getFilename() === 'data')) {
          $target_directory = $file->getPathname() . $directory_path;
        }
      }

    } elseif ($job_data && !empty($job_data) && !empty($id) && (DIRECTORY_SEPARATOR === '\\')) {
      $target_directory = $project_dir . $job_id . DIRECTORY_SEPARATOR . ltrim($id, '\\') . DIRECTORY_SEPARATOR;
    } else {
      $target_directory = $project_dir . $job_id . $id . DIRECTORY_SEPARATOR;
    }
    
    // If on a Windows based system, replace forward slashes with backslashes.
    if (DIRECTORY_SEPARATOR === '\\') {
      $target_directory = str_replace('/', '\\', $target_directory);
    }
    // If on a *nix based system, replace backslashes with forward slashes.
    if (DIRECTORY_SEPARATOR === '/') {
      $target_directory = str_replace('\\', '/', $target_directory);
    }

    if (!empty($job_id) && is_dir($target_directory)) {

      $finder = new Finder();
      $finder->in($target_directory);
      $finder->depth('== 0');
      $finder->sortByName();

      foreach ($finder as $file) {

        $this_file_path = str_replace($project_dir, '', $file->getPathname());
        $this_file_path = $this->external_file_storage_path . $this_file_path;
        $this_file_path_array = explode(DIRECTORY_SEPARATOR, $this_file_path);
        $this_file_name = array_pop($this_file_path_array);
        $this_file_id = str_replace($project_dir . $job_id . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $this_external_file_path = str_replace(DIRECTORY_SEPARATOR, '/', $this_file_path);

        // $this->u->dumper($project_dir,0);
        // $this->u->dumper($this_file_id,0);
        // $this->u->dumper($this_file_path,0);
        // $this->u->dumper(is_dir($file->getPathname()),0);
        // $this->u->dumper($directory_opened_state);

        if(is_dir($file->getPathname())) {
          $data[] = array(
            'text' => $this_file_name,
            'children' => true,
            'id' => $this_file_id,
            'icon' => 'glyphicon glyphicon-folder-close',
            'size' => filesize($file->getPathname()),
            'state' => array('opened' => $directory_opened_state, 'disabled' => false)
          );
        }
        else {
          $data[] = array(
            'text' => $this_file_name . '&nbsp;&nbsp;&nbsp;(' . $this->byteConvert(filesize($file->getPathname())) . ')',
            'children' => false,
            'id' => $this_file_id,
            'type' => 'file',
            'icon' => 'glyphicon glyphicon-file',
            'a_attr' => array('href' => '/admin/get_file?path=' . ltrim($this_external_file_path, '/'), 'download' => $this_file_name)
          );
        }
      }
    }

    return $this->json($data);
  }

  /**
   * @Route("/admin/get_file", name="get_file", methods="GET")
   *
   * Get File
   *
   * Examples:
   * http://127.0.0.1:8000/admin/get_file?path=3DRepo/uploads/3df_5b72cbe032b519.30125756/testupload02/bag-info.txt
   *
   * @param object  $request  Request object
   * @return string
   */
  public function getFile(Request $request) {

    $path = !empty($request->get('path')) ? $request->get('path') : '';

    // If the external storage directory isn't in the path, try to set the right path.
    if(strpos($path, $this->external_file_storage_path) !== 0) {

      // Remove the uploads directory if it exists.
      if(strpos($path, str_replace("web", "", $this->uploads_directory)) !== false) {
        if(strpos($path, 'web') !== 0) {
          $path = 'web' . $path;
        }
        $path = str_replace("\\", "/",  $path);
        $path = str_replace(str_replace("\\", "/",  $this->uploads_directory), '', $path);
      }
      $path = str_replace("\\", "/", $this->external_file_storage_path . $path);
      $path = str_replace("//", "/", $path);

      // The complete path should now look like this:
      // /3DRepo/uploads/1E155C38-DC69-E33B-4208-7757D5CDAA35/data/cc/camera/f1978_40-cc_j3a.JPG
    }

    $file_path_array = explode('/', $path);
    $file_name = array_pop($file_path_array);

    // Retrieve a read-stream
    try {
      $filesystem = $this->container->get('oneup_flysystem.assets_filesystem');
      $stream = $filesystem->readStream($path);
      $contents = stream_get_contents($stream);
      // Before calling fclose on the resource, check if it's still valid using is_resource.
      if (is_resource($stream)) fclose($stream);
      // Return a response with a specific content
      $response = new Response($contents);
      // Create the disposition of the file
      $disposition = $response->headers->makeDisposition(
        ResponseHeaderBag::DISPOSITION_ATTACHMENT,
        $file_name
      );
      // Set the content disposition
      $response->headers->set('Content-Disposition', $disposition);
      // Dispatch request
      return $response;
    }
    // Catch the error.
    catch(\League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
      throw $this->createNotFoundException($e->getMessage() . " (File Path: $path)");
    }

  }

  /**
   * Byte Convert
   * Converts bytes to human readable file size.
   *
   * @param int $bytes Bytes
   * @return string
   */
  public function byteConvert($bytes) {
    if ($bytes == 0)
      return "0.00 B";

    $s = array('B', 'KB', 'MB', 'GB', 'TB', 'PB');
    $e = floor(log($bytes, 1024));

    return round($bytes/pow(1024, $e), 2) . ' ' . $s[$e];
  }

}