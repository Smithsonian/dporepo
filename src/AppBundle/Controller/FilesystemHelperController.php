<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class FilesystemHelperController extends Controller
{
  /**
   * @var object $u
   */
  public $u;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * @var string $uploads_directory
   */
  private $browser_path;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct()
  {
    // Usage: $this->u->dumper($variable);
    $this->u = new AppUtilities();
    $this->repo_storage_controller = new RepoStorageHybridController();

    // TODO: move this to parameters.yml and bind in services.yml.
    $ds = DIRECTORY_SEPARATOR;
    $this->uploads_directory = $ds . 'web' . $ds . 'uploads' . $ds . 'repository' . $ds;

    // Remove Symfony's 'web' directory for the browser path.
    $this->browser_path = str_replace('web' . $ds, '', $this->uploads_directory);
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
  public function get_directory_contents(Request $request) {

    $data = $job_data = [];
    $data_directory_path = '';

    // The uploads directory path.
    $project_dir = $this->get('kernel')->getProjectDir() . $this->uploads_directory;
    // URL attributes
    $job_id = (!empty($request->attributes->get('job_id')) && ($request->attributes->get('job_id') !== 'false')) ? $request->attributes->get('job_id') : false;
    $id = (!empty($request->get('id')) && ($request->get('id') !== '#')) ? DIRECTORY_SEPARATOR . $request->get('id') : '';
    $directory_path = !empty($request->get('path')) ? $request->get('path') : '';
    $record_id = !empty($request->get('record_id')) ? $request->get('record_id') : false;

    if(!empty($directory_path) && !empty($record_id)) {
      // Set the container.
      $this->repo_storage_controller->setContainer($this->container);
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
    }

    // Overwrite the $job_id if pulling data from the 'job_import_record' table.
    $job_id = ($job_data && !empty($job_data)) ? $job_data[0]['job_id'] : $job_id;
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

        $path_array = explode(DIRECTORY_SEPARATOR, $file->getPathname());
        $last_path_array_item = array_pop($path_array);

        if (is_dir($file->getPathname()) && ($last_path_array_item === 'data')) {
          $target_directory = $file->getPathname() . $directory_path;
        }
      }

    } elseif ($job_data && !empty($job_data) && !empty($id) && (DIRECTORY_SEPARATOR === '\\')) {
      $target_directory = ltrim($id, '\\') . DIRECTORY_SEPARATOR;
    } else {
      $target_directory = $project_dir . $job_id . $id . DIRECTORY_SEPARATOR;
    }

    // $this->u->dumper($target_directory);

    if (!empty($job_id) && is_dir($target_directory)) {

      $finder = new Finder();
      $finder->in($target_directory);
      $finder->depth('== 0');
      $finder->sortByName();

      foreach ($finder as $file) {

        $this_file_path = str_replace($project_dir, '', $file->getPathname());
        $this_file_path = $this->browser_path . $this_file_path;
        $this_file_path_array = explode(DIRECTORY_SEPARATOR, $this_file_path);
        $this_file_name = array_pop($this_file_path_array);
        $this_file_id = str_replace($project_dir . $job_id . DIRECTORY_SEPARATOR, '', $file->getPathname());

        // $this->u->dumper($project_dir,0);
        // $this->u->dumper($this_file_id,0);
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
            'a_attr' => array('href' => $this_file_path, 'download' => $this_file_name)
          );
        }
      }
    }

    return $this->json($data);
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