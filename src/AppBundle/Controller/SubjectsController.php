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

        return $this->render('subjects/browse_subjects.html.twig', array(
            'page_title' => 'Project: ' . $project_data['project_name'],
            'project_repository_id' => $project_repository_id,
            'project_data' => $project_data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_subjects/{project_repository_id}", name="subjects_browse_datatables", methods="POST")
     *
     * Browse subjects
     *
     * Run a query to retreive all subjects in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_subjects(Connection $conn, Request $request, ItemsController $items)
    {
        $sort = '';
        $search_sql = '';
        $pdo_params = array();
        $data = array();

        $req = $request->request->all();
        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;

        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY subject.last_modified DESC ";
        }

        if ($search) {
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $search_sql = "
                AND (
                  subject.subject_name LIKE ?
                  OR subject.holding_entity_guid LIKE ?
                  OR subject.last_modified LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
              subject.subject_repository_id AS manage
              ,subject.subject_repository_id
              ,subject.holding_entity_guid
              ,subject.local_subject_id
              ,subject.subject_guid
              ,subject.subject_name
              ,subject.last_modified
              ,subject.active
              ,subject.subject_repository_id AS DT_RowId
          FROM subject
          LEFT JOIN item ON item.subject_repository_id = subject.subject_repository_id
          WHERE subject.active = 1
          AND project_repository_id = " . (int)$project_repository_id . "
          {$search_sql}
          GROUP BY subject.holding_entity_guid, subject.local_subject_id, subject.subject_guid, subject.subject_name, subject.last_modified, subject.active, subject.subject_repository_id
          {$sort}
          {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

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

        $statement = $conn->prepare("SELECT FOUND_ROWS()");
        $statement->execute();
        $count = $statement->fetch();
        $data["iTotalRecords"] = $count["FOUND_ROWS()"];
        $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];
        
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
        $subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
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
            $subject_repository_id = $this->insert_update_subject($subject, $subject->project_repository_id, $subject_repository_id, $conn);

            $this->addFlash('message', 'Subject successfully updated.');
            return $this->redirect('/admin/projects/items/' . $subject->project_repository_id . '/' . $subject_repository_id);

        }

        return $this->render('subjects/subject_form.html.twig', array(
            'page_title' => !empty($subject_repository_id) ? 'Subject: ' . $subject->subject_name : 'Create Subject',
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
     * @param   object  $conn    Database connection object
     * @return  array|bool       The query result
     */
    public function get_subject($subject_id, $conn)
    {
        $statement = $conn->prepare("SELECT *
            FROM subject
            WHERE subject.active = 1
            AND subject_repository_id = :subject_repository_id");
        $statement->bindValue(":subject_repository_id", $subject_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
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
            0 => array('field_names' => array('active'), 'search_values' => array(1), 'comparison' => '='),
            1 => array('field_names' => array('project_repository_id'), 'search_values' => array($project_repository_id), 'comparison' => '=')
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
              $data[$key]['text'] = $value['local_subject_id'] . ' - ' . $value['subject_name'];
          } else {
              $data[$key]['text'] = $value['subject_name'] . ' - ' . $value['local_subject_id'];
          }
      }

      $response = new JsonResponse($data);
      return $response;
    }

    /**
     * Insert/Update subject
     *
     * Run queries to insert and update subjects in the database.
     *
     * @param   array   $data         The data array
     * @param   int     $project_repository_id  The project ID
     * @param   int     $subject_repository_id  The subject ID
     * @param   object  $conn         Database connection object
     * @return  int     The subject ID
     */
    public function insert_update_subject($data, $project_repository_id = false, $subject_repository_id = FALSE, $conn)
    {

        // Update
        if($subject_repository_id) {
          $statement = $conn->prepare("
            UPDATE subjects
            SET subject_guid = :subject_guid
            ,subject_name = :subject_name
            ,holding_entity_guid = :holding_entity_guid
            ,local_subject_id = :local_subject_id
            ,last_modified_user_account_id = :last_modified_user_account_id
            WHERE subject_repository_id = :subject_repository_id
          ");
          $statement->bindValue(":subject_guid", $data->subject_guid, PDO::PARAM_STR);
          $statement->bindValue(":subject_name", $data->subject_name, PDO::PARAM_STR);
          $statement->bindValue(":holding_entity_guid", $data->holding_entity_guid, PDO::PARAM_STR);
          $statement->bindValue(":local_subject_id", $data->local_subject_id, PDO::PARAM_STR);
          $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->bindValue(":subject_repository_id", $subject_repository_id, PDO::PARAM_INT);
          $statement->execute();

          return $subject_repository_id;
        }

        // Insert
        if(!$subject_repository_id) {

          $statement = $conn->prepare("INSERT INTO subjects
            (subject_guid, project_repository_id, subject_name, holding_entity_guid, local_subject_id, 
            date_created, created_by_user_account_id, last_modified_user_account_id )
          VALUES (:subject_guid, :project_repository_id, :subject_name, :holding_entity_guid, :local_subject_id,
             NOW(), :user_account_id, :user_account_id )");
          $statement->bindValue(":subject_guid", $data->subject_guid, PDO::PARAM_STR);
          $statement->bindValue(":project_repository_id", $project_repository_id, PDO::PARAM_INT);
          $statement->bindValue(":subject_name", $data->subject_name, PDO::PARAM_STR);
          $statement->bindValue(":holding_entity_guid", $data->holding_entity_guid, PDO::PARAM_STR);
          $statement->bindValue(":local_subject_id", $data->local_subject_id, PDO::PARAM_STR);
          $statement->bindValue(":user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->execute();
          $last_inserted_id = $conn->lastInsertId();

          if(!$last_inserted_id) {
            die('INSERT INTO `subjects` failed.');
          }

          return $last_inserted_id;

        }

    }

    /**
     * Delete Multiple Subjects
     *
     * @Route("/admin/projects/subjects/{project_repository_id}/delete", name="subjects_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $conn     Database connection object
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_subjects(Connection $conn, Request $request)
    {
        $ids = $request->query->get('ids');
        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;

        if(!empty($ids) && $project_repository_id) {

          $ids_array = explode(',', $ids);

          foreach ($ids_array as $key => $id) {

            $statement = $conn->prepare("
                UPDATE subject
                LEFT JOIN item ON item.subject_repository_id = subject.subject_repository_id
                LEFT JOIN capture_dataset ON capture_dataset.parent_item_repository_id = item.item_repository_id
                LEFT JOIN capture_data_element ON capture_data_element.capture_dataset_repository_id = capture_dataset.capture_dataset_repository_id
                SET subject.active = 0,
                    subject.last_modified_user_account_id = :last_modified_user_account_id,
                    item.active = 0,
                    item.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE subject.subject_repository_id = :id
            ");
            $statement->bindValue(":id", $id, PDO::PARAM_INT);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->execute();

          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('subjects_browse', array('project_repository_id' => $project_repository_id));
    }

    /**
     * Delete Subject
     *
     * Run a query to delete a subject from the database.
     *
     * @param   int     $subject_repository_id  The subject ID
     * @param   object  $conn         Database connection object
     * @return  void
     */
    public function delete_subject($subject_repository_id, $conn)
    {
        $statement = $conn->prepare("
            DELETE FROM subjects
            WHERE subject_repository_id = :subject_repository_id");
        $statement->bindValue(":subject_repository_id", $subject_repository_id, PDO::PARAM_INT);
        $statement->execute();
    }

}
