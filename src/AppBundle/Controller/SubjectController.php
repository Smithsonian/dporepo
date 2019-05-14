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

use AppBundle\Form\SubjectForm;
use AppBundle\Entity\Subject;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

use AppBundle\Service\RepoUserAccess;

class SubjectController extends Controller
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

    //@todo this is deprecated, make sure it is un-used and then delete it
    /**
     * @Route("/admin/projects/subjects/{project_id}", name="subjects_browse", methods="GET", requirements={"project_id"="\d+"})
     */
    public function browseSubjects(Connection $conn, Request $request, ProjectController $projects)
    {
        $project_id = !empty($request->attributes->get('project_id')) ? $request->attributes->get('project_id') : false;

        $username = $this->getUser()->getUsernameCanonical();
        $user_can_edit = $user_can_edit_project = false;
        if(false !== $project_id) {
          $access = $this->repo_user_access->get_user_access($username, 'view_project_details', $project_id);
          if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
            $response = new Response();
            $response->setStatusCode(403);
            return $response;
          }

          $access = $this->repo_user_access->get_user_access($username, 'edit_projects', $project_id);
          if(array_key_exists('project_ids', $access) && in_array($project_id, $access['project_ids'])) {
            $user_can_edit_project = true;
          }

          $access = $this->repo_user_access->get_user_access($username, 'edit_project_details', $project_id);
          if(array_key_exists('project_ids', $access) && in_array($project_id, $access['project_ids'])) {
            $user_can_edit = true;
          }
        }

        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $project_data = $this->repo_storage_controller->execute('getProject', array('project_id' => $project_id));

        if(!$project_data) throw $this->createNotFoundException('The record does not exist');

        // Check to see if there are any subjects, to present the Upload Metadata button or not.
        $subjects = $this->getSubjects((int)$project_data['project_id']);

        return $this->render('subjects/browse_subjects.html.twig', array(
            'page_title' => 'Project: ' . $project_data['project_name'],
            'project_id' => $project_id,
            'project_data' => $project_data,
            'upload_metadata_button' => !empty($subjects) ? true : false,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'user_can_edit_project' => $user_can_edit_project,
            'user_can_edit' => $user_can_edit,
        ));
    }

    /**
     * @Route("/admin/datatables_browse_subjects/{project_id}", name="subjects_browse_datatables", methods="POST", defaults={"project_id" = null})
     *
     * Browse subjects
     *
     * Run a query to retrieve all subjects in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatablesBrowseSubjects(Request $request, ItemController $items)
    {
        $req = $request->request->all();
        $project_id = !empty($request->attributes->get('project_id')) ? $request->attributes->get('project_id') : false;

        $username = $this->getUser()->getUsernameCanonical();
        if(false !== $project_id) {
          $access = $this->repo_user_access->get_user_access($username, 'view_project_details', $project_id);
          if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
            return $this->json(array());
          }
        }

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
          'project_id' => $project_id,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $data = $this->repo_storage_controller->execute('getDatatableSubject', $query_params);

        // Convert status to human readable words.
        if(!empty($data['aaData'])) {
            foreach ($data['aaData'] as $key => $value) {
                switch($value['active']) {
                    case '0':
                        $label = 'warning';
                        $text = 'In Queue';
                        break;
                    case '1':
                        $label = 'primary';
                        $text = 'Processing';
                        break;
                    case '2':
                        $label = 'success';
                        $text = 'Processed';
                        break;
                }
                $data['aaData'][$key]['active'] = '<span class="label label-' . $label . '"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' . $text . '</span>';

                // Get the items count
                $subject_items = $items->getItems($value['subject_id']);
                $data['aaData'][$key]['items_count'] = count($subject_items);
            }
        }

        return $this->json($data);
    }

    /**
     * Matches /admin/subject/*
     *
     * @Route("/admin/subject/add/{ajax}", name="subject_add", methods={"GET","POST"}, defaults={"subject_id" = null, "ajax" = null})
     * @Route("/admin/subject/manage/{subject_id}", name="subject_manage", methods={"GET","POST"}, defaults={"subject_id" = null, "ajax" = null})
     *
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array                 Redirect or render
     */
    function showSubjectForm( $subject_id, Connection $conn, Request $request )
    {
        $subject = new Subject();
        $subject->access_model_purpose = NULL;

        $post = $request->request->all();
        $subject->project_id = !empty($request->attributes->get('project_id')) ? $request->attributes->get('project_id') : false;
        $id = false;
        $ajax = false;

        if (!empty($request->attributes->get('subject_id'))) {
          $id = $request->attributes->get('subject_id');
        }

        // Check user's permissions.
        $project_id = (!empty($item_record) && array_key_exists('project_id', $item_record)) ? $item_record['project_id'] : '';
        $username = $this->getUser()->getUsernameCanonical();
        // Check if user has permission to access this page.
        $access = $this->repo_user_access->get_user_access($username, 'edit_project_details', $project_id);
        if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
          $response = new Response();
          $response->setStatusCode(403);
          return $response;
        }

        // If being POSTed via ajax, set the ajax flag to true.
        if (!empty($request->attributes->get('ajax'))) $ajax = true;

        $username = $this->getUser()->getUsernameCanonical();
        $user_can_edit = false;
        if(false !== $subject->project_id && false !== $id) {
          $access = $this->repo_user_access->get_user_access($username, 'view_project_details', $subject->project_id);
          if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
            $response = new Response();
            $response->setStatusCode(403);
            return $response;
          }

          $access = $this->repo_user_access->get_user_access($username, 'edit_project_details', $subject->project_id);
          if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
            $user_can_edit = true;
          }
        }

        // Get values for options.
        $model_purpose_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_purpose',
          'value_field' => 'model_purpose_description',
          'id_field' => 'model_purpose_id',
          ));
        $model_face_count_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_face_count',
          'value_field' => 'model_face_count',
          'id_field' => 'model_face_count_id',
        ));
        $uv_map_size_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'uv_map_size',
          'value_field' => 'uv_map_size',
          'id_field' => 'uv_map_size_id',
        ));

        // Retrieve data from the database.
        if(!empty($id) && empty($post)) {
          $rec = $this->repo_storage_controller->execute('getSubject', array(
            'record_type' => 'subject',
            'record_id' => $id));
          if(isset($rec)) {
            $subject = (object)$rec;
          }

          $subject->model_purpose_picker = $subject->access_model_purpose;
        }

        $subject->model_face_count_options = $model_face_count_options;
        $subject->uv_map_size_options = $uv_map_size_options;
        $subject->model_purpose_options = $model_purpose_options;

        $subject = (array)$subject;

        // Create the form
        $form = $this->createForm(SubjectForm::class, $subject);
        // Handle the request
        $form->handleRequest($request);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $subject = (array)($form->getData());
            $id = $this->repo_storage_controller->execute('saveSubject', array(
              'base_table' => 'subject',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => $subject
            ));

            if ($ajax) {
              // Return the ID of the new record.
              $response = new JsonResponse(array('id' => $id));
              return $response;
            } else {
              $this->addFlash('message', 'Subject successfully updated.');
              return $this->redirect('/admin/subject/view/' . $id);
            }
        }

        if ($ajax) {
          $response = new JsonResponse($subject);
          return $response;
        } else {
          return $this->render('subjects/subject_form.html.twig', array(
              'page_title' => !empty($id) ? 'Subject: ' . $subject['subject_name'] : 'Create Subject',
              'subject_data' => $subject,
              'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
              'user_can_edit' => $user_can_edit,
              'form' => $form->createView(),
          ));
        }

    }

    /**
     * @Route("/admin/subject/view/{subject_id}", name="view_subject", methods="GET")
     */
    public function browseSubjectItems(Connection $conn, Request $request)
    {

      $subject_id = !empty($request->attributes->get('subject_id')) ? $request->attributes->get('subject_id') : false;

      // Get the parent project ID.
      $item_record = $this->repo_storage_controller->execute('getRecord', array(
          'base_table' => 'item',
          'id_field' => 'subject_id',
          'id_value' => $subject_id,
        )
      );

      // Check user's permissions.
      $username = $this->getUser()->getUsernameCanonical();
      $user_can_create = $user_can_edit = $user_can_delete = false;

      // If there's a parent project ID...
      if(array_key_exists('project_id', $item_record) && (false !== $item_record['project_id'])) {
        // Check if user has permission to access this page.
        $access = $this->repo_user_access->get_user_access($username, 'view_project_details', $item_record['project_id']);
        if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
          $response = new Response();
          $response->setStatusCode(403);
          return $response;
        }
        // Check if user has permission to create content.
        $access = $this->repo_user_access->get_user_access($username, 'create_project_details', $item_record['project_id']);
        if(array_key_exists('project_ids', $access) && in_array($item_record['project_id'], $access['project_ids'])) {
          $user_can_create = true;
        }
        // Check if user has permission to edit content.
        $access = $this->repo_user_access->get_user_access($username, 'edit_project_details', $item_record['project_id']);
        if(array_key_exists('project_ids', $access) && in_array($item_record['project_id'], $access['project_ids'])) {
          $user_can_edit = true;
        }
        // Check if user has permission to delete content.
        $access = $this->repo_user_access->get_user_access($username, 'delete_project_details', $item_record['project_id']);
        if(array_key_exists('project_ids', $access) && in_array($item_record['project_id'], $access['project_ids'])) {
          $user_can_delete = true;
        }
      }

      // If there is no parent project ID... (this means there are no items *yet* associated to the subject).
      if(!array_key_exists('project_id', $item_record)) {
        // Check if user has permission to access this page.
        $access = $this->repo_user_access->get_user_access_any($username, 'view_projects');
        if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
          $response = new Response();
          $response->setStatusCode(403);
          return $response;
        }
        // Check if user has permission to create content.
        $access = $this->repo_user_access->get_user_access_any($username, 'create_projects');
        if(array_key_exists('project_ids', $access)) {
          $user_can_create = true;
        }
        // Check if user has permission to create content.
        $access = $this->repo_user_access->get_user_access_any($username, 'edit_project_details');
        if(array_key_exists('project_ids', $access)) {
          $user_can_edit = true;
        }
        // Check if user has permission to create content.
        $access = $this->repo_user_access->get_user_access_any($username, 'delete_project_details');
        if(array_key_exists('project_ids', $access)) {
          $user_can_delete = true;
        }
      }

      // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
      $subject_data = $this->getSubject((int)$subject_id);
      if(!$subject_data) throw $this->createNotFoundException('The record does not exist');

      return $this->render('items/browse_subject_items.html.twig', array(
        'page_title' => 'Subject: ' .  $subject_data['subject_name'],
        'subject_id' => $subject_id,
        'subject_data' => $subject_data,
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        'user_can_create' => $user_can_create,
        'user_can_edit' => $user_can_edit,
        'user_can_delete' => $user_can_delete,
      ));
    }


  /**
     * Get Subject
     *
     * Run a query to retrieve one subject from the database.
     *
     * @param   int $subject_id  The subject ID
     * @return  array|bool       The query result
     */
    public function getSubject($subject_id)
    {
        $data = $this->repo_storage_controller->execute('getRecordById', array(
          'record_type' => 'subject',
          'record_id' => (int)$subject_id));
        return $data;
    }

    /**
     * Get Subjects
     *
     * Run a query to retrieve all subjects from the database.
     *
     * @return  array|bool  The query result
     */
    public function getSubjects($available_only = false)
    {

      $query_params = array(
        'base_table' => 'subject',
        'fields' => array(),
        'sort_fields' => array(
          0 => array('field_name' => 'subject_name')
        ),
        'search_params' => array(
          //0 => array('field_names' => array('project_id'), 'search_values' => array($project_id), 'comparison' => '=')
        ),
        'search_type' => 'AND',
      );
      //@todo available only
      $data = $this->repo_storage_controller->execute('getRecords', $query_params );

      return $data;
    }

    /**
     * Get Subjects (for the tree browser)
     *
     * @Route("/admin/projects/get_subjects/{project_id}/{number_first}", name="get_subjects_tree_browser", methods="GET", defaults={"number_first" = false})
     */
    public function getSubjectsTreeBrowser(Request $request, ItemController $items)
    {      
      $project_id = !empty($request->attributes->get('project_id')) ? $request->attributes->get('project_id') : false;

      $username = $this->getUser()->getUsernameCanonical();
      if(false !== $project_id) {
        $access = $this->repo_user_access->get_user_access($username, 'view_project_details', $project_id);
        if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
          return new JsonResponse(array());
        }
      }

      $subjects = $this->getSubjects($project_id);

      foreach ($subjects as $key => $value) {

          // Check for child dataset records so the 'children' key can be set accordingly.
          $item_data = $items->getItems((int)$value['subject_id']);

          $data[$key] = array(
            'id' => 'subjectId-' . $value['subject_id'],
            'children' => count($item_data) ? true : false,
            'a_attr' => array('href' => '/admin/projects/items/' . $project_id . '/' . $value['subject_id']),
          );
          
          if($request->attributes->get('number_first') === 'true') {
              $data[$key]['text'] = $value['local_subject_id'] . ' - ' . $value['subject_name'];
          } else {
              $data[$key]['text'] = $value['subject_name'] . ' - ' . $value['local_subject_id'];
          }
      }

      $response = new JsonResponse($data);
      return $response;
    }

    /**
     * Delete Multiple Subjects
     *
     * @Route("/admin/subject/delete", name="subjects_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $request  Request object
     * @return  void
     */
    public function deleteMultipleSubjects(Request $request)
    {
        $ids = $request->query->get('ids');

        $username = $this->getUser()->getUsernameCanonical();
        $access = $this->repo_user_access->get_user_access_any($username, 'edit_project_details');
        if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
          $this->addFlash('message', 'No access. You are not allowed to delete subjects.');
          return;
        }

        if(!empty($ids)) {
          $ids_array = explode(',', $ids);

          foreach ($ids_array as $key => $id) {
            $ret = $this->repo_storage_controller->execute('markSubjectInactive', array(
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
            ));
          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

}
