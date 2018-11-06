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

// Subjects methods
use AppBundle\Controller\SubjectsController;
// Items methods
use AppBundle\Controller\ItemsController;

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
     * @Route("/install/", name="install", methods="GET")
     * @Route("/install/{flag}", name="install-flag")
     */
    public function install(Connection $conn, Request $request,$flag = null)
    {
    	$dbexist = $this->repo_storage_controller->build('databaseExist', null);
    	if ($flag == 'cdb' && !$dbexist) {
    		$this->repo_storage_controller->build('installDatabase', null);
    	}
        
        if (!$dbexist) {
        	$this->repo_storage_controller->build('installDatabase', null);
        	$flag = 'cdb';
        	$dbexist= true;
        }
        return $this->render('install/install.html.twig', array(
            'page_title' => 'Install Database',"dbexist"=>$dbexist,"flag"=>$flag
        ));
    }


}
