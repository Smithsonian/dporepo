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
        $this->repo_storage_controller->setContainer($this->container);

        $ret = $this->repo_storage_controller->build('createTable', array('table_name' => 'capture_dataset'));

        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;

        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $item = new Items();
        //$item->item_data = $item->getItem((int)$item_repository_id, $conn);
        $item_array = $this->repo_storage_controller->execute('getItem', array(
          'item_repository_id' => $item_repository_id,
        ));
        if(is_array($item_array)) {
          $item->item_data = (object)$item_array;
        }

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
        $req = $request->request->all();
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;

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
          'item_repository_id' => $item_repository_id,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $this->repo_storage_controller->setContainer($this->container);
        $data = $this->repo_storage_controller->execute('getDatatableCaptureDataset', $query_params);

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
    function show_datasets_form( $id, Connection $conn, Request $request )
    {
        $dataset = new Datasets();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;
        $dataset->project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $dataset->subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $dataset->item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;

        // Retrieve data from the database.
        if (!empty($id) && empty($post)) {
            $dataset_array = $this->repo_storage_controller->execute('getRecordById', array(
              'base_table' => 'capture_dataset',
              'id_field' => 'capture_dataset_repository_id',
              'id_value' => $id,
            ));
            if(is_array($dataset_array)) {
              $dataset = (object)$dataset_array;
            }
        }

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

        $this->repo_storage_controller->setContainer($this->container);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $dataset = $form->getData();
            $dataset_array = (array)$dataset;
            $dataset_array['parent_item_repository_id'] = $dataset_array['item_repository_id'];

            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'capture_dataset',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => $dataset_array
            ));

            $this->addFlash('message', 'Dataset successfully updated.');
            return $this->redirect('/admin/projects/datasets/' . $dataset->project_repository_id . '/' . $dataset->subject_repository_id . '/' . $dataset->item_repository_id); // . '/' . $capture_dataset_repository_id);

        }

        return $this->render('datasets/dataset_form.html.twig', array(
            'page_title' => !empty($id) ? 'Dataset: ' . $dataset->capture_dataset_name : 'Create Dataset',
            'dataset_data' => $dataset,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));

    }

    /**
     * Get Datasets
     *
     * Get datasets from the database.
     *
     * @param       int $item_repository_id    The item ID
     * @return      array|bool       The query result
     */
    public function get_datasets($item_repository_id = false)
    {

      $query_params = array(
        'item_repository_id' => $item_repository_id,
      );
      $this->repo_storage_controller->setContainer($this->container);
      $data = $this->repo_storage_controller->execute('getDatasets', $query_params);

      return $data;

    }

    /**
     * Get Datasets (for the tree browser)
     *
     * @Route("/admin/projects/get_datasets/{item_repository_id}", name="get_datasets_tree_browser", methods="GET")
     */
    public function get_datasets_tree_browser(Connection $conn, Request $request, DatasetElementsController $dataset_elements)
    {      
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;        
        $datasets = $this->get_datasets($item_repository_id);

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
          capture_dataset.capture_dataset_guid
          ,capture_dataset.capture_dataset_field_id
          ,capture_dataset.capture_method
          ,capture_dataset.capture_dataset_type
          ,capture_dataset.capture_dataset_name
          ,capture_dataset.collected_by
          ,capture_dataset.date_of_capture
          ,capture_dataset.capture_dataset_description
          ,capture_dataset.collection_notes
          ,capture_dataset.support_equipment
          ,capture_dataset.item_position_type
          ,capture_dataset.item_position_field_id
          ,capture_dataset.item_arrangement_field_id
          ,capture_dataset.positionally_matched_capture_datasets
          ,capture_dataset.focus_type
          ,capture_dataset.light_source_type
          ,capture_dataset.background_removal_method
          ,capture_dataset.cluster_type
          ,capture_dataset.cluster_geometry_field_id
          ,capture_dataset.resource_capture_datasets
          ,capture_dataset.calibration_object_used
          ,capture_dataset.date_created
          ,capture_dataset.created_by_user_account_id
          ,capture_dataset.last_modified
          ,capture_dataset.last_modified_user_account_id
          ,capture_method.label AS capture_method
          ,dataset_type.label AS capture_dataset_type
          ,item_position_type.label_alias AS item_position_type
          ,focus_type.label AS focus_type
          ,light_source_type.label AS light_source_type
          ,background_removal_method.label AS background_removal_method
          ,camera_cluster_type.label AS camera_cluster_type
        FROM capture_dataset
        LEFT JOIN capture_method ON capture_method.capture_method_repository_id = capture_dataset.capture_method
        LEFT JOIN dataset_type ON dataset_type.dataset_type_repository_id = capture_dataset.capture_dataset_type
        LEFT JOIN item_position_type ON item_position_type.item_position_type_repository_id = capture_dataset.item_position_type
        LEFT JOIN focus_type ON focus_type.focus_type_repository_id = capture_dataset.focus_type
        LEFT JOIN light_source_type ON light_source_type.light_source_type_repository_id = capture_dataset.light_source_type
        LEFT JOIN background_removal_method ON background_removal_method.background_removal_method_repository_id = capture_dataset.background_removal_method
        LEFT JOIN camera_cluster_type ON camera_cluster_type.camera_cluster_type_repository_id = capture_dataset.cluster_type
        WHERE capture_dataset.active = 1
        AND capture_dataset.capture_dataset_repository_id = :capture_dataset_repository_id");
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

      $statement = $conn->prepare("SELECT * FROM capture_method ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['capture_method_repository_id'];
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

      $statement = $conn->prepare("SELECT * FROM dataset_type ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['dataset_type_repository_id'];
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

      $statement = $conn->prepare("SELECT * FROM item_position_type ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['item_position_type_repository_id'];
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

      $statement = $conn->prepare("SELECT * FROM focus_type ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['focus_type_repository_id'];
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

      $statement = $conn->prepare("SELECT * FROM light_source_type ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['light_source_type_repository_id'];
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

      $statement = $conn->prepare("SELECT * FROM background_removal_method ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['background_removal_method_repository_id'];
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

      $statement = $conn->prepare("SELECT * FROM camera_cluster_type ORDER BY label ASC");
      $statement->execute();
      
      foreach ($statement->fetchAll(PDO::FETCH_ASSOC) as $key => $value) {
        $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $data[$label] = $value['camera_cluster_type_repository_id'];
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
                UPDATE capture_dataset
                LEFT JOIN capture_data_element ON capture_data_element.capture_data_element_repository_id = capture_dataset.capture_dataset_repository_id
                SET capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE capture_dataset.capture_dataset_repository_id = :id
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
            DELETE FROM capture_dataset
            WHERE capture_dataset_repository_id = :capture_dataset_repository_id");
        $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, PDO::PARAM_INT);
        $statement->execute();
    }

}
