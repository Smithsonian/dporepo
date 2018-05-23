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

use AppBundle\Form\Subject;
use AppBundle\Entity\Subjects;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class SubjectsController extends Controller
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
     * @Route("/admin/projects/subjects/{project_repository_id}", name="subjects_browse", methods="GET", requirements={"project_repository_id"="\d+"})
     */
    public function browse_subjects(Connection $conn, Request $request, ProjectsController $projects)
    {
        // Database tables are only created if not present.
        $this->repo_storage_controller->setContainer($this->container);
        $ret = $this->repo_storage_controller->build('createTable', array('table_name' => 'subject'));

        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;

        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $project_data = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $project_repository_id));

        if(!$project_data) throw $this->createNotFoundException('The record does not exist');

        // Check to see if there are any subjects, to present the Upload Metadata button or not.
        $subjects = $this->get_subjects($this->container, (int)$project_data['project_repository_id']);

        return $this->render('subjects/browse_subjects.html.twig', array(
            'page_title' => 'Project: ' . $project_data['project_name'],
            'project_repository_id' => $project_repository_id,
            'project_data' => $project_data,
            'upload_metadata_button' => !empty($subjects) ? true : false,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_subjects/{project_repository_id}", name="subjects_browse_datatables", methods="POST")
     *
     * Browse subjects
     *
     * Run a query to retrieve all subjects in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_subjects(Request $request, ItemsController $items)
    {
        $req = $request->request->all();
        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;

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
          'project_repository_id' => $project_repository_id,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $this->repo_storage_controller->setContainer($this->container);
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
                $subject_items = $items->get_items($this->container, $value['subject_repository_id']);
                $data['aaData'][$key]['items_count'] = count($subject_items);
            }
        }

        return $this->json($data);
    }

    /**
     * Matches /admin/projects/subject/*
     *
     * @Route("/admin/projects/subject/{project_repository_id}/{subject_repository_id}", name="subjects_manage", methods={"GET","POST"}, requirements={"project_repository_id"="\d+"}, defaults={"subject_repository_id" = null})
     *
     * @param   int     $subject_repository_id  The subject ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array                 Redirect or render
     */
    function show_subjects_form( $subject_repository_id, Connection $conn, Request $request )
    {
        $subject = new Subjects();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $subject->project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;

        // Retrieve data from the database.
        $this->repo_storage_controller->setContainer($this->container);
        if(!empty($id) && empty($post)) {
          $rec = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'subject',
            'record_id' => $id));
          if(isset($rec)) {
            $subject = (object)$rec;
          }
        }

        // Create the form
        $form = $this->createForm(Subject::class, $subject);
        // Handle the request
        $form->handleRequest($request);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $subject = $form->getData();
            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'subject',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => (array)$subject
            ));

            $this->addFlash('message', 'Subject successfully updated.');
            return $this->redirect('/admin/projects/items/' . $subject->project_repository_id . '/' . $id);

        }

        return $this->render('subjects/subject_form.html.twig', array(
            'page_title' => !empty($id) ? 'Subject: ' . $subject->subject_name : 'Create Subject',
            'subject_data' => $subject,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
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
    public function get_subject($container, $subject_id)
    {
        $this->repo_storage_controller->setContainer($container);
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
     * @param   $container  The Symfony container, passed from the caller.
     * @param   int $project_repository_id  The project ID
     * @return  array|bool  The query result
     */
    public function get_subjects($container, $project_repository_id = false)
    {

      $this->repo_storage_controller->setContainer($container);
      $data = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'subject',
          'fields' => array(),
          'sort_fields' => array(
            0 => array('field_name' => 'subject_name')
          ),
          'search_params' => array(
            0 => array('field_names' => array('project_repository_id'), 'search_values' => array($project_repository_id), 'comparison' => '=')
          ),
          'search_type' => 'AND',
        )
      );

      return $data;
    }

    /**
     * Get Subjects (for the tree browser)
     *
     * @Route("/admin/projects/get_subjects/{project_repository_id}/{number_first}", name="get_subjects_tree_browser", methods="GET", defaults={"number_first" = false})
     */
    public function get_subjects_tree_browser(Request $request, ItemsController $items)
    {      
      $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
      $subjects = $this->get_subjects($this->container, $project_repository_id);

      foreach ($subjects as $key => $value) {

          // Check for child dataset records so the 'children' key can be set accordingly.
          $item_data = $items->get_items($this->container, (int)$value['subject_repository_id']);

          $data[$key] = array(
            'id' => 'subjectId-' . $value['subject_repository_id'],
            'children' => count($item_data) ? true : false,
            'a_attr' => array('href' => '/admin/projects/items/' . $project_repository_id . '/' . $value['subject_repository_id']),
          );
          
          if($request->attributes->get('number_first') === 'true') {
              $data[$key]['text'] = $value['local_subject_id'] . ' - ' . $value['subject_display_name'];
          } else {
              $data[$key]['text'] = $value['subject_display_name'] . ' - ' . $value['local_subject_id'];
          }
      }

      $response = new JsonResponse($data);
      return $response;
    }

    /**
     * Delete Multiple Subjects
     *
     * @Route("/admin/projects/subjects/{project_repository_id}/delete", name="subjects_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_subjects(Request $request)
    {
        $ids = $request->query->get('ids');
        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;

        if(!empty($ids) && $project_repository_id) {

          $ids_array = explode(',', $ids);

          $this->repo_storage_controller->setContainer($this->container);
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

        return $this->redirectToRoute('subjects_browse', array('project_repository_id' => $project_repository_id));
    }

}
