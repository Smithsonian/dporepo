<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Finder\Finder;

class FilesystemHelperController extends Controller
{
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

        // var_dump($project_dir); echo '<br>';
        // var_dump($this_file_id); echo '<br>';
        // var_dump($this_file_name); die();

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

}