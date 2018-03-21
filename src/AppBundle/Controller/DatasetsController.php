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

use AppBundle\Form\Dataset;
use AppBundle\Entity\Datasets;
use AppBundle\Entity\Items;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class DatasetsController extends Controller
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
     * @Route("/admin/projects/datasets/{project_repository_id}/{subject_repository_id}/{item_repository_id}", name="datasets_browse", methods="GET")
     */
    public function browse_datasets(Connection $conn, Request $request, ProjectsController $projects, SubjectsController $subjects)
    {
        // Database tables are only created if not present.
        $create_datasets_table = $this->create_capture_datasets_table($conn);
        $this->repo_storage_controller->setContainer($this->container);

        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;

        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $item = new Items();
        $item->item_data = $item->getItem((int)$item_repository_id, $conn);
        if(!$item->item_data) throw $this->createNotFoundException('The record does not exist');

        $project_data = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => (int)$project_repository_id));
        $subject_data = $subjects->get_subject((int)$subject_repository_id, $conn);
        $jobBoxDirectoryContents = is_dir(JOBBOX_PATH) ? scandir(JOBBOX_PATH) : array();
        $jobBoxProcessedDirectoryContents = is_dir(JOBBOXPROCESS_PATH) ? scandir(JOBBOXPROCESS_PATH) : array();

        // Truncate the item_description.
        $more_indicator = (strlen($item->item_data->item_description) > 50) ? '...' : '';
        $item->item_data->item_description_truncated = substr($item->item_data->item_description, 0, 50) . $more_indicator;

        return $this->render('datasets/browse_datasets.html.twig', array(
            'page_title' => 'Item: ' . $item->item_data->local_item_id,
            'project_repository_id' => $project_repository_id,
            'subject_repository_id' => $subject_repository_id,
            'item_repository_id' => $item_repository_id,
            'project_data' => $project_data,
            'subject_data' => $subject_data,
            'item_data' => $item->item_data,
            'destination' => $project_repository_id . '|' . $subject_repository_id . '|' . $item_repository_id,
            'include_directory_button' => !in_array($item->item_data->item_guid, $jobBoxDirectoryContents) && !in_array($item->item_data->item_guid, $jobBoxProcessedDirectoryContents) ? true : false,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_datasets/{project_repository_id}/{subject_repository_id}/{item_repository_id}", name="datasets_browse_datatables", methods="POST")
     *
     * Browse datasets
     *
     * Run a query to retreive all datasets in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_datasets(Connection $conn, Request $request)
    {
        $sort = '';
        $search_sql = '';
        $pdo_params = array();
        $data = array();

        $req = $request->request->all();
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;

        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY capture_datasets.last_modified DESC ";
        }

        if ($search) {
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $pdo_params[] = '%'.$search.'%';
          $search_sql = "
              AND (
                OR capture_datasets.capture_dataset_guid LIKE ?
                OR capture_datasets.capture_dataset_field_id LIKE ?
                OR capture_datasets.capture_method LIKE ?
                OR capture_datasets.capture_dataset_type LIKE ?
                OR capture_datasets.capture_dataset_name LIKE ?
                OR capture_datasets.collected_by LIKE ?
                OR capture_datasets.date_of_capture LIKE ?
                OR capture_datasets.capture_dataset_description LIKE ?
                OR capture_datasets.collection_notes LIKE ?
                OR capture_datasets.support_equipment LIKE ?
                OR capture_datasets.item_position_type LIKE ?
                OR capture_datasets.item_position_field_id LIKE ?
                OR capture_datasets.item_arrangement_field_id LIKE ?
                OR capture_datasets.positionally_matched_capture_datasets LIKE ?
                OR capture_datasets.focus_type LIKE ?
                OR capture_datasets.light_source_type LIKE ?
                OR capture_datasets.background_removal_method LIKE ?
                OR capture_datasets.cluster_type LIKE ?
                OR capture_datasets.cluster_geometry_field_id LIKE ?
                OR capture_datasets.resource_capture_datasets LIKE ?
                OR capture_datasets.calibration_object_used LIKE ?
                OR capture_datasets.date_created LIKE ?
                OR capture_datasets.created_by_user_account_id LIKE ?
                OR capture_datasets.last_modified LIKE ?
                OR capture_datasets.last_modified_user_account_id LIKE ?
              ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
              capture_datasets.capture_dataset_repository_id AS manage
              ,capture_datasets.capture_dataset_guid
              ,capture_datasets.capture_dataset_field_id
              ,capture_datasets.capture_dataset_name
              ,capture_datasets.collected_by
              ,capture_datasets.date_of_capture
              ,capture_datasets.capture_dataset_description
              ,capture_datasets.collection_notes
              ,capture_datasets.support_equipment
              ,capture_datasets.item_position_field_id
              ,capture_datasets.item_arrangement_field_id
              ,capture_datasets.positionally_matched_capture_datasets
              ,capture_datasets.cluster_geometry_field_id
              ,capture_datasets.resource_capture_datasets
              ,capture_datasets.calibration_object_used
              ,capture_datasets.date_created
              ,capture_datasets.created_by_user_account_id
              ,capture_datasets.last_modified
              ,capture_datasets.last_modified_user_account_id
              ,capture_datasets.capture_dataset_repository_id AS DT_RowId
              ,capture_methods.label AS capture_method
              ,dataset_types.label AS capture_dataset_type
              ,item_position_types.label_alias AS item_position_type
              ,focus_types.label AS focus_type
              ,light_source_types.label AS light_source_type
              ,background_removal_methods.label AS background_removal_method
              ,camera_cluster_types.label AS cluster_type
          FROM capture_datasets
          LEFT JOIN capture_methods ON capture_methods.capture_methods_id = capture_datasets.capture_method
          LEFT JOIN dataset_types ON dataset_types.dataset_types_id = capture_datasets.capture_dataset_type
          LEFT JOIN item_position_types ON item_position_types.item_position_types_id = capture_datasets.item_position_type
          LEFT JOIN focus_types ON focus_types.focus_types_id = capture_datasets.focus_type
          LEFT JOIN light_source_types ON light_source_types.light_source_types_id = capture_datasets.light_source_type
          LEFT JOIN background_removal_methods ON background_removal_methods.background_removal_methods_id = capture_datasets.background_removal_method
          LEFT JOIN camera_cluster_types ON camera_cluster_types.camera_cluster_types_id = capture_datasets.cluster_type
          WHERE capture_datasets.active = 1
          AND capture_datasets.parent_item_repository_id = " . (int)$item_repository_id . "
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
     * Matches /admin/projects/dataset/*
     *
     * @Route("/admin/projects/dataset/{project_repository_id}/{subject_repository_id}/{item_repository_id}/{capture_dataset_repository_id}", name="datasets_manage", methods={"GET","POST"}, defaults={"capture_dataset_repository_id" = null})
     *
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_datasets_form( $capture_dataset_repository_id, Connection $conn, Request $request )
    {
        $dataset = new Datasets();
        $post = $request->request->all();
        $capture_dataset_repository_id = !empty($request->attributes->get('capture_dataset_repository_id')) ? $request->attributes->get('capture_dataset_repository_id') : false;
        $dataset->project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $dataset->subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $dataset->item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;

        // Retrieve data from the database.
        $dataset = (!empty($capture_dataset_repository_id) && empty($post)) ? $dataset->getDataset((int)$capture_dataset_repository_id, $conn) : $dataset;
        
        // Get data from lookup tables.
        $dataset->capture_methods_lookup_options = $this->get_capture_methods($conn);
        $dataset->dataset_types_lookup_options = $this->get_dataset_types($conn);
        $dataset->item_position_types_lookup_options = $this->get_item_position_types($conn);
        $dataset->focus_types_lookup_options = $this->get_focus_types($conn);
        $dataset->light_source_types_lookup_options = $this->get_light_source_types($conn);
        $dataset->background_removal_methods_lookup_options = $this->get_background_removal_methods($conn);
        $dataset->camera_cluster_types_lookup_options = $this->get_camera_cluster_types($conn);

        // Create the form
        $form = $this->createForm(Dataset::class, $dataset);
        // Handle the request
        $form->handleRequest($request);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $dataset = $form->getData();
            $capture_dataset_repository_id = $this->insert_update_datasets($dataset, $capture_dataset_repository_id, $dataset->item_repository_id, $conn);

            $this->addFlash('message', 'Dataset successfully updated.');
            return $this->redirect('/admin/projects/dataset_elements/' . $dataset->project_repository_id . '/' . $dataset->subject_repository_id . '/' . $dataset->item_repository_id . '/' . $capture_dataset_repository_id);

        }

        return $this->render('datasets/dataset_form.html.twig', array(
            'page_title' => !empty($capture_dataset_repository_id) ? 'Dataset: ' . $dataset->capture_dataset_name : 'Create Dataset',
            'dataset_data' => $dataset,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));

    }

    /**
     * Insert/Update dataset
     *
     * Run queries to insert and update a dataset in the database.
     *
     * @param   array   $data                       The data array
     * @param   int     $capture_dataset_repository_id        The dataset ID
     * @param   int     $parent_item_repository_id  The item ID
     * @param   object  $conn                       Database connection object
     * @return  int     The item ID
     */
    public function insert_update_datasets($data, $capture_dataset_repository_id = FALSE, $parent_item_repository_id = FALSE, $conn)
    {

        // Update
        if($capture_dataset_repository_id) {
          $statement = $conn->prepare("
            UPDATE capture_datasets
            SET 
            capture_method = :capture_method
            ,capture_dataset_type = :capture_dataset_type
            ,capture_dataset_name = :capture_dataset_name
            ,collected_by = :collected_by
            ,date_of_capture = :date_of_capture
            ,capture_dataset_description = :capture_dataset_description
            ,collection_notes = :collection_notes
            ,item_position_type = :item_position_type
            ,positionally_matched_capture_datasets = :positionally_matched_capture_datasets
            ,focus_type = :focus_type
            ,light_source_type = :light_source_type
            ,background_removal_method = :background_removal_method
            ,cluster_type = :cluster_type
            ,cluster_geometry_field_id = :cluster_geometry_field_id
            ,capture_dataset_guid = :capture_dataset_guid
            ,capture_dataset_field_id = :capture_dataset_field_id
            ,support_equipment = :support_equipment
            ,item_position_field_id = :item_position_field_id
            ,item_arrangement_field_id = :item_arrangement_field_id
            ,resource_capture_datasets = :resource_capture_datasets
            ,calibration_object_used = :calibration_object_used
            ,last_modified_user_account_id = :last_modified_user_account_id
            WHERE capture_dataset_repository_id = :capture_dataset_repository_id
          ");
          $statement->bindValue(":capture_method", $data->capture_method);
          $statement->bindValue(":capture_dataset_type", $data->capture_dataset_type);
          $statement->bindValue(":capture_dataset_name", $data->capture_dataset_name);
          $statement->bindValue(":collected_by", $data->collected_by);
          $statement->bindValue(":date_of_capture", $data->date_of_capture);
          $statement->bindValue(":capture_dataset_description", $data->capture_dataset_description);
          $statement->bindValue(":collection_notes", $data->collection_notes);
          $statement->bindValue(":item_position_type", $data->item_position_type);
          $statement->bindValue(":positionally_matched_capture_datasets", $data->positionally_matched_capture_datasets);
          $statement->bindValue(":focus_type", $data->focus_type);
          $statement->bindValue(":light_source_type", $data->light_source_type);
          $statement->bindValue(":background_removal_method", $data->background_removal_method);
          $statement->bindValue(":cluster_type", $data->cluster_type);
          $statement->bindValue(":cluster_geometry_field_id", $data->cluster_geometry_field_id);
          $statement->bindValue(":capture_dataset_guid", $data->capture_dataset_guid);
          $statement->bindValue(":capture_dataset_field_id", $data->capture_dataset_field_id);
          $statement->bindValue(":support_equipment", $data->support_equipment);
          $statement->bindValue(":item_position_field_id", $data->item_position_field_id);
          $statement->bindValue(":item_arrangement_field_id", $data->item_arrangement_field_id);
          $statement->bindValue(":resource_capture_datasets", $data->resource_capture_datasets);
          $statement->bindValue(":calibration_object_used", $data->calibration_object_used);
          $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, PDO::PARAM_INT);
          $statement->execute();

          return $capture_dataset_repository_id;
        }

        // Insert
        if(!$capture_dataset_repository_id) {

          $statement = $conn->prepare("INSERT INTO capture_datasets
            (capture_dataset_guid, parent_item_repository_id, capture_method, capture_dataset_type, capture_dataset_name, collected_by, date_of_capture, 
            capture_dataset_description, collection_notes, item_position_type, positionally_matched_capture_datasets, focus_type, 
            light_source_type, background_removal_method, cluster_type, cluster_geometry_field_id, capture_dataset_field_id, support_equipment, item_position_field_id, item_arrangement_field_id, resource_capture_datasets, calibration_object_used, date_created, created_by_user_account_id, last_modified_user_account_id )
          VALUES ((select md5(UUID())), :parent_item_repository_id, :capture_method, :capture_dataset_type, :capture_dataset_name, :collected_by, :date_of_capture, 
          :capture_dataset_description, :collection_notes, :item_position_type, :positionally_matched_capture_datasets, :focus_type, 
          :light_source_type, :background_removal_method, :cluster_type, :cluster_geometry_field_id, :capture_dataset_field_id, :support_equipment, :item_position_field_id, :item_arrangement_field_id, :resource_capture_datasets, :calibration_object_used, NOW(), :user_account_id, :user_account_id )");
          $statement->bindValue(":capture_dataset_guid", $data->capture_dataset_guid);
          $statement->bindValue(":parent_item_repository_id", $parent_item_repository_id, PDO::PARAM_INT);
          $statement->bindValue(":capture_method", $data->capture_method);
          $statement->bindValue(":capture_dataset_type", $data->capture_dataset_type);
          $statement->bindValue(":capture_dataset_name", $data->capture_dataset_name);
          $statement->bindValue(":collected_by", $data->collected_by);
          $statement->bindValue(":date_of_capture", $data->date_of_capture);
          $statement->bindValue(":capture_dataset_description", $data->capture_dataset_description);
          $statement->bindValue(":collection_notes", $data->collection_notes);
          $statement->bindValue(":item_position_type", $data->item_position_type);
          $statement->bindValue(":positionally_matched_capture_datasets", $data->positionally_matched_capture_datasets);
          $statement->bindValue(":focus_type", $data->focus_type);
          $statement->bindValue(":light_source_type", $data->light_source_type);
          $statement->bindValue(":background_removal_method", $data->background_removal_method);
          $statement->bindValue(":cluster_type", $data->cluster_type);
          $statement->bindValue(":cluster_geometry_field_id", $data->cluster_geometry_field_id);
          $statement->bindValue(":capture_dataset_field_id", $data->capture_dataset_field_id);
          $statement->bindValue(":support_equipment", $data->support_equipment);
          $statement->bindValue(":item_position_field_id", $data->item_position_field_id);
          $statement->bindValue(":item_arrangement_field_id", $data->item_arrangement_field_id);
          $statement->bindValue(":resource_capture_datasets", $data->resource_capture_datasets);
          $statement->bindValue(":calibration_object_used", $data->calibration_object_used);
          $statement->bindValue(":user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->execute();
          $last_inserted_id = $conn->lastInsertId();

          if(!$last_inserted_id) {
            die('INSERT INTO `capture_datasets` failed.');
          }

          return $last_inserted_id;

        }

    }

    /**
     * Get Datasets
     *
     * Get datasets from the database.
     *
     * @param       int $item_repository_id    The item ID
     * @return      array|bool       The query result
     */
    public function get_datasets($conn, $parent_item_repository_id = false)
    {
      $statement = $conn->prepare("
          SELECT
              projects.project_repository_id
              ,subjects.subject_repository_id
              ,capture_datasets.parent_item_repository_id
              ,capture_datasets.capture_dataset_repository_id
              ,capture_datasets.capture_dataset_guid
              ,capture_datasets.capture_dataset_field_id
              ,capture_datasets.capture_method
              ,capture_datasets.capture_dataset_type
              ,capture_datasets.capture_dataset_name
              ,capture_datasets.collected_by
              ,capture_datasets.date_of_capture
              ,capture_datasets.capture_dataset_description
              ,capture_datasets.collection_notes
              ,capture_datasets.support_equipment
              ,capture_datasets.item_position_type
              ,capture_datasets.item_position_field_id
              ,capture_datasets.item_arrangement_field_id
              ,capture_datasets.positionally_matched_capture_datasets
              ,capture_datasets.focus_type
              ,capture_datasets.light_source_type
              ,capture_datasets.background_removal_method
              ,capture_datasets.cluster_type
              ,capture_datasets.cluster_geometry_field_id
              ,capture_datasets.resource_capture_datasets
              ,capture_datasets.calibration_object_used
              ,capture_datasets.date_created
              ,capture_datasets.created_by_user_account_id
              ,capture_datasets.last_modified
              ,capture_datasets.last_modified_user_account_id
              ,capture_datasets.active
          FROM capture_datasets
          LEFT JOIN items ON items.item_repository_id = capture_datasets.parent_item_repository_id
          LEFT JOIN subjects ON subjects.subject_repository_id = items.subject_repository_id
          LEFT JOIN projects ON projects.project_repository_id = subjects.project_repository_id
          WHERE capture_datasets.active = 1
          AND capture_datasets.parent_item_repository_id = :parent_item_repository_id");
      $statement->bindValue(":parent_item_repository_id", $parent_item_repository_id, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Datasets (for the tree browser)
     *
     * @Route("/admin/projects/get_datasets/{item_repository_id}", name="get_datasets_tree_browser", methods="GET")
     */
    public function get_datasets_tree_browser(Connection $conn, Request $request, DatasetElementsController $dataset_elements)
    {      
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;        
        $datasets = $this->get_datasets($conn, $item_repository_id);

        foreach ($datasets as $key => $value) {

            // Check for child dataset records so the 'children' key can be set accordingly.
            $dataset_elements_data = $dataset_elements->get_dataset_elements((int)$value['capture_dataset_repository_id'], $conn);
            $data[$key] = array(
                'id' => 'datasetId-' . $value['capture_dataset_repository_id'],
                'children' => count($dataset_elements_data) ? true : false,
                'text' => $value['capture_dataset_name'],
                'a_attr' => array('href' => '/admin/projects/dataset_elements/' . $value['project_repository_id'] . '/' . $value['subject_repository_id'] . '/' . $value['parent_item_repository_id'] . '/' . $value['capture_dataset_repository_id']),
            );
        }

        $response = new JsonResponse($data);
        return $response;
    }

    /**
     * Get Dataset
     *
     * Get one dataset from the database.
     *
     * @param       int $capture_dataset_repository_id    The data value
     * @return      array|bool              The query result
     */
    public function get_dataset($capture_dataset_repository_id = false, $conn)
    {
      $statement = $conn->prepare("SELECT
          capture_datasets.capture_dataset_guid
          ,capture_datasets.capture_dataset_field_id
          ,capture_datasets.capture_method
          ,capture_datasets.capture_dataset_type
          ,capture_datasets.capture_dataset_name
          ,capture_datasets.collected_by
          ,capture_datasets.date_of_capture
          ,capture_datasets.capture_dataset_description
          ,capture_datasets.collection_notes
          ,capture_datasets.support_equipment
          ,capture_datasets.item_position_type
          ,capture_datasets.item_position_field_id
          ,capture_datasets.item_arrangement_field_id
          ,capture_datasets.positionally_matched_capture_datasets
          ,capture_datasets.focus_type
          ,capture_datasets.light_source_type
          ,capture_datasets.background_removal_method
          ,capture_datasets.cluster_type
          ,capture_datasets.cluster_geometry_field_id
          ,capture_datasets.resource_capture_datasets
          ,capture_datasets.calibration_object_used
          ,capture_datasets.date_created
          ,capture_datasets.created_by_user_account_id
          ,capture_datasets.last_modified
          ,capture_datasets.last_modified_user_account_id
          ,capture_methods.label AS capture_method
          ,dataset_types.label AS capture_dataset_type
          ,item_position_types.label_alias AS item_position_type
          ,focus_types.label AS focus_type
          ,light_source_types.label AS light_source_type
          ,background_removal_methods.label AS background_removal_method
          ,camera_cluster_types.label AS camera_cluster_type
        FROM capture_datasets
        LEFT JOIN capture_methods ON capture_methods.capture_methods_id = capture_datasets.capture_method
        LEFT JOIN dataset_types ON dataset_types.dataset_types_id = capture_datasets.capture_dataset_type
        LEFT JOIN item_position_types ON item_position_types.item_position_types_id = capture_datasets.item_position_type
        LEFT JOIN focus_types ON focus_types.focus_types_id = capture_datasets.focus_type
        LEFT JOIN light_source_types ON light_source_types.light_source_types_id = capture_datasets.light_source_type
        LEFT JOIN background_removal_methods ON background_removal_methods.background_removal_methods_id = capture_datasets.background_removal_method
        LEFT JOIN camera_cluster_types ON camera_cluster_types.camera_cluster_types_id = capture_datasets.cluster_type
        WHERE capture_datasets.active = 1
        AND capture_datasets.capture_dataset_repository_id = :capture_dataset_repository_id");
      $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get capture_methods
     * @return  array|bool  The query result
     */
    public function get_capture_methods($conn)
    {
      $data = array();

      $statement = $conn->prepare("SELECT * FROM capture_methods ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['capture_methods_id'];
      }

      return $data;
    }

    /**
     * Get dataset_types
     * @return  array|bool  The query result
     */
    public function get_dataset_types($conn)
    {
      $data = array();

      $statement = $conn->prepare("SELECT * FROM dataset_types ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['dataset_types_id'];
      }

      return $data;
    }

    /**
     * Get item_position_types
     * @return  array|bool  The query result
     */
    public function get_item_position_types($conn)
    {
      $data = array();

      $statement = $conn->prepare("SELECT * FROM item_position_types ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['item_position_types_id'];
      }

      return $data;
    }

    /**
     * Get focus_types
     * @return  array|bool  The query result
     */
    public function get_focus_types($conn)
    {
      $data = array();

      $statement = $conn->prepare("SELECT * FROM focus_types ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['focus_types_id'];
      }

      return $data;
    }

    /**
     * Get light_source_types
     * @return  array|bool  The query result
     */
    public function get_light_source_types($conn)
    {
      $data = array();

      $statement = $conn->prepare("SELECT * FROM light_source_types ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['light_source_types_id'];
      }

      return $data;
    }

    /**
     * Get background_removal_methods
     * @return  array|bool  The query result
     */
    public function get_background_removal_methods($conn)
    {
      $data = array();

      $statement = $conn->prepare("SELECT * FROM background_removal_methods ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['background_removal_methods_id'];
      }

      return $data;
    }

    /**
     * Get camera_cluster_types
     * @return  array|bool  The query result
     */
    public function get_camera_cluster_types($conn)
    {
      $data = array();

      $statement = $conn->prepare("SELECT * FROM camera_cluster_types ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['camera_cluster_types_id'];
      }

      return $data;
    }

    /**
     * Delete Multiple Datasets
     *
     * @Route("/admin/projects/datasets/{project_repository_id}/{subject_repository_id}/{item_repository_id}/delete", name="datasets_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $conn     Database connection object
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_datasets(Connection $conn, Request $request)
    {
        $ids = $request->query->get('ids');
        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;

        if(!empty($ids) && $project_repository_id && $subject_repository_id && $item_repository_id) {

          $ids_array = explode(',', $ids);

          foreach ($ids_array as $key => $id) {

            $statement = $conn->prepare("
                UPDATE capture_datasets
                LEFT JOIN capture_data_elements ON capture_data_elements.capture_data_element_repository_id = capture_datasets.capture_dataset_repository_id
                SET capture_datasets.active = 0,
                    capture_datasets.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_elements.active = 0,
                    capture_data_elements.last_modified_user_account_id = :last_modified_user_account_id
                WHERE capture_datasets.capture_dataset_repository_id = :id
            ");
            $statement->bindValue(":id", $id, PDO::PARAM_INT);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->execute();

          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('datasets_browse', array('project_repository_id' => $project_repository_id, 'subject_repository_id' => $subject_repository_id, 'item_repository_id' => $item_repository_id));
    }

    /**
     * Delete dataset
     *
     * Run a query to delete a dataset from the database.
     *
     * @param   int     $capture_dataset_repository_id  The dataset ID
     * @param   object  $conn         Database connection object
     * @return  void
     */
    public function delete_dataset($capture_dataset_repository_id, $conn)
    {
        $statement = $conn->prepare("
            DELETE FROM capture_datasets
            WHERE capture_dataset_repository_id = :capture_dataset_repository_id");
        $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Create Datasets Table
     *
     * @return      void
     */
    public function create_capture_datasets_table($conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `capture_datasets` (
          `capture_dataset_repository_id` int(11) NOT NULL AUTO_INCREMENT,
          `capture_dataset_guid` varchar(255) NOT NULL DEFAULT '',
          `parent_project_repository_id` int(255) DEFAULT NULL,
          `parent_item_repository_id` int(11) NOT NULL,
          `capture_dataset_field_id` int(11) NOT NULL,
          `capture_method` int(11) DEFAULT NULL,
          `capture_dataset_type` int(11) DEFAULT NULL,
          `capture_dataset_name` varchar(255) NOT NULL DEFAULT '',
          `collected_by` varchar(255) NOT NULL DEFAULT '',
          `date_of_capture` datetime NOT NULL,
          `capture_dataset_description` text,
          `collection_notes` text,
          `support_equipment` varchar(255) DEFAULT NULL,
          `item_position_type` int(11) DEFAULT NULL,
          `item_position_field_id` int(11) NOT NULL,
          `item_arrangement_field_id` int(11) NOT NULL,
          `positionally_matched_capture_datasets` varchar(255) DEFAULT '',
          `focus_type` int(11) DEFAULT NULL,
          `light_source_type` int(11) DEFAULT NULL,
          `background_removal_method` int(11) DEFAULT NULL,
          `cluster_type` int(11) DEFAULT NULL,
          `cluster_geometry_field_id` int(11) DEFAULT NULL,
          `resource_capture_datasets` varchar(255) DEFAULT '',
          `calibration_object_used` varchar(255) DEFAULT '',
          `date_created` datetime NOT NULL,
          `created_by_user_account_id` int(11) NOT NULL,
          `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `last_modified_user_account_id` int(11) NOT NULL,
          `active` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`capture_dataset_repository_id`),
          KEY `created_by_user_account_id` (`created_by_user_account_id`),
          KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8 COMMENT='This table stores capture_datasets metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `capture_datasets` failed.');
        } else {
            return TRUE;
        }
    }
}
