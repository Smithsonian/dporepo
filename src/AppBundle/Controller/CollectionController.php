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

use AppBundle\Form\CollectionForm;
use AppBundle\Entity\Collection;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

use AppBundle\Service\RepoUserAccess;

class CollectionController extends Controller
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
     * @Route("/admin/collection", name="collection_view", methods="GET")
     */
    public function viewCollection(Connection $conn, Request $request, ProjectController $projects)
    {
      //$project_id = !empty($request->attributes->get('project_id')) ? $request->attributes->get('project_id') : false;

      $username = $this->getUser()->getUsernameCanonical();
      $user_can_edit = $user_can_edit_project = false;
      //if(false !== $project_id) {
       // $access = $this->repo_user_access->get_user_access($username, 'view_project_details', $project_id);
       // if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
         // $response = new Response();
          //$response->setStatusCode(403);
         // return $response;
       // }

       // $access = $this->repo_user_access->get_user_access($username, 'edit_projects', $project_id);
       // if(array_key_exists('project_ids', $access) && in_array($project_id, $access['project_ids'])) {
        //  $user_can_edit_project = true;
       // }

       // $access = $this->repo_user_access->get_user_access($username, 'edit_project_details', $project_id);
        //if(array_key_exists('project_ids', $access) && in_array($project_id, $access['project_ids'])) {
        //  $user_can_edit = true;
       // }
      //}

      // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
      //$project_data = $this->repo_storage_controller->execute('getProject', array('project_id' => $project_id));

      //if(!$project_data) throw $this->createNotFoundException('The record does not exist');

      return $this->render('collections/collection.html.twig', array(
        'page_title' => 'Collections',
        //'project_id' => $project_id,
        //'project_data' => $project_data,
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        //'user_can_edit_project' => $user_can_edit_project,
        //'user_can_edit' => $user_can_edit,
        'current_tab' => 'collections',
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
    public function getProjects($params = array())
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
            2 => array('field_names' => array('project.project_id'), 'search_values' => $project_ids, 'comparison' => 'IN')
          ),
          'search_type' => 'AND',
          )
        );

        return $data;
    }

    /**
     * Matches /admin/collection/manage/*
     *
     * @Route("/admin/collection/add/", name="collection_add", methods={"GET","POST"}, defaults={"id" = null})
     * @Route("/admin/collection/manage/{id}", name="collection_manage", methods={"GET","POST"})
     *
     * @param   int     $project_id  The project ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array                 Redirect or render
     */
    function showCollectionForm( $id, Connection $conn, Request $request)
    {

        //$project = new Collection();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        $username = $this->getUser()->getUsernameCanonical();
        $user_can_edit = false;
        if(false == $id) {
          $access = $this->repo_user_access->get_user_access_any($username, 'edit_projects');
          if(array_key_exists('project_ids', $access) && isset($access['project_ids'])) {
            $user_can_edit = true;
          }
        }
        else {
          $access = $this->repo_user_access->get_user_access($username, 'view_projects', $id);
          if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
            $response = new Response();
            $response->setStatusCode(403);
            return $response;
          }

          $access = $this->repo_user_access->get_user_access($username, 'edit_projects', $id);
          if(array_key_exists('project_ids', $access) && isset($access['project_ids'])) {
            $user_can_edit = true;
          }
        }

        // Retrieve data from the database.
        //if (!empty($id) && empty($post)) {
          //$project = (object)$this->repo_storage_controller->execute('getProject', array('project_id' => $id));
          //$project->stakeholder_guid_picker = NULL;

         // if ($project->stakeholder_guid) {
          //    $stakeholder = $this->repo_storage_controller->execute('getStakeholderByIsniId', array(
           //     'record_id' => $project->stakeholder_guid));

            //  if(!empty($stakeholder)) {
           //       $project->stakeholder_guid_picker = $stakeholder['unit_stakeholder_id'];
           //   }
        //  }

        //  $project->api_publication_picker = NULL;
        //  $picker_val = (string)$project->api_published;
        //  $picker_val .= (string)$project->api_discoverable;
        //  $project->api_publication_picker = $picker_val;
        //}

        // Get data from lookup tables.
        //$project->stakeholder_guid_options = $this->getUnitsStakeholders();
        //$project->api_publication_options = array(
        //  'Published, Discoverable' => '11',
        //  'Published, Not Discoverable' => '10',
        //  'Not Published' => '00',
        //);

        // Create the form
        $form = $this->createForm(CollectionForm::class, $project);

        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $project = $form->getData();
            $project_id = $this->insertUpdateProject($project, $id);

            $this->addFlash('message', 'Project successfully updated.');
            return $this->redirect('/admin/collection/view/' . $project_id);
        }

        return $this->render('collections/collection_form.html.twig', array(
            'page_title' => !empty($id) ? 'Collection: ' . $project->project_name : 'Create Collection',
            'project_data' => (array)$project,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'user_can_edit' => $user_can_edit,
            'form' => $form->createView(),
            'current_tab' => 'collections',
        ));

    }

}
