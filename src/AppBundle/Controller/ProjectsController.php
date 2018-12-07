<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use PDO;

use AppBundle\Form\Project;
use AppBundle\Entity\Projects;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

use AppBundle\Service\RepoUserAccess;

class ProjectsController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;
    private $repo_user_access;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->repo_user_access = new RepoUserAccess($conn);
    }

    /**
     * @Route("/admin/workspace/", name="projects_browse", methods="GET")
     */
    public function browse_projects(Connection $conn, Request $request, IsniController $isni)
    {

      $username = $this->getUser()->getUsernameCanonical();
      $access = $this->repo_user_access->get_user_access_any($username, 'view_projects');

      if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
      }

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
        $username = $this->getUser()->getUsernameCanonical();
        $access = $this->repo_user_access->get_user_access_any($username, 'view_projects');

        $project_ids = array(0);
        if(array_key_exists('project_ids', $access) && isset($access['project_ids'])) {
          $project_ids = $access['project_ids'];
        }

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
        $query_params['project_ids'] = $project_ids;

        $data = $this->repo_storage_controller->execute('getDatatableProject', $query_params);

        // Get the subjects count
        if(!empty($data['aaData'])) {
            foreach ($data['aaData'] as $key => $value) {
                $project_subjects = $subjects->get_subjects($value['project_repository_id']);
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

        $username = $this->getUser()->getUsernameCanonical();
        $user_can_edit = false;
        if(false !== $id) {
          $access = $this->repo_user_access->get_user_access($username, 'view_projects', $id);
          if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
            //$response = new Response();
            //$response->setStatusCode(403);
            //return $response;
          }

          $access = $this->repo_user_access->get_user_access($username, 'edit_projects', $id);
          if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
            $user_can_edit = true;
          }
        }

        // Retrieve data from the database.
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
        $project->stakeholder_guid_options = $this->get_units_stakeholders();

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
            'project_data' => (array)$project,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'user_can_edit' => $user_can_edit,
            'form' => $form->createView(),
        ));

    }

    /**
     * Get Projects
     *
     * Run a query to retrieve all projects from the database.
     *
     * @param array $params Parameters sent by the typeahead-bundle
     * The $params array contains the following keys: query, limit, render, property
     *
     * @return array|bool The query result
     */
    public function get_projects($params = array())
    {

      $username = $this->getUser()->getUsernameCanonical();
      $access = $this->repo_user_access->get_user_access_any($username, 'view_projects');
      $project_ids = array(0);
      if(array_key_exists('project_ids', $access) && isset($access['project_ids'])) {
        $project_ids = $access['project_ids'];
      }
      $query_params['project_ids'] = $project_ids;

        // Query the database.
        $data = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'project',
          'fields' => array(),
          'sort_fields' => array(
            0 => array('field_name' => 'stakeholder_guid')
          ),
          'limit' => (int)$params['limit'],
          'search_params' => array(
            0 => array('field_names' => array('project.active'), 'search_values' => array(1), 'comparison' => '='),
            1 => array('field_names' => array('project.project_name'), 'search_values' => $params['query'], 'comparison' => 'LIKE'),
            2 => array('field_names' => array('project.project_repository_id'), 'search_values' => $project_ids, 'comparison' => 'IN')
          ),
          'search_type' => 'AND',
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

        $username = $this->getUser()->getUsernameCanonical();
        $access = $this->repo_user_access->get_user_access_any($username, 'view_projects');
        $project_ids = array(0);
        if(array_key_exists('project_ids', $access) && isset($access['project_ids'])) {
          $project_ids = $access['project_ids'];
        }

        $projects = $this->repo_storage_controller->execute('getRecords', array(
            'base_table' => 'project',
            'fields' => array(),
            'sort_fields' => array(
              0 => array('field_name' => 'project_name')
            ),
            'search_params' => array(
              0 => array('field_names' => array('stakeholder_guid'), 'search_values' => array($stakeholder_guid), 'comparison' => '='),
              1 => array('field_names' => array('project.project_repository_id'), 'search_values' => $project_ids, 'comparison' => 'IN')
            ),
            'search_type' => 'AND'
          )
        );

        foreach ($projects as $key => $value) {

            // Check for child dataset records so the 'children' key can be set accordingly.
            $subject_data = $subjects->get_subjects((int)$value['project_repository_id']);

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
    public function insert_update_project($data, $project_repository_id = FALSE)
    {

        $username = $this->getUser()->getUsernameCanonical();

        if(FALSE !== $project_repository_id) {
          $access = $this->repo_user_access->get_user_access($username, 'edit_projects', $project_repository_id);
          if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
            $response = new Response();
            $response->setStatusCode(403);
            //return $response;
          }
        }

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
    public function get_units_stakeholders()
    {
      $data = array();

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

          $username = $this->getUser()->getUsernameCanonical();
          $any_skipped = $any_deleted = false;
          foreach ($ids_array as $key => $id) {

            $access = $this->repo_user_access->get_user_access($username, 'edit_projects', $id);
            if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
              $any_skipped = true;
              continue;
            }

            $any_deleted = true;
            $ret = $this->repo_storage_controller->execute('markProjectInactive', array(
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
            ));
          }

          if($any_skipped && $any_deleted) {
            $this->addFlash('message', 'Some records were successfully removed but some were not because this user does not have access.');
          }
          elseif($any_skipped) {
            $this->addFlash('message', 'Records were not removed because this user does not have access.');
          }
          else {
            $this->addFlash('message', 'Records successfully removed.');
          }

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('projects_browse');
    }

}
