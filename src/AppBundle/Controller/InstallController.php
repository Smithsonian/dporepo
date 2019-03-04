<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Controller\RepoStorageHybridController;
use PDO;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

// Subject methods
use AppBundle\Controller\SubjectController;
// Item methods
use AppBundle\Controller\ItemController;

class InstallController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    //private $repo_storage_controller;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
    }

    /**
     * @Route("/install/", name="install")
     */
    public function install(Connection $conn, Request $request,$flag = null)
    {

      $ret = $this->repo_storage_controller->build('installDatabase', null);

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


}
