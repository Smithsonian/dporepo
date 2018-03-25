<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use Symfony\Component\DependencyInjection\Container;
use PDO;

use AppBundle\Form\Project;
use AppBundle\Entity\Projects;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class ProjectsController extends Controller
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
     * @Route("/admin/workspace/", name="projects_browse", methods="GET")
     */
    public function browse_projects(Connection $conn, Request $request, IsniController $isni)
    {
        // Database tables are only created if not present.
        $this->repo_storage_controller->setContainer($this->container);
        $ret = $this->repo_storage_controller->build('createTable', array('table_name' => 'project'));
        $ret = $this->repo_storage_controller->build('createTable', array('table_name' => 'isni_data'));

        return $this->render('projects/browse_projects.html.twig', array(
            'page_title' => 'Browse Projects',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_projects", name="projects_browse_datatables", methods="POST")
     *
     * Browse Projects
     *
     * Run a query to retreive all projects in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_projects(Request $request, SubjectsController $subjects)
    {
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $query_params = array(
          'record_type' => 'project',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $this->repo_storage_controller->setContainer($this->container);
        $data = $this->repo_storage_controller->execute('getDatatable', $query_params);

        // Get the subjects count
        if(!empty($data['aaData'])) {
            foreach ($data['aaData'] as $key => $value) {
                $project_subjects = $subjects->get_subjects($this->container, $value['project_repository_id']);
                $data['aaData'][$key]['subjects_count'] = count($project_subjects);
            }
        }
        return $this->json($data);
    }

    /**
     * Matches /admin/projects/manage/*
     *
     * @Route("/admin/projects/manage/{id}", name="projects_manage", methods={"GET","POST"}, defaults={"id" = null})
     *
     * @param   int     $project_repository_id  The project ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array                 Redirect or render
     */
    function show_projects_form( $id, Connection $conn, Request $request)
    {

        $project = new Projects();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // Retrieve data from the database.
        $this->repo_storage_controller->setContainer($this->container);
        if (!empty($id) && empty($post)) {
          $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $id));
        }
        
        // Get data from lookup tables.
        $project->stakeholder_guid_options = $this->get_units_stakeholders($conn);

        // Create the form
        $form = $this->createForm(Project::class, $project);
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $project = $form->getData();
            $project_repository_id = $this->insert_update_project($project, $id);

            $this->addFlash('message', 'Project successfully updated.');
            return $this->redirect('/admin/projects/subjects/' . $project_repository_id);

        }

        return $this->render('projects/project_form.html.twig', array(
            'page_title' => !empty($id) ? 'Project: ' . $project->project_name : 'Create Project',
            'project_data' => $project,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));

    }

    /**
     * Get Projects
     *
     * Run a query to retrieve all projects from the database.
     *
     * @param   object  $conn  Database connection object
     * @return  array|bool     The query result
     */
    public function get_projects()
    {
        $this->repo_storage_controller->setContainer($this->container);
        $data = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'project',
          'fields' => array(),
          'sort_fields' => array(
            0 => array('field_name' => 'stakeholder_guid')
          ),
          )
        );

        return $data;
    }

    /**
     * Get Stakeholder GUIDs
     *
     * Run a query to retrieve all Stakeholder GUIDs from the database.
     *
     * @return  array|bool  The query result
     */
    public function get_stakeholder_guids($conn)
    {
        // $statement_fgb = $conn->prepare("
        //     SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
        // ");
        // $statement_fgb->execute();

        $statement = $conn->prepare("
            SELECT project.project_repository_id
                ,project.stakeholder_guid
                ,isni_data.isni_label AS stakeholder_label
            FROM project
            LEFT JOIN isni_data ON isni_data.isni_id = project.stakeholder_guid
            WHERE project.active = 1
            GROUP BY isni_data.isni_label
            ORDER BY isni_data.isni_label ASC
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Stakeholder GUIDs (route for the tree browser)
     *
     * @Route("/admin/projects/get_stakeholder_guids", name="get_stakeholder_guids_tree_browser", methods="GET")
     */
    public function get_stakeholder_guids_tree_browser(Connection $conn)
    {
      $projects = $this->get_stakeholder_guids($conn);
      foreach ($projects as $key => $value) {
          $data[$key]['id'] = 'stakeholderGuid-' . $value['stakeholder_guid'];
          $data[$key]['text'] = $value['stakeholder_label'];
          $data[$key]['children'] = true;
      }

      $response = new JsonResponse($data);
      return $response;
    }

    /**
     * Get a Stakeholder's Projects' (route for the tree browser)
     *
     * @Route("/admin/projects/get_stakeholder_projects/{stakeholder_guid}", name="get_stakeholder_projects_tree_browser", methods="GET")
     */
    public function get_stakeholder_projects_tree_browser(Request $request, SubjectsController $subjects)
    {
        $data = array();
        $stakeholder_guid = !empty($request->attributes->get('stakeholder_guid')) ? $request->attributes->get('stakeholder_guid') : false;

        $this->repo_storage_controller->setContainer($this->container);
        $projects = $this->repo_storage_controller->execute('getRecords', array(
            'base_table' => 'project',
            'fields' => array(),
            'sort_fields' => array(
              0 => array('field_name' => 'project_name')
            ),
            'search_params' => array(
              0 => array('field_names' => array('active'), 'search_values' => array(1), 'comparison' => '='),
              1 => array('field_names' => array('stakeholder_guid'), 'search_values' => array($stakeholder_guid), 'comparison' => '=')
            ),
            'search_type' => 'AND'
          )
        );

        foreach ($projects as $key => $value) {

            // Check for child dataset records so the 'children' key can be set accordingly.
            $subject_data = $subjects->get_subjects($this->container, (int)$value['project_repository_id']);

            $data[$key] = array(
                'id' => 'projectId-' . $value['project_repository_id'],
                'text' => $value['project_name'],
                'children' => count($subject_data) ? true : false,
                'a_attr' => array('href' => '/admin/projects/subjects/' . $value['project_repository_id']),
            );
        }

        $response = new JsonResponse($data);
        return $response;
    }

    /**
     * Insert/Update Project
     *
     * Run queries to insert and update projects in the database.
     *
     * @param   array   $data        The data array
     * @param   int     $project_id  The project ID
     * @param   object  $conn        Database connection object
     * @return  int     The project ID
     */
    public function insert_update_project($data, $project_repository_id = FALSE)
    {
        $this->repo_storage_controller->setContainer($this->container);
        if(empty($post)) {
          $ret = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'unit_stakeholder',
            'record_id' => $data->stakeholder_guid_picker));
          $unit_record = $ret;
        }

        if($unit_record && !empty($unit_record['isni_id'])) {
          $data->stakeholder_guid = $unit_record['isni_id'];
        } else {
          $data->stakeholder_guid = $data->stakeholder_guid_picker;
        }

        // Query the isni_data table to see if there's an entry.
        $isni_data = $this->repo_storage_controller->execute('getRecordById', array(
          'record_type' => 'isni',
          'record_id' => $data->stakeholder_guid));

        // If there is no entry, then perform an insert.
        if(!$isni_data) {
          //$isni_inserted = $isni->insert_isni_data($data->stakeholder_guid, $data->stakeholder_label, $this->getUser()->getId(), $conn);
          $isni_inserted = $this->repo_storage_controller->execute('saveRecord', array(
            'base_table' => 'isni',
            'user_id' => $this->getUser()->getId(),
            'values' => array(
              'isni_id' => $data->stakeholder_guid,
              'isni_label' => $data->stakeholder_label,
            )
          ));
        }

        $id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'project',
          'record_id' => $project_repository_id,
          'user_id' => $this->getUser()->getId(),
          'values' => (array)$data
        ));

        return $id;
    }

    /**
     * Get unit_stakeholder
     * @return  array|bool  The query result
     */
    public function get_units_stakeholders($conn)
    {
      $data = array();

      $statement = $conn->prepare("SELECT * FROM unit_stakeholder
        WHERE unit_stakeholder.active = 1
        ORDER BY unit_stakeholder_label ASC");
      $statement->execute();

      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $data[$value['unit_stakeholder_label'] . ' - ' . $value['unit_stakeholder_full_name']] = $value['unit_stakeholder_repository_id'];
      }

      return $data;
    }

    /**
     * Delete Multiple Projects
     *
     * Matches /admin/workspace/delete
     *
     * @Route("/admin/workspace/delete", name="projects_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $conn     Database connection object
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_projects(Connection $conn, Request $request)
    {
        $ids = $request->query->get('ids');

        if(!empty($ids)) {

          $ids_array = explode(',', $ids);

          foreach ($ids_array as $key => $id) {

            $statement = $conn->prepare("
                UPDATE project
                LEFT JOIN subject ON subject.project_repository_id = project.project_repository_id
                LEFT JOIN item ON item.subject_repository_id = subject.subject_repository_id
                LEFT JOIN capture_dataset ON capture_dataset.parent_item_repository_id = item.item_repository_id
                LEFT JOIN capture_data_element ON capture_data_element.capture_dataset_repository_id = capture_dataset.capture_dataset_repository_id
                SET project.active = 0,
                    project.last_modified_user_account_id = :last_modified_user_account_id,
                    subject.active = 0,
                    subject.last_modified_user_account_id = :last_modified_user_account_id,
                    item.active = 0,
                    item.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE project.project_repository_id = :id
            ");
            $statement->bindValue(":id", $id, PDO::PARAM_INT);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->execute();

          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('projects_browse');
    }

    /**
     * Delete Project
     *
     * Run a query to delete a project from the database.
     *
     * @param   int     $project_id  The project ID
     * @param   object  $conn        Database connection object
     * @return  void
     */
    public function delete_project($project_repository_id, $conn)
    {
        $statement = $conn->prepare("
            DELETE FROM project
            WHERE project_repository_id = :project_repository_id");
        $statement->bindValue(":project_repository_id", $project_repository_id, PDO::PARAM_INT);
        $statement->execute();

        // First, delete all subjects.
        $statement = $conn->prepare("
            DELETE FROM subject
            WHERE project_repository_id = :project_repository_id");
        $statement->bindValue(":project_repository_id", $project_repository_id, PDO::PARAM_INT);
        $statement->execute();
    }

}
