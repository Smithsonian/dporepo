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
     * @var string
     */
    private $file_upload_path;

    /**
     * @var string
     */
    private $file_processing_path;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, $file_upload_path, $file_processing_path)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController();

        $this->file_upload_path = $file_upload_path;
        $this->file_processing_path = $file_processing_path;
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
        $item_array = $this->repo_storage_controller->execute('getItem', array(
          'item_repository_id' => $item_repository_id,
        ));
        if(is_array($item_array)) {
          $item->item_data = (object)$item_array;
        }
        // Throw a createNotFoundException (404).
        if(!isset($item->item_data->item_repository_id)) throw $this->createNotFoundException('The record does not exist');

        $project_data = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => (int)$project_repository_id));
        $subject_data = $subjects->get_subject($this->container, (int)$subject_repository_id);
        $jobBoxDirectoryContents = is_dir($this->file_upload_path) ? scandir($this->file_upload_path) : array();
        $jobBoxProcessedDirectoryContents = is_dir($this->file_processing_path) ? scandir($this->file_processing_path) : array();

        return $this->render('datasets/browse_datasets.html.twig', array(
            'page_title' => isset($item->item_data->item_display_name) ? 'Item: ' . $item->item_data->item_display_name : 'Item',
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
     * Run a query to retrieve all datasets in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_datasets(Request $request)
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
     * @Route("/admin/projects/dataset/{parent_project_repository_id}/{parent_subject_repository_id}/{parent_item_repository_id}/{capture_dataset_repository_id}", name="datasets_manage", methods={"GET","POST"}, defaults={"capture_dataset_repository_id" = null})     *
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_datasets_form( Connection $conn, Request $request )
    {
        $dataset = new Datasets();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('capture_dataset_repository_id')) ? $request->attributes->get('capture_dataset_repository_id') : false;

        $this->repo_storage_controller->setContainer($this->container);

        // Retrieve data from the database.
        if (!empty($id) && empty($post)) {
            $dataset_array = $this->repo_storage_controller->execute('getCaptureDataset', array(
              'capture_dataset_repository_id' => $id,
            ));
            if(is_array($dataset_array)) {
              $dataset = (object)$dataset_array;
            }
        }
        $dataset->parent_project_repository_id = !empty($request->attributes->get('parent_project_repository_id')) ? $request->attributes->get('parent_project_repository_id') : false;
        $dataset->parent_subject_repository_id = !empty($request->attributes->get('parent_subject_repository_id')) ? $request->attributes->get('parent_subject_repository_id') : false;
        $dataset->parent_item_repository_id = !empty($request->attributes->get('parent_item_repository_id')) ? $request->attributes->get('parent_item_repository_id') : false;

        $dataset->parent_project_repository_id = !empty($request->attributes->get('parent_project_repository_id')) ? $request->attributes->get('parent_project_repository_id') : false;
        $dataset->parent_subject_repository_id = !empty($request->attributes->get('parent_subject_repository_id')) ? $request->attributes->get('parent_subject_repository_id') : false;
        $dataset->parent_item_repository_id = !empty($request->attributes->get('parent_item_repository_id')) ? $request->attributes->get('parent_item_repository_id') : false;

        // Get data from lookup tables.
        $dataset->capture_methods_lookup_options = $this->get_capture_methods($this->container);
        $dataset->dataset_types_lookup_options = $this->get_dataset_types($this->container);
        $dataset->item_position_types_lookup_options = $this->get_item_position_types($this->container);
        $dataset->focus_types_lookup_options = $this->get_focus_types($this->container);
        $dataset->light_source_types_lookup_options = $this->get_light_source_types($this->container);
        $dataset->background_removal_methods_lookup_options = $this->get_background_removal_methods($this->container);
        $dataset->camera_cluster_types_lookup_options = $this->get_camera_cluster_types($this->container);
        $dataset->calibration_object_type_options = $this->get_calibration_object_types();

        // Create the form
        $form = $this->createForm(Dataset::class, $dataset);
        // Handle the request
        $form->handleRequest($request);

        $this->repo_storage_controller->setContainer($this->container);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $dataset = $form->getData();
            $dataset_array = (array)$dataset;
            //$dataset_array['item_repository_id'] = $dataset_array['item_repository_id'];

            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'capture_dataset',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => $dataset_array
            ));

            $this->addFlash('message', 'Capture Dataset successfully updated.');
            return $this->redirect('/admin/projects/dataset_elements/' . $dataset->parent_project_repository_id . '/' . $dataset->parent_subject_repository_id . '/' . $dataset->parent_item_repository_id . '/' . $id);
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
    public function get_datasets($container, $item_repository_id = false)
    {

      $query_params = array(
        'item_repository_id' => $item_repository_id,
      );
      $this->repo_storage_controller->setContainer($container);
      $data = $this->repo_storage_controller->execute('getDatasets', $query_params);

      return $data;

    }

    /**
     * Get Datasets (for the tree browser)
     *
     * @Route("/admin/projects/get_datasets/{item_repository_id}", name="get_datasets_tree_browser", methods="GET")
     */
    public function get_datasets_tree_browser(Request $request, DatasetElementsController $dataset_elements)
    {      
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;        
        $datasets = $this->get_datasets($this->container, $item_repository_id);

        foreach ($datasets as $key => $value) {

            // Check for child dataset records so the 'children' key can be set accordingly.
            $dataset_elements_data = $dataset_elements->get_dataset_elements($this->container, (int)$value['capture_dataset_repository_id']);
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
    public function get_dataset($container, $capture_dataset_repository_id = false)
    {
      $query_params = array(
        'capture_dataset_repository_id' => $capture_dataset_repository_id,
      );
      $this->repo_storage_controller->setContainer($container);
      $data = $this->repo_storage_controller->execute('getCaptureDataset', $query_params);
      return $data;
    }

    /**
     * Get capture_methods
     * @return  array|bool  The query result
     */
    public function get_capture_methods($this_container)
    {
      $data = array();
      $this->repo_storage_controller->setContainer($this_container);
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'capture_method',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        // $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $label = $value['label'];
        $data[$label] = $value['capture_method_repository_id'];
      }

      return $data;
    }

    /**
     * Get dataset_types
     * @return  array|bool  The query result
     */
    public function get_dataset_types($this_container)
    {
      $data = array();
      $this->repo_storage_controller->setContainer($this_container);
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'dataset_type',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        // $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $label = $value['label'];
        $data[$label] = $value['dataset_type_repository_id'];
      }

      return $data;
    }

    /**
     * Get item_position_types
     * @return  array|bool  The query result
     */
    public function get_item_position_types($this_container)
    {
      $data = array();
      $this->repo_storage_controller->setContainer($this_container);
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'item_position_type',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        // $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $label = $value['label'];
        $data[$label] = $value['item_position_type_repository_id'];
      }

      return $data;
    }

    /**
     * Get focus_types
     * @return  array|bool  The query result
     */
    public function get_focus_types($this_container)
    {
      $data = array();
      $this->repo_storage_controller->setContainer($this_container);
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'focus_type',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        // $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $label = $value['label'];
        $data[$label] = $value['focus_type_repository_id'];
      }

      return $data;
    }

    /**
     * Get light_source_types
     * @return  array|bool  The query result
     */
    public function get_light_source_types($this_container)
    {
      $data = array();
      $this->repo_storage_controller->setContainer($this_container);
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'light_source_type',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        // $label = $this->u->removeUnderscoresTitleCase($value['label']);
        $label = $value['label'];
        $data[$label] = $value['light_source_type_repository_id'];
      }

      return $data;
    }

    /**
     * Get background_removal_methods
     * @return  array|bool  The query result
     */
    public function get_background_removal_methods($this_container)
    {
      $data = array();
      $this->repo_storage_controller->setContainer($this_container);
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'background_removal_method',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        $label = $value['label'];
        $data[$label] = $value['background_removal_method_repository_id'];
      }

      return $data;
    }

    /**
     * Get camera_cluster_types
     * @return  array|bool  The query result
     */
    public function get_camera_cluster_types($this_container)
    {
      $data = array();
      $this->repo_storage_controller->setContainer($this_container);
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'camera_cluster_type',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        $label = $value['label'];
        $data[$label] = $value['camera_cluster_type_repository_id'];
      }

      return $data;
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
     * Delete Multiple Datasets
     *
     * @Route("/admin/projects/datasets/{project_repository_id}/{subject_repository_id}/{item_repository_id}/delete", name="datasets_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_datasets(Request $request)
    {
        $ids = $request->query->get('ids');
        $project_repository_id = !empty($request->attributes->get('project_repository_id')) ? $request->attributes->get('project_repository_id') : false;
        $subject_repository_id = !empty($request->attributes->get('subject_repository_id')) ? $request->attributes->get('subject_repository_id') : false;
        $item_repository_id = !empty($request->attributes->get('item_repository_id')) ? $request->attributes->get('item_repository_id') : false;

        if(!empty($ids) && $project_repository_id && $subject_repository_id && $item_repository_id) {

          $ids_array = explode(',', $ids);

          $this->repo_storage_controller->setContainer($this->container);
          foreach ($ids_array as $key => $id) {
            $ret = $this->repo_storage_controller->execute('markCaptureDatasetInactive', array(
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
            ));
          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('datasets_browse', array('project_repository_id' => $project_repository_id, 'subject_repository_id' => $subject_repository_id, 'item_repository_id' => $item_repository_id));
    }

}
