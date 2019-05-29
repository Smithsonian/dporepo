<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Controller\RepoStorageHybridController;
use PDO;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoStorageStructureHybrid;

class InstallController extends Controller
{
  public $fs;
  public $project_directory;
  public $remote_uploads_directory;
  public $uploads_directory;
  private $kernel;

  private $repo_storage_controller;
  private $connection;

  /**
  * Constructor
  * @param object  $u  Utility functions object
  */
  public function __construct($conn, FilesystemHelperController $fs, string $remote_uploads_directory, KernelInterface $kernel, string $uploads_directory)
  {

    $this->fs = $fs;
    $this->project_directory = $kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->remote_uploads_directory = $remote_uploads_directory;
    $this->uploads_directory = $uploads_directory;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->connection = $conn;
    $this->kernel = $kernel;
  }

  /**
   * @Route("/install/", name="install")
   */
  public function install(Connection $conn, Request $request, $flag = null)
  {

    $ret = $this->build('installDatabase', null);

    $database_installed = false;
    $database_error = '';

    if(is_array($ret)) {
      if(isset($ret['installed'])) {
        $database_installed = $ret['installed'];
      }
      if(isset($ret['error'])) {
        $database_error = $ret['error'];
      }
    }

    return $this->render('install/install.html.twig', array(
    'page_title' => 'Install Database',
    'database_installed' => $database_installed,
    'database_error' => $database_error
    ));
  }

  public function build($function, $parameters) {

    $this->repo_storage_structure = new RepoStorageStructureHybrid(
      $this->connection,
      $this->uploads_directory,
      $this->project_directory,
      $this->fs,
      $this->uploads_directory //container->getParameter('uploads_directory')
  );

    if(!method_exists($this->repo_storage_structure, $function)) {
      //@todo
      return NULL;
    }
    else {
      if ($parameters) {
        return $this->repo_storage_structure->$function($parameters);
      }else{
        return $this->repo_storage_structure->$function();
      }

    }
    return array('installed' => true);

  }

}
