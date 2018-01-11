<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
// Datasets methods
use AppBundle\Controller\DatasetsController;

class DatasetElementsController extends Controller
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
     * @Route("/admin/projects/dataset_elements/{projects_id}/{subjects_id}/{items_id}/{datasets_id}", name="dataset_elements_browse", methods="GET")
     */
    public function browse_dataset_elements(Connection $conn, Request $request, ProjectsController $projects, SubjectsController $subjects, ItemsController $items, DatasetsController $datasets)
    {
        // Database tables are only created if not present.
        $create_dataset_elements_table = $this->create_dataset_elements_table($conn);

        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;
        $items_id = !empty($request->attributes->get('items_id')) ? $request->attributes->get('items_id') : false;
        $datasets_id = !empty($request->attributes->get('datasets_id')) ? $request->attributes->get('datasets_id') : false;
        
        $project_data = $projects->get_project((int)$projects_id, $conn);
        $subject_data = $subjects->get_subject((int)$subjects_id, $conn);
        $item_data = $items->get_item((int)$items_id, $conn);
        $dataset_data = $datasets->get_dataset((int)$datasets_id, $conn);
        $dataset_element_data = $this->get_dataset_element((int)$datasets_id, $conn);

        // Truncate the item_description.
        $more_indicator = (strlen($item_data['item_description']) > 50) ? '...' : '';
        $item_data['item_description_truncated'] = substr($item_data['item_description'], 0, 50) . $more_indicator;

        return $this->render('datasetElements/browse_dataset_elements.html.twig', array(
            'page_title' => $project_data['projects_label'] . ': ' .  $dataset_data['dataset_name'],
            'projects_id' => $projects_id,
            'subjects_id' => $subjects_id,
            'items_id' => $items_id,
            'datasets_id' => $datasets_id,
            'project_data' => $project_data,
            'subject_data' => $subject_data,
            'item_data' => $item_data,
            'dataset_data' => $dataset_data,
            'dataset_element_data' => $dataset_element_data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_dataset_elements/{projects_id}/{subjects_id}/{items_id}/{datasets_id}", name="dataset_elements_browse_datatables", methods="POST")
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
        $datasets_id = !empty($request->attributes->get('datasets_id')) ? $request->attributes->get('datasets_id') : false;

        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY dataset_elements.last_modified DESC ";
        }

        if ($search) {
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $search_sql = "
                AND (
                  dataset_elements.dataset_element_guid LIKE ?
                  OR dataset_elements.camera_id LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
                dataset_elements.datasets_id AS manage
                ,dataset_elements.dataset_element_guid
                ,dataset_elements.camera_id
                ,dataset_elements.camera_capture_position_id
                ,dataset_elements.cluster_position_id
                ,dataset_elements.camera_position_in_cluster_id
                ,dataset_elements.calibration_object_type_id
                ,dataset_elements.cameras
                ,dataset_elements.lenses
                ,dataset_elements.last_modified
                ,dataset_elements.datasets_id AS DT_RowId
            FROM dataset_elements
            WHERE datasets_id = " . (int)$datasets_id . "
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
     * @Route("/admin/projects/dataset_element/{projects_id}/{subjects_id}/{items_id}/{datasets_id}/{dataset_elements_id}", name="dataset_elements_manage", methods={"GET","POST"}, defaults={"dataset_elements_id" = null})
     *
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_datasets_form( Connection $conn, Request $request, GumpParseErrors $gump_parse_errors, ProjectsController $projects, SubjectsController $subjects, ItemsController $items, DatasetsController $datasets )
    {
        $errors = false;
        $gump = new GUMP();
        $post = $request->request->all();

        $dataset_elements_id = !empty($request->attributes->get('dataset_elements_id')) ? $request->attributes->get('dataset_elements_id') : false;
        $dataset_element_data = $post ? $post : $this->get_dataset_element((int)$dataset_elements_id, $conn);

        $dataset_element_data['projects_id'] = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $dataset_element_data['subjects_id'] = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;
        $dataset_element_data['items_id'] = !empty($request->attributes->get('items_id')) ? $request->attributes->get('items_id') : false;
        $dataset_element_data['datasets_id'] = !empty($request->attributes->get('datasets_id')) ? $request->attributes->get('datasets_id') : false;

        $project_data = $projects->get_project((int)$dataset_element_data['projects_id'], $conn);
        $subject_data = $subjects->get_subject((int)$dataset_element_data['subjects_id'], $conn);
        $item_data = $items->get_item((int)$dataset_element_data['items_id'], $conn);
        $dataset_data = $datasets->get_dataset((int)$dataset_element_data['datasets_id'], $conn);

        // Truncate the item_description.
        $more_indicator = (strlen($item_data['item_description']) > 50) ? '...' : '';
        $item_data['item_description_truncated'] = substr($item_data['item_description'], 0, 50) . $more_indicator;

        // Get data from lookup tables.
        $dataset_element_data['calibration_object_types'] = $this->get_calibration_object_types($conn);
        
        // Validate posted data.
        if(!empty($post)) {
            // "" => "required|numeric",
            // "" => "required|alpha_numeric",
            // "" => "required|date",
            // "" => "numeric|exact_len,5",
            // "" => "required|max_len,255|alpha_numeric",
            $rules = array(
                // 'csrf_key' => 'required',
                'camera_id' => 'required|numeric',
                'camera_capture_position_id' => 'required|max_len,255|alpha_numeric',
                'cluster_position_id' => 'required|max_len,255|alpha_numeric',
                'camera_position_in_cluster_id' => 'required|max_len,255|alpha_numeric',
                'calibration_object_type_id' => 'required|numeric',
                'cameras' => 'required|max_len,255',
                'lenses' => 'required|max_len,255'        
            );
            $validated = $gump->validate($post, $rules);

            $errors = array();
            if ($validated !== true) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
            $dataset_elements_id = $this->insert_update_dataset_elements($post, $dataset_element_data['datasets_id'], $dataset_elements_id, $conn);
            $this->addFlash('message', 'Dataset element successfully updated.');
            return $this->redirectToRoute('dataset_elements_browse', array('projects_id' => $dataset_element_data['projects_id'], 'subjects_id' => $dataset_element_data['subjects_id'], 'items_id' => $dataset_element_data['items_id'], 'datasets_id' => $dataset_element_data['datasets_id']));
        } else {
            return $this->render('datasetElements/dataset_element_form.html.twig', array(
                'page_title' => ((int)$dataset_elements_id && isset($dataset_element_data['dataset_element_guid'])) ? 'GUID: ' . $dataset_element_data['dataset_element_guid'] : 'Add a Dataset Element',
                'projects_id' => $dataset_element_data['projects_id'],
                'subjects_id' => $dataset_element_data['subjects_id'],
                'items_id' => $dataset_element_data['items_id'],
                'datasets_id' => $dataset_element_data['datasets_id'],
                'project_data' => $project_data,
                'subject_data' => $subject_data,
                'item_data' => $item_data,
                'dataset_data' => $dataset_data,
                'dataset_element_data' => $dataset_element_data,
                'errors' => $errors,
                'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
            ));
        }

    }

    /**
     * Insert/Update dataset element
     *
     * Run queries to insert and update a dataset element in the database.
     *
     * @param   array   $data                 The data array
     * @param   int     $datasets_id          The dataset ID
     * @param   int     $dataset_elements_id  The dataset elements ID
     * @param   object  $conn                 Database connection object
     * @return  int     The item ID
     */
    public function insert_update_dataset_elements($data, $datasets_id = FALSE, $dataset_elements_id = FALSE, $conn) {

        // Update
        if($dataset_elements_id) {
            $statement = $conn->prepare("
                UPDATE dataset_elements
                SET camera_id = :camera_id
                ,camera_capture_position_id = :camera_capture_position_id
                ,cluster_position_id = :cluster_position_id
                ,camera_position_in_cluster_id = :camera_position_in_cluster_id
                ,calibration_object_type_id = :calibration_object_type_id
                ,cameras = :cameras
                ,lenses = :lenses
                ,last_modified_user_account_id = :last_modified_user_account_id
                WHERE dataset_elements_id = :dataset_elements_id
            ");
            $statement->bindValue(":camera_id", $data['camera_id'], PDO::PARAM_STR);
            $statement->bindValue(":camera_capture_position_id", $data['camera_capture_position_id'], PDO::PARAM_STR);
            $statement->bindValue(":cluster_position_id", $data['cluster_position_id'], PDO::PARAM_STR);
            $statement->bindValue(":camera_position_in_cluster_id", $data['camera_position_in_cluster_id'], PDO::PARAM_STR);
            $statement->bindValue(":calibration_object_type_id", $data['calibration_object_type_id'], PDO::PARAM_INT);
            $statement->bindValue(":cameras", $data['cameras'], PDO::PARAM_STR);
            $statement->bindValue(":lenses", $data['lenses'], PDO::PARAM_STR);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->bindValue(":dataset_elements_id", $dataset_elements_id, PDO::PARAM_INT);
            $statement->execute();

            return $dataset_elements_id;
        }

        // Insert
        if(!$dataset_elements_id) {
            $statement = $conn->prepare("INSERT INTO dataset_elements
                (dataset_element_guid, datasets_id, camera_id, camera_capture_position_id, 
                cluster_position_id, camera_position_in_cluster_id, calibration_object_type_id, cameras, lenses, 
                date_created, created_by_user_account_id, last_modified_user_account_id )
                VALUES ((select md5(UUID())), :datasets_id, :camera_id, :camera_capture_position_id, 
                :cluster_position_id, :camera_position_in_cluster_id, :calibration_object_type_id, :cameras, :lenses, 
                NOW(), :user_account_id, :user_account_id )");
            $statement->bindValue(":datasets_id", $datasets_id, PDO::PARAM_INT);
            $statement->bindValue(":camera_id", $data['camera_id'], PDO::PARAM_STR);
            $statement->bindValue(":camera_capture_position_id", $data['camera_capture_position_id'], PDO::PARAM_STR);
            $statement->bindValue(":cluster_position_id", $data['cluster_position_id'], PDO::PARAM_STR);
            $statement->bindValue(":camera_position_in_cluster_id", $data['camera_position_in_cluster_id'], PDO::PARAM_STR);
            $statement->bindValue(":calibration_object_type_id", $data['calibration_object_type_id'], PDO::PARAM_INT);
            $statement->bindValue(":cameras", $data['cameras'], PDO::PARAM_STR);
            $statement->bindValue(":lenses", $data['lenses'], PDO::PARAM_STR);
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
     * Get Dataset Elements
     *
     * Get dataset elements from the database.
     *
     * @param       int $datasets_id  The dataset ID
     * @return      array|bool        The query result
     */
    public function get_dataset_elements($datasets_id = false, $conn)
    {
      $statement = $conn->prepare("SELECT *
          FROM dataset_elements
          WHERE datasets_id = :datasets_id");
      $statement->bindValue(":datasets_id", $datasets_id, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Dataset Element
     *
     * Get one dataset element from the database.
     *
     * @param       int $dataset_elements_id  The dataset element ID
     * @return      array|bool                The query result
     */
    public function get_dataset_element($dataset_elements_id = false, $conn)
    {
        $statement = $conn->prepare("SELECT *
            FROM dataset_elements
            WHERE dataset_elements_id = :dataset_elements_id");
        $statement->bindValue(":dataset_elements_id", $dataset_elements_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get calibration_object_types
     * @return  array|bool  The query result
     */
    public function get_calibration_object_types($conn)
    {
        $statement = $conn->prepare("SELECT * FROM calibration_object_types ORDER BY label ASC");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete dataset element
     *
     * Run a query to delete a dataset element from the database.
     *
     * @param   int     $dataset_elements_id  The dataset element ID
     * @param   object  $conn                 Database connection object
     * @return  void
     */
    public function delete_dataset_element($dataset_elements_id, $conn)
    {
        $statement = $conn->prepare("
            DELETE FROM dataset_elements
            WHERE dataset_elements_id = :dataset_elements_id");
        $statement->bindValue(":dataset_elements_id", $dataset_elements_id, PDO::PARAM_INT);
        $statement->execute();
    }

  /**
   * Create Dataset Elements Table
   *
   * @return      void
   */
  public function create_dataset_elements_table($conn)
  {
    $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `dataset_elements` (
      `dataset_elements_id` int(11) NOT NULL AUTO_INCREMENT,
      `datasets_id` int(11) NOT NULL,
      `dataset_element_guid` varchar(255) NOT NULL DEFAULT '',
      `camera_id` varchar(255) NULL DEFAULT NULL,
      `camera_capture_position_id` varchar(255) NULL DEFAULT NULL,
      `cluster_position_id` varchar(255) NULL DEFAULT NULL,
      `camera_position_in_cluster_id` varchar(255) NULL DEFAULT NULL,
      `calibration_object_type_id` int(11) NOT NULL,
      `cameras` varchar(255) NOT NULL DEFAULT '',
      `lenses` varchar(255) NOT NULL DEFAULT '',
      `date_created` datetime NOT NULL,
      `created_by_user_account_id` int(11) NOT NULL,
      `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
      `last_modified_user_account_id` int(11) NOT NULL,
      `active` tinyint(1) NOT NULL DEFAULT '1',
      PRIMARY KEY (`dataset_elements_id`),
      KEY `created_by_user_account_id` (`created_by_user_account_id`),
      KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores dataset metadata'");

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
