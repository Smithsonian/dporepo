<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class ImportController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;

    /**
     * Constructor
     * @param object  $u  Utility functions object
     */
    public function __construct(AppUtilities $u)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController();
    }

    /**
     * @Route("/admin/import_validation/{id}", name="import_validation", defaults={"id" = null})
     */
    public function importValidation($id, Connection $conn, Request $request, ValidateMetadataController $validate)
    {
        $project = array();

        if(!empty($id)) {
          // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
          $this->repo_storage_controller->setContainer($this->container);
          $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $id));
          if(!$project) throw $this->createNotFoundException('The Project record does not exist');
        }

        $validation_results = $validate->validate_metadata($id, $request);

        return $this->render('import/import_validation.html.twig', array(
            'page_title' => !empty($project) ? 'Import Validation: ' . $project['project_name'] : 'Import Validation',
            'validation_results' => !empty($validation_results) ? $validation_results : '',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));
    }

    /**
     * @Route("/admin/import", name="import_summary_dashboard")
     */
    public function importSummaryAction(Connection $conn, Request $request)
    {
        return $this->render('import/import_summary_dashboard.html.twig', array(
            'page_title' => 'Uploads',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));
    }

    /**
     * @Route("/admin/import/datatables_import_projects", name="projects_import_datatables", methods="POST")
     *
     * Browse Projects
     *
     * Run a query to retrieve all projects in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_import_projects(Request $request)
    {
        $data = array("aaData"=>array(
                        array("project_repository_id"=>1,
                              "project_name"=>ucwords("nmnh human origins vertebrate zoology models"),
                              "date_uploaded"=>"Apr 17, 2018",
                              "items_total"=>2000,
                              "uploadedby"=>"blundellj",
                              "status"=>ucwords("processing")
                        ),
                        array("project_repository_id"=>3,
                              "project_name"=>ucwords("project abc"),
                              "date_uploaded"=>"Apr 10, 2018",
                              "items_total"=>2000,
                              "uploadedby"=>"andersonm",
                              "status"=>ucwords("aborted")
                        ),
                        array("project_repository_id"=>4,
                              "project_name"=>ucwords("project def"),
                              "date_uploaded"=>"Mar 22, 2018",
                              "items_total"=>10000,
                              "uploadedby"=>"andersonm",
                              "status"=>ucwords("complete")
                        ),
                        array("project_repository_id"=>2,
                              "project_name"=>ucwords("project deff"),
                              "date_uploaded"=>"Mar 20, 2018",
                              "items_total"=>1500,
                              "uploadedby"=>"conradj",
                              "status"=>ucwords("failed")
                        ),
                        array("project_repository_id"=>5,
                              "project_name"=>ucwords("project ghi"),
                              "date_uploaded"=>"Mar 20, 2018",
                              "items_total"=>1500,
                              "uploadedby"=>"andersonm",
                              "status"=>ucwords("complete")
                        )
                ));
        $data["iTotalRecords"] = count($data['aaData']);
        $data["iTotalDisplayRecords"] = count($data['aaData']);
        return $this->json($data);
    }
    
    /**
     * @Route("/admin/import/{project_id}", name="import_summary_item")
     */
    public function importSummaryItemAction(Connection $conn, Request $request, $project_id)
    {
        $data = array(
                    array("project_repository_id"=>1,
                          "project_name"=>ucwords("NMNH human origins vertebrate zoology models"),
                          "date_uploaded"=>"Apr 17, 2018",
                          "item_success"=>25,
                          "item_failed"=>2,
                          "item_in_progress"=>12,
                          "item_pending_processing"=>2889,
                          "items_total"=>2928
                    ),
                    array("project_repository_id"=>3,
                          "project_name"=>ucwords("project abc"),
                          "date_uploaded"=>"Apr 10, 2018",
                          "item_success"=>1000,
                          "item_failed"=>50,
                          "item_in_progress"=>20,
                          "item_pending_processing"=>930,
                          "items_total"=>2000
                    ),
                    array("project_repository_id"=>4,
                          "project_name"=>ucwords("project def"),
                          "date_uploaded"=>"Mar 22, 2018",
                          "item_success"=>6090,
                          "item_failed"=>450,
                          "item_in_progress"=>550,
                          "item_pending_processing"=>2910,
                          "items_total"=>10000
                    ),
                    array("project_repository_id"=>2,
                          "project_name"=>ucwords("project deff"),
                          "date_uploaded"=>"Mar 20, 2018",
                          "item_success"=>450,
                          "item_failed"=>310,
                          "item_in_progress"=>40,
                          "item_pending_processing"=>700,
                          "items_total"=>1500
                    ),
                    array("project_repository_id"=>5,
                          "project_name"=>ucwords("project ghi"),
                          "date_uploaded"=>"Mar 20, 2018",
                          "item_success"=>900,
                          "item_failed"=>50,
                          "item_in_progress"=>60,
                          "item_pending_processing"=>490,
                          "items_total"=>1500
                    )
            );
        $key = array_search($project_id, array_column($data, 'project_repository_id'));

        return $this->render('import/import_summary_item.html.twig', array(
            'page_title' => 'Uploads: ' . $data[$key]['project_name'],
            'project' => $data[$key],
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));

        return $this->render('import/import_summary_item.html.twig',array("project"=>$data[$key]));
    }

    /**
     * @Route("/admin/import/{project_id}/datatables_import_project_item", name="projects_import_datatables_item")
     *
     * Browse Projects
     *
     * Run a query to retrieve all projects in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_import_projects_item(Request $request,$project_id)
    {
        $data = array("aaData"=>array(
                        array("project_repository_id"=>1,
                              "item"=>"USNM 281591 Cranium",
                              "subject"=>ucwords("white-Fronted Capuchin, colombia (USNM 281591)"),
                              "holding_entity"=>"smithsonian natural museum of natural history",
                              "status"=>ucwords("success")
                        ),
                        array("project_repository_id"=>1,
                              "item"=>"USNM 281591 Mandible",
                              "subject"=>ucwords("white-Fronted Capuchin, colombia (USNM 281591)"),
                              "holding_entity"=>"smithsonian natural museum of natural history",
                              "status"=>ucwords("processing")
                        ),
                        array("project_repository_id"=>1,
                              "item"=>"USNM A21171 Cranium",
                              "subject"=>ucwords("sifaka, madagascar (USNM A21171)"),
                              "holding_entity"=>"smithsonian natural museum of natural history",
                              "status"=>ucwords("success")
                        ),
                        array("project_repository_id"=>1,
                              "item"=>"USNM A21171 Mandible",
                              "subject"=>ucwords("sifaka, madagascar (USNM A21171)"),
                              "holding_entity"=>"smithsonian natural museum of natural history",
                              "status"=>ucwords("failed")
                        ),
                        array("project_repository_id"=>2,
                              "item"=>"USNM A22462 Mandible",
                              "subject"=>ucwords("Ring-tailed lemur, madagascar (USNM  A22462)"),
                              "holding_entity"=>"smithsonian natural museum of natural history",
                              "status"=>ucwords("failed")
                        ),
                        array("project_repository_id"=>2,
                              "item"=>"USNM 281591 Cranium",
                              "subject"=>ucwords("white-Fronted Capuchin, colombia (USNM 281591)"),
                              "holding_entity"=>"smithsonian natural museum of natural history",
                              "status"=>ucwords("processing")
                        )
                ));
        $newdata = [];
        foreach ($data['aaData'] as $d) {
            if((int)$d['project_repository_id'] == (int)$project_id){
                $newdata[] = $d;
            }
        }
        $newdata = array("aaData"=>$newdata);
        $newdata["iTotalRecords"] = count($newdata['aaData']);
        $newdata["iTotalDisplayRecords"] = count($newdata['aaData']);
        return $this->json($newdata);
    }
}
