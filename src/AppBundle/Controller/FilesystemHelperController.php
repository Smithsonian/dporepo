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
   * @param object  $request  Request object
   * @return string
   */
  public function get_directory_contents(Request $request) {

    $data = [];
    $job_id = !empty($request->attributes->get('job_id')) ? $request->attributes->get('job_id') : false;
    $id = (!empty($request->get('id')) && ($request->get('id') !== '#')) ? DIRECTORY_SEPARATOR . $request->get('id') : '';
    $directory_opened_state = (!empty($request->get('id')) && ($request->get('id') === '#')) ? true : false;
    $project_dir = $this->get('kernel')->getProjectDir() . $this->uploads_directory;

    if (!empty($job_id) && is_dir($project_dir . $job_id . $id . DIRECTORY_SEPARATOR)) {

      $finder = new Finder();
      $finder->in($project_dir . $job_id . $id . DIRECTORY_SEPARATOR);
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
        // $this->u->dumper($this_file_name);

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
            'text' => $this_file_name,
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
   * @Route("/admin/get_uploaded_files", name="get_uploaded_files", methods="GET")
   *
   * Get Uploaded Files
   * Construct a directory listing of all uploaded files.
   * Example: /admin/get_uploaded_files?path=/testupload05/data/&id=2
   *
   * @param object  $request  Request object
   * @return string
   */
  public function get_uploaded_files(Request $request) {

    $items = array();
    $directory_path = !empty($request->get('path')) ? $request->get('path') : false;
    $record_id = !empty($request->get('id')) ? $request->get('id') : false;

    if(!empty($directory_path) && !empty($record_id)) {

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

      // If data is returned, proceed with parsing a list of uploaded files.
      if ($job_data && !empty($job_data)) {
        // The target directory.
        $dir = $this->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'web' . $this->browser_path . DIRECTORY_SEPARATOR . $job_data[0]['job_id'] . str_replace('/', DIRECTORY_SEPARATOR, $directory_path);

        // The target directory.
        // $dir = $this->get('kernel')->getProjectDir() . DIRECTORY_SEPARATOR . 'web' . $this->browser_path . DIRECTORY_SEPARATOR . $job_data[0]['job_id'];

        // Construct the JSON for the tree.
        // $data = $this->u->get_directory_tree($dir);
        $data = $this->get_tree($dir);

        // $this->u->dumper($data);
      }

    }

    return $this->json($data);
  }

  public function get_tree($dir = null, $project_dir = null, $previous_file_paths = array(), $previous_dirs = array()) {

    // $this->u->dumper('previous_file_paths',0);
    // $this->u->dumper($previous_file_paths,0);

    $items = array();

    if (empty($project_dir)) {
      $project_dir = $this->get('kernel')->getProjectDir();
    }

    if(!empty($dir) && is_dir($dir)) {

      $finder = new Finder();
      $finder->in($dir);
      $finder->sortByName();

      foreach ($finder as $file) {

        $item = array();
        $this_file_path = str_replace($project_dir . DIRECTORY_SEPARATOR . 'web', '', $file->getPathname());
        $this_pretty_file_path = str_replace($project_dir . DIRECTORY_SEPARATOR . 'web' . $this->browser_path . DIRECTORY_SEPARATOR, '', $file->getPathname());
        $this_pretty_file_path_array = explode(DIRECTORY_SEPARATOR, $this_pretty_file_path);
        $this_file_name = array_pop($this_pretty_file_path_array);

        // If FILE
        if(!is_dir($file->getPathname()) && !in_array($file->getPathname(), $previous_file_paths)) {
          $file_entry = array('text' => $this_file_name . '&nbsp;&nbsp;&nbsp;(' . $this->byteConvert(filesize($file->getPathname())) . ')', 'icon' => 'glyphicon glyphicon-file', 'a_attr' => array('href' => $this_file_path, 'path' => $this_pretty_file_path, 'size' => filesize($file->getPathname()), 'download' => $this_file_name));
          $item = $file_entry;
          array_push($previous_file_paths, $file->getPathname());
        }

        // If FOLDER, then go inside and repeat the loop
        if(is_dir($file->getPathname()) && !in_array($file->getPathname(), $previous_file_paths)) {
          $folder = array('text' => $this_file_name, 'icon' => 'glyphicon glyphicon-folder-close', 'path' => $this_pretty_file_path, 'size' => filesize($file->getPathname()));
          $children = $this->get_tree($file->getPathname(), $project_dir, $previous_file_paths, $previous_dirs);
          $folder['children'] = $children;
          $item = $folder;
          array_push($previous_file_paths, $file->getPathname());
        }
        
        if (!empty($item)) {
          $items[] = $item;
        }

      }
    }

    return $items;
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

    return round($bytes/pow(1024, $e), 2).$s[$e];
  }

}