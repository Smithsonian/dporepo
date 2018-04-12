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
     * Run a query to retrieve all projects in the database.
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
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $this->repo_storage_controller->setContainer($this->container);
        $data = $this->repo_storage_controller->execute('getDatatableProject', $query_params);

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
          $project = (object)$this->repo_storage_controller->execute('getProject', array('project_repository_id' => $id));
          $project->stakeholder_guid_picker = NULL;

          if ($project->stakeholder_guid) {
              $stakeholder = $this->repo_storage_controller->execute('getStakeholderByIsniId', array(
                'record_id' => $project->stakeholder_guid));

              if(!empty($stakeholder)) {
                  $project->stakeholder_guid_picker = $stakeholder['unit_stakeholder_repository_id'];
              }
          }
        }

        // Get data from lookup tables.
        $project->stakeholder_guid_options = $this->get_units_stakeholders($this->container);

        // Create the form
        $form = $this->createForm(Project::class, $project);

        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $project = $form->getData();
            $project_repository_id = $this->insert_update_project($this->container, $project, $id);

            $this->addFlash('message', 'Project successfully updated.');
            return $this->redirect('/admin/projects/subjects/' . $project_repository_id);
        }

        return $this->render('projects/project_form.html.twig', array(
            'page_title' => !empty($id) ? 'Project: ' . $project->project_name : 'Create Project',
            'project_data' => (array)$project,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));

    }

    /**
     * Get Projects
     *
     * Run a query to retrieve all projects from the database.
     *
     * @return  array|bool     The query result
     */
    public function get_projects($container)
    {
        $this->repo_storage_controller->setContainer($container);
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
     * Get Stakeholder GUIDs (route for the tree browser)
     *
     * @Route("/admin/projects/get_stakeholder_guids", name="get_stakeholder_guids_tree_browser", methods="GET")
     */
    public function get_stakeholder_guids_tree_browser()
    {
      $data = array();
      $this->repo_storage_controller->setContainer($this->container);
      $projects = $this->repo_storage_controller->execute('getStakeholderGuids');
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
              0 => array('field_names' => array('stakeholder_guid'), 'search_values' => array($stakeholder_guid), 'comparison' => '=')
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
     * @return  int     The project ID
     */
    public function insert_update_project($container, $data, $project_repository_id = FALSE)
    {
        $this->repo_storage_controller->setContainer($container);

        // If there is no entry, then perform an insert.
        if(isset($data->stakeholder_guid)) {
          $this->repo_storage_controller->execute('saveIsniRecord', array(
              'user_id' => $this->getUser()->getId(),
              'record_id' => $data->stakeholder_guid,
              'record_label' => $data->stakeholder_label,
            )
          );
        }
        elseif(isset($data->stakeholder_guid_picker)) {
          // get the isni id from unit_stakeholder
          $stakeholder = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'unit_stakeholder',
            'record_id' => (int)$data->stakeholder_guid_picker));
          if(isset($stakeholder) && isset($stakeholder['isni_id'])) {
            $data->stakeholder_guid = $stakeholder['isni_id'];
          }
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
    public function get_units_stakeholders($container)
    {
      $data = array();

      $this->repo_storage_controller->setContainer($container);
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'unit_stakeholder',
          'sort_fields' => array(
            0 => array('field_name' => 'unit_stakeholder_label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        $akey = $value['unit_stakeholder_label'] . ' - ' . $value['unit_stakeholder_full_name'];
        $data[$akey] = $value['unit_stakeholder_repository_id'];
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
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_projects(Request $request)
    {
        $ids = $request->query->get('ids');

        if(!empty($ids)) {

          $ids_array = explode(',', $ids);

          $this->repo_storage_controller->setContainer($this->container);

          foreach ($ids_array as $key => $id) {
            $ret = $this->repo_storage_controller->execute('markProjectInactive', array(
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
            ));
          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('projects_browse');
    }

}
