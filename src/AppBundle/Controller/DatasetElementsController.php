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

use AppBundle\Form\DatasetElement;
use AppBundle\Entity\DatasetElements;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class DatasetElementsController extends Controller
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
     * @Route("/admin/projects/dataset_elements/{project_repository_id}/{subject_repository_id}/{item_repository_id}/{capture_dataset_repository_id}", name="dataset_elements_browse", methods="GET")
     */
    public function browse_dataset_elements(Connection $conn, Request $request, ProjectsController $projects, SubjectsController $subjects, ItemsController $items, DatasetsController $datasets)
    {
        // Database tables are only created if not present.
        $this->repo_storage_controller->setContainer($this->container);
        $ret = $this->repo_storage_controller->build('createTable', array('table_name' => 'dataset_element'));

        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;
        $capture_dataset_repository_id = !empty($request->attributes->get('capture_dataset_repository_id')) ? $request->attributes->get('capture_dataset_repository_id') : false;

        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $dataset_data = $datasets->get_dataset((int)$capture_dataset_repository_id, $conn);
        if(!$dataset_data) throw $this->createNotFoundException('The record does not exist');
        
        $project_data = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $project_repository_id));

        $subject_data = $subjects->get_subject((int)$subject_repository_id, $conn);
        $item_data = $items->get_item((int)$item_repository_id, $conn);
        $dataset_element_data = $this->get_dataset_element((int)$capture_dataset_repository_id, $conn);

        // Truncate the item_description.
        $more_indicator = (strlen($item_data['item_description']) > 50) ? '...' : '';
        $item_data['item_description_truncated'] = substr($item_data['item_description'], 0, 50) . $more_indicator;

        return $this->render('datasetElements/browse_dataset_elements.html.twig', array(
            'page_title' => 'Capture Dataset: ' .  $dataset_data['capture_dataset_name'],
            'project_repository_id' => $project_repository_id,
            'subject_repository_id' => $subject_repository_id,
            'item_repository_id' => $item_repository_id,
            'capture_dataset_repository_id' => $capture_dataset_repository_id,
            'project_data' => $project_data,
            'subject_data' => $subject_data,
            'item_data' => $item_data,
            'dataset_data' => $dataset_data,
            'dataset_element_data' => $dataset_element_data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_dataset_elements/{project_repository_id}/{subject_repository_id}/{item_repository_id}/{capture_dataset_repository_id}", name="dataset_elements_browse_datatables", methods="POST")
     *
     * Browse dataset_elements
     *
     * Run a query to retreive all dataset elements in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_dataset_elements(Connection $conn, Request $request)
    {
        $sort = '';
        $search_sql = '';
        $pdo_params = array();
        $data = array();

        $req = $request->request->all();
        $capture_dataset_repository_id = !empty($request->attributes->get('capture_dataset_repository_id')) ? $request->attributes->get('capture_dataset_repository_id') : false;

        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY capture_data_element.last_modified DESC ";
        }

        if ($search) {
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $search_sql = "
                AND (
                  capture_data_element.dataset_element_guid LIKE ?
                  OR capture_data_element.camera_id LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
                capture_data_element.capture_data_element_repository_id AS manage
                ,capture_data_element.capture_dataset_repository_id
                ,capture_data_element.capture_device_configuration_id
                ,capture_data_element.capture_device_field_id
                ,capture_data_element.capture_sequence_number
                ,capture_data_element.cluster_position_field_id
                ,capture_data_element.position_in_cluster_field_id
                ,capture_data_element.date_created
                ,capture_data_element.created_by_user_account_id
                ,capture_data_element.last_modified
                ,capture_data_element.last_modified_user_account_id
                ,capture_data_element.capture_data_element_repository_id AS DT_RowId
            FROM capture_data_element
            WHERE capture_data_element.active = 1
            AND capture_dataset_repository_id = " . (int)$capture_dataset_repository_id . "
            {$search_sql}
            {$sort}
            {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement = $conn->prepare("SELECT FOUND_ROWS()");
        $statement->execute();
        $count = $statement->fetch();
        $data["iTotalRecords"] = $count["FOUND_ROWS()"];
        $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];
        
        return $this->json($data);
    }

    /**
     * Matches /admin/projects/dataset_element/*
     *
     * @Route("/admin/projects/dataset_element/{project_repository_id}/{subject_repository_id}/{item_repository_id}/{capture_dataset_repository_id}/{capture_data_element_rep_id}", name="dataset_elements_manage", methods={"GET","POST"}, defaults={"capture_data_element_rep_id" = null})
     *
     * Note: capture_data_element_rep_id does not follow naming convention due to a 32 character limit for route variables in Symfony.
     * The error - "Variable name "capture_data_element_repository_id" cannot be longer than 32 characters in route pattern"
     *
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_dataset_elements_form( Connection $conn, Request $request, ProjectsController $projects, SubjectsController $subjects, ItemsController $items, DatasetsController $datasets )
    {
        $this->repo_storage_controller->setContainer($this->container);

        $dataset_element = new DatasetElements();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('capture_data_element_rep_id')) ? $request->attributes->get('capture_data_element_rep_id') : false;
        
        // Retrieve data from the database.
        if (!empty($id) && empty($post)) {
          $dataset_element_array = $this->repo_storage_controller->execute('getRecordById', array(
            'base_table' => 'capture_data_element',
            'id_field' => 'capture_data_element_repository_id',
            'id_value' => $id,
          ));
          if(is_array($dataset_element_array)) {
            $dataset_element = (object)$dataset_element_array;
          }
        }

        $dataset_element->project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $dataset_element->subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $dataset_element->item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;
        $dataset_element->capture_dataset_repository_id = !empty($request->attributes->get('capture_dataset_repository_id')) ? $request->attributes->get('capture_dataset_repository_id') : false;
        
        // Get data for the breadcumbs.
        // TODO: find a better way?
        $project_data = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => (int)$dataset_element->project_repository_id));
        $subject_data = $subjects->get_subject((int)$dataset_element->subject_repository_id, $conn);
        $item_data = $items->get_item((int)$dataset_element->item_repository_id, $conn);
        $dataset_data = $datasets->get_dataset((int)$dataset_element->capture_dataset_repository_id, $conn);
        
        // Truncate the item_description so the breadcrumb don't blow up.
        $more_indicator = (strlen($item_data['item_description']) > 50) ? '...' : '';
        $item_data['item_description_truncated'] = substr($item_data['item_description'], 0, 50) . $more_indicator;

        // Get data from lookup tables.
        $dataset_element->calibration_object_type_options = $this->get_calibration_object_types($conn);

        // Create the form
        $form = $this->createForm(DatasetElement::class, $dataset_element);
        // Handle the request
        $form->handleRequest($request);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $dataset_element = $form->getData();
            $dataset_array = (array)$dataset_element;

            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'capture_data_element',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => $dataset_array
            ));

            $this->addFlash('message', 'Dataset Element successfully updated.');
            return $this->redirectToRoute('dataset_elements_browse', array('project_repository_id' => $dataset_element->project_repository_id, 'subject_repository_id' => $dataset_element->subject_repository_id, 'item_repository_id' => $dataset_element->item_repository_id, 'capture_dataset_repository_id' => $dataset_element->capture_dataset_repository_id));

        }

        return $this->render('datasetElements/dataset_element_form.html.twig', array(
            'page_title' => ((int)$id && isset($dataset_element->capture_sequence_number)) ? 'Dataset Element: ' . $dataset_element->capture_sequence_number : 'Add a Dataset Element',
            'project_repository_id' => $dataset_element->project_repository_id,
            'subject_repository_id' => $dataset_element->subject_repository_id,
            'item_repository_id' => $dataset_element->item_repository_id,
            'capture_dataset_repository_id' => $dataset_element->capture_dataset_repository_id,
            'project_data' => $project_data,
            'subject_data' => $subject_data,
            'item_data' => $item_data,
            'dataset_data' => $dataset_data,
            'dataset_element_data' => $dataset_element,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));

    }

    /**
     * Get Dataset Elements
     *
     * Get dataset elements from the database.
     *
     * @param       int $capture_dataset_repository_id  The dataset ID
     * @return      array|bool        The query result
     */
    public function get_dataset_elements($capture_dataset_repository_id = false, $conn)
    {
        $statement = $conn->prepare("
            SELECT
                project.project_repository_id,
                subject.subject_repository_id,
                item.item_repository_id,
                capture_data_element.capture_data_element_repository_id,
                capture_data_element.capture_dataset_repository_id,
                capture_data_element.capture_device_configuration_id,
                capture_data_element.capture_device_field_id,
                capture_data_element.capture_sequence_number,
                capture_data_element.cluster_position_field_id,
                capture_data_element.position_in_cluster_field_id,
                capture_data_element.date_created,
                capture_data_element.created_by_user_account_id,
                capture_data_element.last_modified,
                capture_data_element.last_modified_user_account_id,
                capture_data_element.active
            FROM capture_data_element
            LEFT JOIN capture_dataset ON capture_dataset.capture_dataset_repository_id = capture_data_element.capture_dataset_repository_id
            LEFT JOIN item ON item.item_repository_id = capture_dataset.parent_item_repository_id
            LEFT JOIN subject ON subject.subject_repository_id = item.subject_repository_id
            LEFT JOIN project ON project.project_repository_id = subject.project_repository_id
            WHERE capture_data_element.active = 1
            AND capture_data_element.capture_dataset_repository_id = :capture_dataset_repository_id");
        $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Dataset Elements (for the tree browser)
     *
     * @Route("/admin/projects/get_dataset_elements/{capture_dataset_repository_id}", name="get_dataset_elements_tree_browser", methods="GET")
     */
    public function get_dataset_elements_tree_browser(Connection $conn, Request $request)
    {      
        $capture_dataset_repository_id = !empty($request->attributes->get('capture_dataset_repository_id')) ? $request->attributes->get('capture_dataset_repository_id') : false;        
        $capture_data_element = $this->get_dataset_elements($capture_dataset_repository_id, $conn);

        foreach ($capture_data_element as $key => $value) {
            $data[$key] = array(
                'id' => 'datasetElementId-' . $value['capture_data_element_repository_id'],
                'children' => false,
                'text' => $value['capture_sequence_number'],
                'a_attr' => array('href' => '/admin/projects/dataset_element/' . $value['project_repository_id'] . '/' . $value['subject_repository_id'] . '/' . $value['item_repository_id'] . '/' . $value['capture_dataset_repository_id'] . '/' . $value['capture_data_element_repository_id']),
            );
        }

        $response = new JsonResponse($data);
        return $response;
    }

    /**
     * Get Dataset Element
     *
     * Get one dataset element from the database.
     *
     * @param       int $capture_data_element_repository_id  The dataset element ID
     * @return      array|bool                The query result
     */
    public function get_dataset_element($capture_data_element_repository_id = false)
    {
        $this->repo_storage_controller->setContainer($this->container);
        $record = $this->repo_storage_controller->execute('getRecordById',
          array(
            'base_table' => 'capture_data_element',
            'id_field' => 'capture_data_element_repository_id',
            'id_value' => $capture_data_element_repository_id
          )
        );
        return $record;
    }

    /**
     * Get calibration_object_types
     * @return  array|bool  The query result
     */
    public function get_calibration_object_types()
    {
        $data = array();

        $this->repo_storage_controller->setContainer($this->container);
        $records = $this->repo_storage_controller->execute('getRecords',
          array(
            'base_table' => 'calibration_object_type',
          )
        );
        foreach ($records as $key => $value) {
            $label = $this->u->removeUnderscoresTitleCase($value['label']);
            $data[$label] = $value['calibration_object_type_repository_id'];
        }

        return $data;
    }

    /**
     * Delete Multiple Capture Data Elements
     *
     * @Route("/admin/projects/dataset_elements/{project_repository_id}/{subject_repository_id}/{item_repository_id}/{capture_dataset_repository_id}/delete", name="dataset_elements_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $conn     Database connection object
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_capture_data_element(Connection $conn, Request $request)
    {
        $ids = $request->query->get('ids');
        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;
        $capture_dataset_repository_id = !empty($request->attributes->get('capture_dataset_repository_id')) ? $request->attributes->get('capture_dataset_repository_id') : false;

        if(!empty($ids) && $project_repository_id && $subject_repository_id && $item_repository_id && $capture_dataset_repository_id) {

          $ids_array = explode(',', $ids);

          $this->repo_storage_controller->setContainer($this->container);

          // Loop thorough the ids.
          foreach ($ids_array as $key => $id) {
            // Run the query against a single record.
            $ret = $this->repo_storage_controller->execute('markRecordsInactive', array(
              'record_type' => $this->table_name,
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
            ));
          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('dataset_elements_browse', array('project_repository_id' => $project_repository_id, 'subject_repository_id' => $subject_repository_id, 'item_repository_id' => $item_repository_id, 'capture_dataset_repository_id' => $capture_dataset_repository_id));
    }

    /**
     * Delete dataset element
     *
     * Run a query to delete a dataset element from the database.
     *
     * @param   int     $capture_data_element_repository_id  The dataset element ID
     * @param   object  $conn                 Database connection object
     * @return  void
     */
    public function delete_dataset_element($capture_data_element_repository_id, $conn)
    {
        $this->repo_storage_controller->setContainer($this->container);
        $ret = $this->repo_storage_controller->execute('markRecordsInactive', array(
          'record_type' => 'capture_data_element',
          'record_id' => $capture_data_element_repository_id,
          'user_id' => $this->getUser()->getId(),
        ));
    }

}
