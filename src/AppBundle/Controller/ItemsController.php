<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use PDO;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

// Projects methods
use AppBundle\Controller\ProjectsController;
// Subjects methods
use AppBundle\Controller\SubjectsController;
// Datasets methods
use AppBundle\Controller\DatasetsController;

use AppBundle\Form\Item;
use AppBundle\Entity\Items;

class ItemsController extends Controller
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

        // Establish paths.
        if($_SERVER['SERVER_SOFTWARE'] === 'Microsoft-IIS/8.5') {
            define('BASE_ROOT', 'C:\\');
        } else {
            define('BASE_ROOT', getcwd() . '/');
        }

        define('JOBBOX_PATH', BASE_ROOT . 'JobBox');
        define('JOBBOXPROCESS_PATH', BASE_ROOT . 'JobBoxProcess');
    }

    /**
     * @Route("/admin/projects/items/{projects_id}/{subjects_id}", name="items_browse", methods="GET")
     */
    public function browse_items(Connection $conn, Request $request, ProjectsController $projects, SubjectsController $subjects)
    {
        // Database tables are only created if not present.
        $create_db_table = $this->create_items_table($conn);

        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;

        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $subject_data = $subjects->get_subject((int)$subjects_id, $conn);
        if(!$subject_data) throw $this->createNotFoundException('The record does not exist');
        
        $project_data = $projects->get_project((int)$projects_id, $conn);

        return $this->render('items/browse_items.html.twig', array(
            'page_title' => 'Subject: ' .  $subject_data['subject_name'],
            'projects_id' => $projects_id,
            'subjects_id' => $subjects_id,
            'subject_data' => $subject_data,
            'project_data' => $project_data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_items/{projects_id}/{subjects_id}", name="items_browse_datatables", methods="POST")
     *
     * Browse items
     *
     * Run a query to retreive all items in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_items(Connection $conn, Request $request)
    {
        $sort = '';
        $search_sql = '';
        $pdo_params = array();
        $data = array();

        $req = $request->request->all();
        $subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;

        // First, perform a 3D model generation status check, and update statuses in the database accordingly.
        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS items.item_guid
            FROM items
            LEFT JOIN status_types ON items.status_types_id = status_types.status_types_id
            WHERE subjects_id = " . (int)$subjects_id);
        $statement->execute();
        $results = $statement->fetchAll(PDO::FETCH_ASSOC);

        if(!empty($results)) {
            foreach ($results as $key => $value) {
                // Set 3D model generation statuses.
                $this->getDirectoryStatuses($value['item_guid'], $conn);
            }
        }

        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        switch($req['order'][0]['column']) {
            case '1':
                $sort_field = 'item_description';
                break;
            case '2':
                $sort_field = 'local_item_id';
                break;
            case '3':
                $sort_field = 'date_created';
                break;
            case '4':
                $sort_field = 'last_modified';
                break;
            case '5':
                $sort_field = 'status_label';
                break;
        }

        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY items.last_modified DESC ";
        }

        if ($search) {
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $search_sql = "
            AND (
                OR items.item_description LIKE ? 
                OR items.local_item_id LIKE ?
                OR items.date_created LIKE ?
                OR items.last_modified LIKE ?
                OR items.status_label LIKE ?
            ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
            items.items_id AS manage
            ,items.subjects_id
            ,items.local_item_id
            ,CONCAT(SUBSTRING(items.item_description,1, 50), '...') as item_description
            ,items.status_types_id
            ,items.date_created
            ,items.last_modified
            ,items.items_id AS DT_RowId
            ,status_types.label AS status_label
            ,count(distinct capture_datasets.items_id) AS datasets_count
            FROM items
            LEFT JOIN status_types ON items.status_types_id = status_types.status_types_id
            LEFT JOIN capture_datasets ON capture_datasets.items_id = items.items_id
            WHERE items.active = 1
            AND subjects_id = " . (int)$subjects_id . "
            {$search_sql}
            GROUP BY items.subjects_id, items.local_item_id, item_description, items.status_types_id, items.date_created, items.last_modified, items.items_id
            {$sort}
            {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Set status for value of zero (0).
        // TODO: create an entry in the status_types table for this.
        if(!empty($data['aaData'])) {
            foreach ($data['aaData'] as $key => $value) {
                switch($value['status_types_id']) {
                    case '0': // Not Found in JobBox
                        $data['aaData'][$key]['status_label'] = '<span class="label label-danger">Not Found in JobBox</span>';
                        break;
                    case '1': // Uploaded and Properly Labeled
                        $data['aaData'][$key]['status_label'] = '<span class="label label-default">' . $value['status_label'] . '</span>';
                        break;
                    case '2': // Transferred to Processing Directory and In Queue
                        $data['aaData'][$key]['status_label'] = '<span class="label label-warning">' . $value['status_label'] . '</span>';
                        break;
                    case '3': // Clipped via ImageMagick
                    case '4': // Master Model Processed via RealityCapture
                        $data['aaData'][$key]['status_label'] = '<span class="label label-info">' . $value['status_label'] . '</span>';
                        break;
                    case '5': // Web Ready Model Processed via InstantUV
                        $data['aaData'][$key]['status_label'] = '<span class="label label-success">' . $value['status_label'] . '</span>';
                        break;
                    case '6': // Target directory exists in JobBox
                        $data['aaData'][$key]['status_label'] = '<span class="label label-default">' . $value['status_label'] . '</span>';
                        break;
                }
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
     * Matches /admin/projects/item/*
     *
     * @Route("/admin/projects/item/{projects_id}/{subjects_id}/{items_id}", name="items_manage", methods={"GET","POST"}, defaults={"items_id" = null})
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    function show_items_form( Connection $conn, Request $request )
    {
        $item = new Items();
        $post = $request->request->all();
        $items_id = !empty($request->attributes->get('items_id')) ? $request->attributes->get('items_id') : false;
        $item->projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $item->subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;

        // Retrieve data from the database.
        $item = (!empty($items_id) && empty($post)) ? $item->getItem((int)$items_id, $conn) : $item;

        // Get data from lookup tables.
        $item->item_type_lookup_options = $this->get_item_types($conn);

        // $this->u->dumper($item);

        // Create the form
        $form = $this->createForm(Item::class, $item);
        // Handle the request
        $form->handleRequest($request);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $subject = $form->getData();
            $items_id = $this->insert_update_item($item, $item->subjects_id, $items_id, $conn);

            $this->addFlash('message', 'Item successfully updated.');
            return $this->redirect('/admin/projects/datasets/' . $subject->projects_id . '/' . $item->subjects_id . '/' . $items_id);

        }

        return $this->render('items/item_form.html.twig', array(
            'page_title' => ((int)$items_id && isset($item->local_item_id)) ? 'Item: ' . $item->local_item_id : 'Add Item',
            'item_data' => $item,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));

    }

    /**
     * Get Item
     *
     * Run a query to retrieve one subject from the database.
     *
     * @param   int $item_id   The subject ID
     * @param   object  $conn  Database connection object
     * @return  array|bool     The query result
     */
    public function get_item($item_id, $conn)
    {
        $statement = $conn->prepare("SELECT
            items.item_guid
            ,items.local_item_id
            ,items.item_description
            ,items.status_types_id
            ,items.last_modified
            ,items.items_id
            FROM items
            WHERE items.active = 1
            AND items_id = :items_id");
        $statement->bindValue(":items_id", $item_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get Items
     *
     * Run a query to retrieve all items from the database.
     *
     * @param   object  $conn  Database connection object
     * @param   int $subjects_id  The subject ID
     * @return  array|bool     The query result
     */
    public function get_items($conn, $subjects_id = false)
    {
        $statement = $conn->prepare("
            SELECT
                projects.projects_id,
                subjects.subjects_id,
                items.items_id,
                items.item_guid,
                items.subjects_id,
                items.local_item_id,
                items.item_description,
                items.date_created,
                items.created_by_user_account_id,
                items.last_modified,
                items.last_modified_user_account_id,
                items.active,
                items.status_types_id
            FROM items
            LEFT JOIN subjects ON subjects.subjects_id = items.subjects_id
            LEFT JOIN projects ON projects.projects_id = subjects.projects_id
            WHERE items.active = 1
            AND items.subjects_id = :subjects_id
            ORDER BY items.local_item_id ASC
        ");
        $statement->bindValue(":subjects_id", (int)$subjects_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Items (for the tree browser)
     *
     * @Route("/admin/projects/get_items/{subjects_id}", name="get_items_tree_browser", methods="GET")
     */
    public function get_items_tree_browser(Connection $conn, Request $request, DatasetsController $datasets)
    {      
        $subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;
        $items = $this->get_items($conn, $subjects_id);

        foreach ($items as $key => $value) {
            // Check for child dataset records so the 'children' key can be set accordingly.
            $dataset_data = $datasets->get_datasets($conn, (int)$value['items_id']);
            $data[$key] = array(
                'id' => 'itemId-' . $value['items_id'],
                'children' => count($dataset_data) ? true : false,
                'text' => $value['local_item_id'],
                'a_attr' => array('href' => '/admin/projects/datasets/' . $value['projects_id'] . '/' . $value['subjects_id'] . '/' . $value['items_id']),
            );
        }

        $response = new JsonResponse($data);
        return $response;
    }

    /**
     * Get item_types
     * @return  array|bool  The query result
     */
    public function get_item_types($conn)
    {
        $data = array();

        $statement = $conn->prepare("SELECT * FROM subject_types ORDER BY label ASC");
        $statement->execute();

        foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
            $label = $this->u->removeUnderscoresTitleCase($value['label']);
            $data[$label] = $value['subject_types_id'];
        }

        return $data;
    }

    /**
     * Insert/Update item
     *
     * Run queries to insert and update items in the database.
     *
     * @param   array   $data         The data array
     * @param   int     $subjects_id  The subject ID
     * @param   int     $items_id     The item ID
     * @param   object  $conn         Database connection object
     * @return  int     The item ID
     */
    public function insert_update_item($data, $subjects_id = false, $items_id = FALSE, $conn)
    {
        // $this->u->dumper($data);

        // Update
        if($items_id) {
            $statement = $conn->prepare("
                UPDATE items
                SET local_item_id = :local_item_id
                ,item_description = :item_description
                ,last_modified_user_account_id = :last_modified_user_account_id
                WHERE items_id = :items_id
            ");
            $statement->bindValue(":local_item_id", $data->local_item_id, PDO::PARAM_STR);
            $statement->bindValue(":item_description", $data->item_description, PDO::PARAM_STR);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->bindValue(":items_id", $items_id, PDO::PARAM_INT);
            $statement->execute();

            return $items_id;
        }

        // Insert
        if(!$items_id) {

            $statement = $conn->prepare("INSERT INTO items
                ( subjects_id, item_guid, local_item_id, item_description, 
                date_created, created_by_user_account_id, last_modified_user_account_id )
                VALUES (:subjects_id, (select md5(UUID())), :local_item_id, :item_description, NOW(), 
                :user_account_id, :user_account_id )");
            $statement->bindValue(":subjects_id", $subjects_id, PDO::PARAM_STR);
            $statement->bindValue(":local_item_id", $data->local_item_id, PDO::PARAM_STR);
            $statement->bindValue(":item_description", $data->item_description, PDO::PARAM_STR);
            $statement->bindValue(":user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
                die('INSERT INTO `items` failed.');
            }

            return $last_inserted_id;

        }

    }

    /**
     * Delete Multiple Items
     *
     * @Route("/admin/projects/items/{projects_id}/{subjects_id}/delete", name="items_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $conn     Database connection object
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_items(Connection $conn, Request $request)
    {
        $ids = $request->query->get('ids');
        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;

        if(!empty($ids) && $projects_id && $subjects_id) {

          $ids_array = explode(',', $ids);

          foreach ($ids_array as $key => $id) {

            $statement = $conn->prepare("
                UPDATE items
                LEFT JOIN datasets ON datasets.items_id = items.items_id
                LEFT JOIN dataset_elements ON dataset_elements.datasets_id = datasets.datasets_id
                SET items.active = 0,
                    items.last_modified_user_account_id = :last_modified_user_account_id,
                    datasets.active = 0,
                    datasets.last_modified_user_account_id = :last_modified_user_account_id,
                    dataset_elements.active = 0,
                    dataset_elements.last_modified_user_account_id = :last_modified_user_account_id
                WHERE items.items_id = :id
            ");
            $statement->bindValue(":id", $id, PDO::PARAM_INT);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->execute();

          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('items_browse', array('projects_id' => $projects_id, 'subjects_id' => $subjects_id));
    }

    /**
     * Delete Item
     *
     * Run a query to delete a item from the database.
     *
     * @param   int     $items_id  The item ID
     * @param   object  $conn      Database connection object
     * @return  void
     */
    public function delete_item($items_id, $conn)
    {
        $statement = $conn->prepare("
            DELETE FROM items
            WHERE items_id = :items_id");
        $statement->bindValue(":items_id", $items_id, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Directory Names
     *
     * @return array  An array of the target directory names.
     */
    public function directoryNames() {
        return array(
            'jobbox',
            'jobboxprocess',
            'clipped',
            'realitycapture',
            'instantuv'
        );
    }

    /**
     * Get Directory Statuses
     *
     * @param  bool    $itemguid  The item guid
     * @param  object  $conn      Database connection object
     * @return array   Array of directory statuses
     */
    public function getDirectoryStatuses($itemguid = '', $conn) {

        $result = $data = array();

        if(!empty($itemguid)) {

            // First, verify that the item_guid is found within the database.
            $statement = $conn->prepare("SELECT item_guid FROM items WHERE item_guid = :itemguid");
            $statement->bindValue(":itemguid", $itemguid, PDO::PARAM_STR);
            $statement->execute();
            $result = $statement->fetch(PDO::FETCH_ASSOC);
  
            // Scan the JobBox directory to see if a directory with the item_guid is present.
            if(!empty($result)) {
                // Get the target directory names.
                $directoryNames = $this->directoryNames();
                // Loop through each directory, and 
                foreach ($directoryNames as $key => $value) {
                  $data[$value] = $this->processDirectoryStatuses($value, $itemguid, $conn);
                }
  
            }

        }

      return $data;
    }

    /**
     * Process Directory Statuses
     *
     * @param  bool  $directoryScanType  The directory scan type
     * @param  bool  $itemguid           The item guid
     * @param  object  $conn             Database connection object
     * @return json  The JSON encoded data
     */
    public function processDirectoryStatuses($directoryScanType = '', $itemguid = '', $conn) {

      $data = $directoryContents = array();
      $jobBoxDirectoryExists = false;
      $data['status'] = 0;

      if(!empty($directoryScanType) && !empty($itemguid)) {

        // Get the contents of each directory.
        switch($directoryScanType) {
            case 'jobbox':

                $jobBoxDirectoryExists = is_dir(JOBBOX_PATH . '/' . $itemguid);
                $directoryContents = is_dir(JOBBOX_PATH . '/' . $itemguid) ? scandir(JOBBOX_PATH . '/' . $itemguid) : array();

                break;
            case 'jobboxprocess':

                $dirContents = is_dir(JOBBOXPROCESS_PATH . '/' . $itemguid) ? scandir(JOBBOXPROCESS_PATH . '/' . $itemguid) : array();

                if( !empty($dirContents) && in_array('_ready.txt', scandir(JOBBOXPROCESS_PATH . '/' . $itemguid)) ) {
                    $directoryContents = scandir(JOBBOXPROCESS_PATH . '/' . $itemguid);
                }

                break;
            case 'clipped':
                $dirContents = is_dir(JOBBOXPROCESS_PATH . '/' . $itemguid) ? scandir(JOBBOXPROCESS_PATH . '/' . $itemguid) : array();
                if( !empty($dirContents) && in_array('_finish_im.txt', scandir(JOBBOXPROCESS_PATH . '/' . $itemguid)) && in_array('clipped', $dirContents) ) {
                    $directoryContents = scandir(JOBBOXPROCESS_PATH . '/' . $itemguid . '/clipped');
                }
                break;
            case 'realitycapture':
                $dirContents = is_dir(JOBBOXPROCESS_PATH . '/' . $itemguid . '/processed') ? scandir(JOBBOXPROCESS_PATH . '/' . $itemguid . '/processed') : array();
                if( !empty($dirContents) && in_array('_finish_rc.txt', scandir(JOBBOXPROCESS_PATH . '/' . $itemguid)) && in_array('mesh.obj', $dirContents) ) {
                    $directoryContents = scandir(JOBBOXPROCESS_PATH . '/' . $itemguid . '/processed');
                }
                break;
            case 'instantuv':
                $dirContents = is_dir(JOBBOXPROCESS_PATH . '/' . $itemguid . '/processed') ? scandir(JOBBOXPROCESS_PATH . '/' . $itemguid . '/processed') : array();
                if( !empty($dirContents) && in_array('_finish_iuv.txt', scandir(JOBBOXPROCESS_PATH . '/' . $itemguid)) && in_array('webready', $dirContents) ) {
                    $directoryContents = scandir(JOBBOXPROCESS_PATH . '/' . $itemguid . '/processed/webready');
                }
            break;
        }

        // If the directory is not empty, build out the status arrays.
        if(!empty($directoryContents)) {
            foreach ($directoryContents as $key => $value) {

                // Set the status if the direcory exists within JobBox.
                if($jobBoxDirectoryExists) $this->updateStatus($itemguid, 6, $conn);
                // If the directory doesn't exist, set the status to 0.
                if(!$jobBoxDirectoryExists) $this->updateStatus($itemguid, 0, $conn);

                if(($value !== '.') && ($value !== '..') && ($value !== '.DS_Store')) {
                    switch($directoryScanType) {
                        case 'jobbox':
                            // Next, check to see if the '_ready.txt' file exists within the target directory.
                            if($value === '_ready.txt') {
                                $data['status'] = 1;
                                $data['directory'] = $value;
                                $this->updateStatus($itemguid, 1, $conn);
                            }
                            break;
                        case 'jobboxprocess':
                            $data['status'] = 1;
                            $data['filelist'][] = $value;
                            $this->updateStatus($itemguid, 2, $conn);
                            break;
                        case 'clipped':
                            $data['status'] = 1;
                            $data['filelist'][] = $value;
                            $this->updateStatus($itemguid, 3, $conn);
                            break;
                        case 'realitycapture':
                            $data['status'] = 1;
                            $data['filelist'][] = $value;
                            $this->updateStatus($itemguid, 4, $conn);
                            break;
                        case 'instantuv':
                            $data['status'] = 1;
                            $data['filelist'][] = $value;
                            $this->updateStatus($itemguid, 5, $conn);
                            break;
                    }
                }
            }
        }

      }

      return $data;
    }

    /**
     * Update Statuses
     *
     * @param  bool  $itemguid  The item guid
     * @param  bool  $statusid  The status id
     * @param  object  $conn    Database connection object
     * @return bool
     */
    public function updateStatus($itemguid = FALSE, $statusid = 0, $conn) {

        $updated = FALSE;

        if(!empty($itemguid)) {
            $statement = $conn->prepare("
                UPDATE items
                SET status_types_id = :statusid
                WHERE item_guid = :itemguid
            ");
            $statement->bindValue(":itemguid", $itemguid, PDO::PARAM_STR);
            $statement->bindValue(":statusid", $statusid, PDO::PARAM_INT);
            $statement->execute();
            $updated = TRUE;
        }

        return $updated;
    }

    /**
     * @Route("/admin/projects/create_directory_in_jobbox/{item_guid}/{destination}", name="create_directory_in_jobbox", methods={"GET"}, defaults={"item_guid" = false, "destination" = false})
     *
     * Create Direcory in JobBox
     *
     * @param   object  Request  Request object
     * @return  void
     */
    function create_directory_in_jobbox(Request $request)
    {
        $data = false;
        $directoryContents = array();
        $itemguid = !empty($request->attributes->get('item_guid')) ? $request->attributes->get('item_guid') : false;
        $destination = !empty($request->attributes->get('destination')) ? $request->attributes->get('destination') : false;
        $ids = explode('|', $destination);
        $message = 'The directory could not be created (' . $itemguid . '). If this persists, please contact the administrator.';
 
        if($itemguid && !empty($itemguid)) {

            $directoryContents = is_dir(JOBBOX_PATH) ? scandir(JOBBOX_PATH) : array();

            if(!in_array($itemguid, $directoryContents)) {
                // Create the directory.
                mkdir(JOBBOX_PATH . '/' . $itemguid, 0775);
                // Check to see if the directory was created.
                if(is_dir(JOBBOX_PATH . '/' . $itemguid)) {
                    $message = 'The directory has been created (' . $itemguid . ').';
                }
                
            } else {
                // The directory already exists, so don't overwrite it. Just send a mesaage.
                $message = 'The directory already exists (' . $itemguid . ').';
            }

        }

        // If the endpoint is accessed from within the application, redirect to the destination.
        // If there is no destination, return a message in JSON format.
        if($destination) {
            $this->addFlash('message', $message);
            return $this->redirectToRoute('datasets_browse', array('projects_id' => $ids[0], 'subjects_id' => $ids[1], 'items_id' => $ids[2]));
        } else {
            return $this->json(array('message' => $message));
        }
    }

    /**
     * Create Items Table
     *
     * @return  void
     */
    public function create_items_table($conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `items` (
            `items_id` int(11) NOT NULL AUTO_INCREMENT,
            `item_guid` varchar(255) NOT NULL DEFAULT '',
            `subjects_id` int(11) NOT NULL,
            `local_item_id` varchar(255) NOT NULL DEFAULT '',
            `item_description` mediumtext NOT NULL,
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            `status_types_id` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`items_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores item metadata'");

        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `items` failed.');
        } else {
            return TRUE;
        }

    }
}
