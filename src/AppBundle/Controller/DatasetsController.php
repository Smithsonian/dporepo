<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use PDO;
use GUMP;

// Custom utility bundles
use AppBundle\Utils\GumpParseErrors;
use AppBundle\Utils\AppUtilities;

// Projects methods
use AppBundle\Controller\ProjectsController;
// Subjects methods
use AppBundle\Controller\SubjectsController;
// Items methods
use AppBundle\Controller\ItemsController;
// Dataset Elements methods
use AppBundle\Controller\DatasetElementsController;

class DatasetsController extends Controller
{
    /**
     * @var object $u
     */
    public $u;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
    }

    /**
     * @Route("/admin/projects/datasets/{projects_id}/{subjects_id}/{items_id}", name="datasets_browse", methods="GET")
     */
    public function browse_datasets(Connection $conn, Request $request, ProjectsController $projects, SubjectsController $subjects, ItemsController $items)
    {
        // Database tables are only created if not present.
        $create_datasets_table = $this->create_datasets_table($conn);

        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;
        $items_id = !empty($request->attributes->get('items_id')) ? $request->attributes->get('items_id') : false;

        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $item_data = $items->get_item((int)$items_id, $conn);
        if(!$item_data) throw $this->createNotFoundException('The record does not exist');
        
        $project_data = $projects->get_project((int)$projects_id, $conn);
        $subject_data = $subjects->get_subject((int)$subjects_id, $conn);
        $directoryContents = is_dir(JOBBOX_PATH) ? scandir(JOBBOX_PATH) : array();

        // Truncate the item_description.
        $more_indicator = (strlen($item_data['item_description']) > 50) ? '...' : '';
        $item_data['item_description_truncated'] = substr($item_data['item_description'], 0, 50) . $more_indicator;

        return $this->render('datasets/browse_datasets.html.twig', array(
            'page_title' => 'Item: ' . $item_data['item_name'],
            'projects_id' => $projects_id,
            'subjects_id' => $subjects_id,
            'items_id' => $items_id,
            'project_data' => $project_data,
            'subject_data' => $subject_data,
            'item_data' => $item_data,
            'destination' => $projects_id . '|' . $subjects_id . '|' . $items_id,
            'include_directory_button' => !in_array($item_data['item_guid'], $directoryContents) ? true : false,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_datasets/{projects_id}/{subjects_id}/{items_id}", name="datasets_browse_datatables", methods="POST")
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
        $items_id = !empty($request->attributes->get('items_id')) ? $request->attributes->get('items_id') : false;

        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY datasets.last_modified DESC ";
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
          $search_sql = "
              AND (
                OR datasets.dataset_guid LIKE ?
                OR datasets.capture_method_lookup_id LIKE ?
                OR datasets.dataset_type_lookup_id LIKE ?
                OR datasets.dataset_name LIKE ?
                OR datasets.collected_by LIKE ?
                OR datasets.collected_by_guid LIKE ?
                OR datasets.date_of_capture LIKE ?
                OR datasets.dataset_description LIKE ?
                OR datasets.dataset_collection_notes LIKE ?
                OR datasets.item_position_type_lookup_id LIKE ?
                OR datasets.positionally_matched_sets_id LIKE ?
                OR datasets.motion_control LIKE ?
                OR datasets.focus_lookup_id LIKE ?
                OR datasets.light_source LIKE ?
                OR datasets.light_source_type_lookup_id LIKE ?
                OR datasets.scale_bars_used LIKE ?
                OR datasets.background_removal_method_lookup_id LIKE ?
                OR datasets.camera_cluster_type_lookup_id LIKE ?
                OR datasets.array_geometry_id LIKE ?
                OR datasets.date_created LIKE ?
              ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
              datasets.datasets_id AS manage
              ,datasets.dataset_guid
              ,datasets.capture_method_lookup_id
              ,datasets.dataset_type_lookup_id
              ,datasets.dataset_name
              ,datasets.collected_by
              ,datasets.collected_by_guid
              ,datasets.date_of_capture
              ,datasets.dataset_description
              ,datasets.dataset_collection_notes
              ,datasets.item_position_type_lookup_id
              ,datasets.positionally_matched_sets_id
              ,datasets.motion_control
              ,datasets.focus_lookup_id
              ,datasets.light_source
              ,datasets.light_source_type_lookup_id
              ,datasets.scale_bars_used
              ,datasets.background_removal_method_lookup_id
              ,datasets.camera_cluster_type_lookup_id
              ,datasets.array_geometry_id
              ,datasets.date_created
              ,datasets.last_modified
              ,datasets.datasets_id AS DT_RowId
          FROM datasets
          WHERE datasets.active = 1
          AND items_id = " . (int)$items_id . "
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
     * @Route("/admin/projects/dataset/{projects_id}/{subjects_id}/{items_id}/{datasets_id}", name="datasets_manage", methods={"GET","POST"}, defaults={"datasets_id" = null})
     *
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_datasets_form( Connection $conn, Request $request, GumpParseErrors $gump_parse_errors, ItemsController $item )
    {
        $errors = false;
        $gump = new GUMP();
        $post = $request->request->all();

        // Get dataset data
        $datasets_id = !empty($request->attributes->get('datasets_id')) ? $request->attributes->get('datasets_id') : false;
        $dataset_data = $post ? $post : $this->get_dataset((int)$datasets_id, $conn);

        // Get item data
        $dataset_data['items_id'] = !empty($request->attributes->get('items_id')) ? $request->attributes->get('items_id') : false;
        $item_data = $item->get_item((int)$dataset_data['items_id'], $conn);

        $more_indicator = (strlen($item_data['item_description']) > 50) ? '...' : '';

        $dataset_data['projects_id'] = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $dataset_data['subjects_id'] = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;

        // Get data from lookup tables.
        $dataset_data['capture_methods'] = $this->get_capture_methods($conn);
        $dataset_data['dataset_types'] = $this->get_dataset_types($conn);
        $dataset_data['item_position_types'] = $this->get_item_position_types($conn);
        $dataset_data['focus_types'] = $this->get_focus_types($conn);
        $dataset_data['light_source_types'] = $this->get_light_source_types($conn);
        $dataset_data['background_removal_methods'] = $this->get_background_removal_methods($conn);
        $dataset_data['camera_cluster_types'] = $this->get_camera_cluster_types($conn);
        
        // Validate posted data.
        if(!empty($post)) {
            // "" => "required|numeric",
            // "" => "required|alpha_numeric",
            // "" => "required|date",
            // "" => "numeric|exact_len,5",
            // "" => "required|max_len,255|alpha_numeric",
            $rules = array(
                // "csrf_key" => "required",
                "capture_method_lookup_id" => "required|numeric",
                "dataset_type_lookup_id" => "required|numeric",
                "dataset_name" => "required|max_len,255",
                "collected_by" => "required|max_len,255",
                // "collected_by_guid" => "required|alpha_numeric",
                "date_of_capture" => "required|date",
                "dataset_description" => "required",
                "item_position_type_lookup_id" => "required|numeric",
                "positionally_matched_sets_id" => "alpha_numeric",
                "focus_lookup_id" => "required|numeric",
                "light_source_type_lookup_id" => "required|numeric",
                "background_removal_method_lookup_id" => "required|numeric",
                "camera_cluster_type_lookup_id" => "required|numeric",
                "array_geometry_id" => "numeric",
                // "resource_datasets" => "required|alpha_numeric",
                // "resource_dataset_elements" => "required|alpha_numeric"
            );
            $validated = $gump->validate($post, $rules);

            $errors = array();
            if ($validated !== true) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
            $items_id = $this->insert_update_datasets($post, $datasets_id, $dataset_data['items_id'], $conn);
            $this->addFlash('message', 'Dataset successfully updated.');
            return $this->redirectToRoute('datasets_browse', array('projects_id' => $dataset_data['projects_id'], 'subjects_id' => $dataset_data['subjects_id'], 'items_id' => $dataset_data['items_id']));
        } else {
            return $this->render('datasets/dataset_form.html.twig', array(
                'page_title' => (int)$datasets_id ? 'Dataset: ' . $dataset_data['dataset_name'] : 'Add a Dataset',
                'dataset_data' => $dataset_data,
                'errors' => $errors,
                'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            ));
        }

    }

    /**
     * Insert/Update dataset
     *
     * Run queries to insert and update a dataset in the database.
     *
     * @param   array   $data         The data array
     * @param   int     $datasets_id  The dataset ID
     * @param   int     $items_id     The item ID
     * @param   object  $conn         Database connection object
     * @return  int     The item ID
     */
    public function insert_update_datasets($data, $datasets_id = FALSE, $items_id = FALSE, $conn)
    {
        // Update
        if($datasets_id) {
          $statement = $conn->prepare("
            UPDATE datasets
            SET capture_method_lookup_id = :capture_method_lookup_id
            ,dataset_name = :dataset_name
            ,collected_by = :collected_by
            ,collected_by_guid = :collected_by_guid
            ,date_of_capture = :date_of_capture
            ,dataset_description = :dataset_description
            ,dataset_collection_notes = :dataset_collection_notes
            ,item_position_type_lookup_id = :item_position_type_lookup_id
            ,positionally_matched_sets_id = :positionally_matched_sets_id
            ,motion_control = :motion_control
            ,focus_lookup_id = :focus_lookup_id
            ,light_source = :light_source
            ,light_source_type_lookup_id = :light_source_type_lookup_id
            ,scale_bars_used = :scale_bars_used
            ,background_removal_method_lookup_id = :background_removal_method_lookup_id
            ,camera_cluster_type_lookup_id = :camera_cluster_type_lookup_id
            ,array_geometry_id = :array_geometry_id
            ,dataset_type_lookup_id = :dataset_type_lookup_id
            ,last_modified_user_account_id = :last_modified_user_account_id
            WHERE datasets_id = :datasets_id
          ");
          $statement->bindValue(":capture_method_lookup_id", $data['capture_method_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":dataset_name", $data['dataset_name'], PDO::PARAM_STR);
          $statement->bindValue(":collected_by", $data['collected_by'], PDO::PARAM_STR);
          $statement->bindValue(":collected_by_guid", $data['collected_by_guid'], PDO::PARAM_STR);
          $statement->bindValue(":date_of_capture", $data['date_of_capture'], PDO::PARAM_STR);
          $statement->bindValue(":dataset_description", $data['dataset_description'], PDO::PARAM_STR);
          $statement->bindValue(":dataset_collection_notes", $data['dataset_collection_notes'], PDO::PARAM_STR);
          $statement->bindValue(":item_position_type_lookup_id", $data['item_position_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":positionally_matched_sets_id", $data['positionally_matched_sets_id'], PDO::PARAM_STR);
          $statement->bindValue(":motion_control", $data['motion_control'], PDO::PARAM_STR);
          $statement->bindValue(":focus_lookup_id", $data['focus_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":light_source", $data['light_source'], PDO::PARAM_STR);
          $statement->bindValue(":light_source_type_lookup_id", $data['light_source_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":scale_bars_used", $data['scale_bars_used'], PDO::PARAM_STR);
          $statement->bindValue(":background_removal_method_lookup_id", $data['background_removal_method_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":camera_cluster_type_lookup_id", $data['camera_cluster_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":array_geometry_id", $data['array_geometry_id'], PDO::PARAM_INT);
          $statement->bindValue(":dataset_type_lookup_id", $data['dataset_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->bindValue(":datasets_id", $datasets_id, PDO::PARAM_INT);
          $statement->execute();

          return $datasets_id;
        }

        // Insert
        if(!$datasets_id) {

          $statement = $conn->prepare("INSERT INTO datasets
            (dataset_guid, items_id, capture_method_lookup_id, 
            dataset_name, collected_by, collected_by_guid, date_of_capture, dataset_description, dataset_collection_notes, item_position_type_lookup_id, positionally_matched_sets_id, 
            motion_control, focus_lookup_id, light_source, light_source_type_lookup_id, scale_bars_used, background_removal_method_lookup_id, camera_cluster_type_lookup_id, array_geometry_id, dataset_type_lookup_id,  
            date_created, created_by_user_account_id, last_modified_user_account_id )
          VALUES ((select md5(UUID())), :items_id, 
            :capture_method_lookup_id, :dataset_name, :collected_by, :collected_by_guid, :date_of_capture, :dataset_description, 
            :dataset_collection_notes, :item_position_type_lookup_id, :positionally_matched_sets_id, :motion_control, :focus_lookup_id, :light_source, :light_source_type_lookup_id, :scale_bars_used, 
            :background_removal_method_lookup_id, :camera_cluster_type_lookup_id, :array_geometry_id, :dataset_type_lookup_id, 
            NOW(), :user_account_id, :user_account_id )");
          $statement->bindValue(":items_id", $items_id, PDO::PARAM_INT);
          $statement->bindValue(":capture_method_lookup_id", $data['capture_method_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":dataset_type_lookup_id", $data['dataset_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":dataset_name", $data['dataset_name'], PDO::PARAM_STR);
          $statement->bindValue(":collected_by", $data['collected_by'], PDO::PARAM_STR);
          $statement->bindValue(":collected_by_guid", $data['collected_by_guid'], PDO::PARAM_STR);
          $statement->bindValue(":date_of_capture", $data['date_of_capture'], PDO::PARAM_STR);
          $statement->bindValue(":dataset_description", $data['dataset_description'], PDO::PARAM_STR);
          $statement->bindValue(":dataset_collection_notes", $data['dataset_collection_notes'], PDO::PARAM_STR);
          $statement->bindValue(":item_position_type_lookup_id", $data['item_position_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":positionally_matched_sets_id", $data['positionally_matched_sets_id'], PDO::PARAM_STR);
          $statement->bindValue(":motion_control", $data['motion_control'], PDO::PARAM_STR);
          $statement->bindValue(":focus_lookup_id", $data['focus_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":light_source", $data['light_source'], PDO::PARAM_STR);
          $statement->bindValue(":light_source_type_lookup_id", $data['light_source_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":scale_bars_used", $data['scale_bars_used'], PDO::PARAM_STR);
          $statement->bindValue(":background_removal_method_lookup_id", $data['background_removal_method_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":camera_cluster_type_lookup_id", $data['camera_cluster_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":array_geometry_id", $data['array_geometry_id'], PDO::PARAM_STR);
          $statement->bindValue(":user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->execute();
          $last_inserted_id = $conn->lastInsertId();

          if(!$last_inserted_id) {
            die('INSERT INTO `datasets` failed.');
          }

          return $last_inserted_id;

        }

    }

    /**
     * Get Datasets
     *
     * Get datasets from the database.
     *
     * @param       int $items_id    The item ID
     * @return      array|bool       The query result
     */
    public function get_datasets($conn, $items_id = false)
    {
      $statement = $conn->prepare("
          SELECT
              projects.projects_id,
              subjects.subjects_id,
              datasets.items_id,
              datasets.datasets_id,
              datasets.dataset_guid,
              datasets.capture_method_lookup_id,
              datasets.dataset_type_lookup_id,
              datasets.dataset_name,
              datasets.collected_by,
              datasets.collected_by_guid,
              datasets.date_of_capture,
              datasets.dataset_description,
              datasets.dataset_collection_notes,
              datasets.item_position_type_lookup_id,
              datasets.positionally_matched_sets_id,
              datasets.motion_control,
              datasets.focus_lookup_id,
              datasets.light_source,
              datasets.light_source_type_lookup_id,
              datasets.scale_bars_used,
              datasets.background_removal_method_lookup_id,
              datasets.camera_cluster_type_lookup_id,
              datasets.array_geometry_id,
              datasets.resource_datasets,
              datasets.resource_dataset_elements,
              datasets.date_created,
              datasets.created_by_user_account_id,
              datasets.last_modified,
              datasets.last_modified_user_account_id,
              datasets.active
          FROM datasets
          LEFT JOIN items ON items.items_id = datasets.items_id
          LEFT JOIN subjects ON subjects.subjects_id = items.subjects_id
          LEFT JOIN projects ON projects.projects_id = subjects.projects_id
          WHERE datasets.active = 1
          AND datasets.items_id = :items_id");
      $statement->bindValue(":items_id", $items_id, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Datasets (for the tree browser)
     *
     * @Route("/admin/projects/get_datasets/{items_id}", name="get_datasets_tree_browser", methods="GET")
     */
    public function get_datasets_tree_browser(Connection $conn, Request $request, DatasetElementsController $dataset_elements)
    {      
        $items_id = !empty($request->attributes->get('items_id')) ? $request->attributes->get('items_id') : false;        
        $datasets = $this->get_datasets($conn, $items_id);

        foreach ($datasets as $key => $value) {
            // Check for child dataset records so the 'children' key can be set accordingly.
            $dataset_elements_data = $dataset_elements->get_dataset_elements((int)$value['datasets_id'], $conn);
            $data[$key] = array(
                'id' => 'datasetId-' . $value['datasets_id'],
                'children' => count($dataset_elements_data) ? true : false,
                'text' => $value['dataset_name'],
                'a_attr' => array('href' => '/admin/projects/dataset_elements/' . $value['projects_id'] . '/' . $value['subjects_id'] . '/' . $value['items_id'] . '/' . $value['datasets_id']),
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
     * @param       int $datasets_id    The data value
     * @return      array|bool              The query result
     */
    public function get_dataset($datasets_id = false, $conn)
    {
      $statement = $conn->prepare("SELECT *
          FROM datasets
          WHERE datasets.active = 1
          AND datasets.datasets_id = :datasets_id");
      $statement->bindValue(":datasets_id", $datasets_id, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get capture_methods
     * @return  array|bool  The query result
     */
    public function get_capture_methods($conn)
    {
      $statement = $conn->prepare("SELECT * FROM capture_methods ORDER BY label ASC");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get dataset_types
     * @return  array|bool  The query result
     */
    public function get_dataset_types($conn)
    {
      $statement = $conn->prepare("SELECT * FROM dataset_types ORDER BY label ASC");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get item_position_types
     * @return  array|bool  The query result
     */
    public function get_item_position_types($conn)
    {
      $statement = $conn->prepare("SELECT * FROM item_position_types ORDER BY label ASC");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get focus_types
     * @return  array|bool  The query result
     */
    public function get_focus_types($conn)
    {
      $statement = $conn->prepare("SELECT * FROM focus_types ORDER BY label ASC");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get light_source_types
     * @return  array|bool  The query result
     */
    public function get_light_source_types($conn)
    {
      $statement = $conn->prepare("SELECT * FROM light_source_types ORDER BY label ASC");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get background_removal_methods
     * @return  array|bool  The query result
     */
    public function get_background_removal_methods($conn)
    {
      $statement = $conn->prepare("SELECT * FROM background_removal_methods ORDER BY label ASC");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get camera_cluster_types
     * @return  array|bool  The query result
     */
    public function get_camera_cluster_types($conn)
    {
      $statement = $conn->prepare("SELECT * FROM camera_cluster_types ORDER BY label ASC");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete Multiple Datasets
     *
     * @Route("/admin/projects/datasets/{projects_id}/{subjects_id}/{items_id}/delete", name="datasets_remove_records", methods={"GET"})
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
        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;
        $items_id = !empty($request->attributes->get('items_id')) ? $request->attributes->get('items_id') : false;

        if(!empty($ids) && $projects_id && $subjects_id && $items_id) {

          $ids_array = explode(',', $ids);

          foreach ($ids_array as $key => $id) {

            $statement = $conn->prepare("
                UPDATE datasets
                LEFT JOIN dataset_elements ON dataset_elements.datasets_id = datasets.datasets_id
                SET datasets.active = 0,
                    datasets.last_modified_user_account_id = :last_modified_user_account_id,
                    dataset_elements.active = 0,
                    dataset_elements.last_modified_user_account_id = :last_modified_user_account_id
                WHERE datasets.datasets_id = :id
            ");
            $statement->bindValue(":id", $id, PDO::PARAM_INT);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->execute();

          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('datasets_browse', array('projects_id' => $projects_id, 'subjects_id' => $subjects_id, 'items_id' => $items_id));
    }

    /**
     * Delete dataset
     *
     * Run a query to delete a dataset from the database.
     *
     * @param   int     $datasets_id  The dataset ID
     * @param   object  $conn         Database connection object
     * @return  void
     */
    public function delete_dataset($datasets_id, $conn)
    {
        $statement = $conn->prepare("
            DELETE FROM datasets
            WHERE datasets_id = :datasets_id");
        $statement->bindValue(":datasets_id", $datasets_id, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Create Datasets Table
     *
     * @return      void
     */
    public function create_datasets_table($conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `datasets` (
            `datasets_id` int(11) NOT NULL AUTO_INCREMENT,
            `dataset_guid` varchar(255) NOT NULL DEFAULT '',
            `items_id` int(11) NOT NULL,
            `capture_method_lookup_id` int(11) NOT NULL,
            `dataset_type_lookup_id` int(11) NOT NULL,
            `dataset_name` varchar(255) NOT NULL DEFAULT '',
            `collected_by` varchar(255) NOT NULL DEFAULT '',
            `collected_by_guid` varchar(255) NULL DEFAULT NULL,
            `date_of_capture` datetime NOT NULL,
            `dataset_description` text NOT NULL,
            `dataset_collection_notes` text NOT NULL,
            `item_position_type_lookup_id` int(11) NOT NULL,
            `positionally_matched_sets_id` varchar(255) NOT NULL DEFAULT '',
            `motion_control` varchar(255) NOT NULL DEFAULT '',
            `focus_lookup_id` varchar(255) NOT NULL DEFAULT '',
            `light_source` varchar(255) NOT NULL DEFAULT '',
            `light_source_type_lookup_id` int(11) NOT NULL,
            `scale_bars_used` varchar(255) NOT NULL DEFAULT '',
            `background_removal_method_lookup_id` varchar(255) NOT NULL DEFAULT '',
            `camera_cluster_type_lookup_id` int(11) NOT NULL,
            `array_geometry_id` varchar(255) NOT NULL DEFAULT '',
            `resource_datasets` varchar(255) NOT NULL DEFAULT '',
            `resource_dataset_elements` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`datasets_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores dataset metadata'");

        // datasets_id
        // dataset_guid
        // items_id -+++-
        // capture_method_lookup_id ----
        // dataset_type_lookup_id
        // dataset_name
        // collected_by
        // collected_by_guid (???????????????)
        // date_of_capture
        // dataset_description
        // dataset_collection_notes
        // item_position_type_lookup_id
        // positionally_matched_sets_id
        // motion_control
        // focus_lookup_id
        // light_source
        // light_source_type_lookup_id
        // scale_bars_used
        // background_removal_method_lookup_id
        // camera_cluster_type_lookup_id
        // array_geometry_id
        // resource_datasets (???????????)
        // resource_dataset_elements (???????????)

        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `datasets` failed.');
        } else {
            return TRUE;
        }
    }
}
