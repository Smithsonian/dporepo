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
        //$this->repo_storage_controller = new RepoStorageHybridController($conn);
    }

    /**
     * @Route("/repo/install/", name="install", methods="GET")
     */
    public function install(Connection $conn, Request $request)
    {
 		//$tables = array("background_removal_method","calibration_object_type","camera_cluster_type","capture_data_element","capture_data_file","capture_dataset","capture_dataset_rights","capture_device","capture_device_component","capture_method","data_rights_restriction_type","dataset_type","favorite","file_package","file_upload","focus_type","fos_user","isni_data","item","item_position_type","item_type","job","job_import_record","job_log","light_source_type","model","model_file","photogrammetry_scale_bar","photogrammetry_scale_bar_target_pair","processing_action","project","scale_bar_barcode_type","status_type","subject","subject_type","target_type","unit","unit_stakeholder","uv_map","vz_export","workflow","workflow_status_log");
        

        $tables = array("background_removal_method","calibration_object_type","camera_cluster_type","data_rights_restriction_type","dataset_type","item_position_type","light_source_type","target_type");
        $tables_in_database = [];
        foreach ($tables as $table) {
        	$exist = $conn->fetchAll("show tables like '$table'");
        	$flag = "does not exist";
        	if (count($exist)) {
        		$flag = "exist";
        	}
        	$tables_in_database[] = array("name"=>$table,"exist"=>$flag);
        }
        return $this->render('install/install.html.twig', array(
            'page_title' => 'Install Database',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),"tables"=>$tables_in_database,
        ));
        
    }


}
