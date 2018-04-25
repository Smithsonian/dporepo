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
    public function importValidation($id, Connection $conn, Request $request, ValidateMetadataController $validate, ItemsController $items)
    {
        $project = array();

        if(!empty($id)) {
          // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
          $this->repo_storage_controller->setContainer($this->container);
          $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $id));
          if(!$project) throw $this->createNotFoundException('The Project record does not exist');
        }

        $validation_results = $validate->validate_metadata($id, $this->container, $items);

        return $this->render('import/import_validation.html.twig', array(
            'page_title' => !empty($project) ? 'Import Validation: ' . $project['project_name'] : 'Import Validation',
            'project_data' => $project,
            'validation_results' => !empty($validation_results) ? $validation_results : '',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));
    }

    /**
     * @Route("/admin/import_metadata/{id}", name="import_metadata", defaults={"id" = null})
     */
    public function importMetadata($id, Connection $conn, Request $request, ValidateMetadataController $validate, ItemsController $items)
    {
      $project = false;
      $csv_data = array();

      if(!empty($id)) {
        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $this->repo_storage_controller->setContainer($this->container);
        $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $id));
        if(!$project) throw $this->createNotFoundException('The Project record does not exist');
      }

      // Insert a record into the job table.
      // TODO: Feed the 'job_label' and 'job_type' to the log leveraging fields from a form submission in the UI.
      $job_id = $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => 'job',
        'user_id' => $this->getUser()->getId(),
        'values' => array(
          'project_id' => (int)$project['project_repository_id'],
          'job_label' => 'VZ Metadata Import',
          'job_type' => 'metadata import',
          'job_status' => 'in progress',
          'date_completed' => null,
          'qa_required' => 0,
          'qa_approved_time' => null,
        )
      ));

      $uploads_directory = __DIR__ . '/../../../web/uploads/';
      $csv_data = $validate->construct_import_data($uploads_directory, $this->container, $items);

      if(!empty($csv_data)) {

        foreach ($csv_data as $csv_key => $csv_value) {
          // Projects
          if($csv_key === 0) {
            // Placeholder
          }
          // Insert Subjects
          if($csv_key === 1) {

            // Insert into the job_log table
            // TODO: Feed the 'job_log_label' to the log leveraging fields from a form submission in the UI.
            $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'job_log',
              'user_id' => $this->getUser()->getId(),
              'values' => array(
                'job_id' => $job_id,
                'job_log_status' => 'start',
                'job_log_label' => 'Import VZ subjects',
                'job_log_description' => 'Import started',
              )
            ));

            $subject_repository_ids = array();

            foreach ($csv_value as $subject_key => $subject) {
              // Set the project_repository_id
              $subject->project_repository_id = $project['project_repository_id'];
              // Insert into the subject table
              $subject_repository_id = $this->repo_storage_controller->execute('saveRecord', array(
                'base_table' => 'subject',
                'user_id' => $this->getUser()->getId(),
                'values' => (array)$subject
              ));
              $subject_repository_ids[$subject->subject_repository_id] = $subject_repository_id;

              // Insert into the job_import_record table
              $job_import_record_id = $this->repo_storage_controller->execute('saveRecord', array(
                'base_table' => 'job_import_record',
                'user_id' => $this->getUser()->getId(),
                'values' => array(
                  'job_id' => $job_id,
                  'record_id' => $subject_repository_id,
                  'project_id' => $project['project_repository_id'],
                  'record_table' => 'subject',
                  'description' => $subject->local_subject_id . ' - ' . $subject->subject_display_name,
                )
              ));

            }

            // Insert into the job_log table
            // TODO: Feed the 'job_log_label' to the log leveraging fields from a form submission in the UI.
            $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'job_log',
              'user_id' => $this->getUser()->getId(),
              'values' => array(
                'job_id' => $job_id,
                'job_log_status' => 'finish',
                'job_log_label' => 'Import VZ subjects',
                'job_log_description' => 'Import finished',
              )
            ));

          }
          // Insert Items
          if($csv_key === 2) {

            // Insert into the job_log table
            // TODO: Feed the 'job_label' and 'job_type' to the log leveraging fields from a form submission in the UI.
            $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'job_log',
              'user_id' => $this->getUser()->getId(),
              'values' => array(
                'job_id' => $job_id,
                'job_log_status' => 'start',
                'job_log_label' => 'Import VZ items',
                'job_log_description' => 'Import started',
              )
            ));

            // $this->u->dumper($subject_repository_ids);

            foreach ($csv_value as $item_key => $item) {
              // Set the subject_repository_id
              $item->subject_repository_id = $subject_repository_ids[$item->subject_repository_id];
              // Insert into the item table
              $item_repository_id = $this->repo_storage_controller->execute('saveRecord', array(
                'base_table' => 'item',
                'user_id' => $this->getUser()->getId(),
                'values' => (array)$item
              ));
              
              // Insert into the job_import_record table
              $job_import_record_id = $this->repo_storage_controller->execute('saveRecord', array(
                'base_table' => 'job_import_record',
                'user_id' => $this->getUser()->getId(),
                'values' => array(
                  'job_id' => $job_id,
                  'record_id' => $item_repository_id,
                  'project_id' => $project['project_repository_id'],
                  'record_table' => 'item',
                  'description' => $item->item_display_name,
                )
              ));
            }

            // Insert into the job_log table
            $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'job_log',
              'user_id' => $this->getUser()->getId(),
              'values' => array(
                'job_id' => $job_id,
                'job_log_status' => 'finish',
                'job_log_label' => 'Import VZ items',
                'job_log_description' => 'Import finished',
              )
            ));

          }
        }

      }

      // Insert a record into the job table.
      $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => 'job',
        'record_id' => $job_id,
        'user_id' => $this->getUser()->getId(),
        'values' => array(
          'project_id' => (int)$project['project_repository_id'],
          'job_label' => 'VZ Metadata Import',
          'job_type' => 'metadata import',
          'job_status' => 'complete',
          'date_completed' => date('Y-m-d h:i:s'),
          'qa_required' => 0,
          'qa_approved_time' => null,
        )
      ));

      $this->addFlash('message', 'Metadata successfully imported for Project "' . $project['project_name'] . '"');
      return $this->redirect('/admin/projects/subjects/' . $project['project_repository_id']);
    }

    /**
     * @Route("/admin/import", name="import_summary_dashboard")
     */
    public function importSummaryDashboard(Connection $conn, Request $request)
    {
        return $this->render('import/import_summary_dashboard.html.twig', array(
            'page_title' => 'Uploads',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));
    }

    /**
     * @Route("/admin/import/datatables_browse_imports", name="imports_browse_datatables", methods="POST")
     *
     * Browse Imports
     *
     * Run a query to retrieve all imports in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_imports(Request $request, SubjectsController $subject, ItemsController $items)
    {
      $req = $request->request->all();
      $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
      $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
      $sort_order = $req['order'][0]['dir'];
      $start_record = !empty($req['start']) ? $req['start'] : 0;
      $stop_record = !empty($req['length']) ? $req['length'] : 20;

      $query_params = array(
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'start_record' => $start_record,
        'stop_record' => $stop_record,
      );
      if ($search) {
        $query_params['search_value'] = $search;
      }

      $this->repo_storage_controller->setContainer($this->container);
      $data = $this->repo_storage_controller->execute('getDatatableImports', $query_params);

      return $this->json($data);
    }
    
    /**
     * @Route("/admin/import/{id}", name="import_summary_details")
     */
    public function importSummaryDetails($id, Connection $conn, ProjectsController $project, Request $request)
    {

      if(!empty($id)) {
        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $this->repo_storage_controller->setContainer($this->container);
        $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $id));
        if(!$project) throw $this->createNotFoundException('The Project record does not exist');
      }

      // Get the total number of Item records for the import.
      $items_total = $this->repo_storage_controller->execute('getImportedItems', array('job_id' => (int)$id));
      $project = array_merge($project, $items_total);

      return $this->render('import/import_summary_item.html.twig', array(
        'page_title' => 'Uploads: ' . $project['project_name'],
        'project' => $project,
        'id' => $id,
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
      ));
    }

    /**
     * @Route("/admin/import/{id}/datatables_browse_import_details", name="import_details_browse_datatables", methods="POST")
     *
     * Browse Import Details
     *
     * Run a query to retrieve the details of an import.
     *
     * @param   int $id  The job ID
     * @param   object $request  Request object
     * @return  array|bool  The query result
     */
    public function datatables_browse_import_details($id, Request $request)
    {
      $req = $request->request->all();
      $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
      $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
      $sort_order = $req['order'][0]['dir'];
      $start_record = !empty($req['start']) ? $req['start'] : 0;
      $stop_record = !empty($req['length']) ? $req['length'] : 20;

      $query_params = array(
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'start_record' => $start_record,
        'stop_record' => $stop_record,
        'id' => $id,
      );

      if ($search) {
        $query_params['search_value'] = $search;
      }

      $this->repo_storage_controller->setContainer($this->container);
      $data = $this->repo_storage_controller->execute('getDatatableImportDetails', $query_params);

      return $this->json($data);
    }
}
