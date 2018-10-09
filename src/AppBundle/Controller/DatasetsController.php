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

use AppBundle\Form\Dataset;
use AppBundle\Entity\Datasets;
use AppBundle\Entity\Items;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoUserAccess;

class DatasetsController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;
    private $repo_user_access;

    /**
     * @var string
     */
    private $uploads_path;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, string $file_upload_path, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->repo_user_access = new RepoUserAccess($conn);
        $this->uploads_path = str_replace('web', '', $file_upload_path);
    }

    /**
     * @Route("/admin/projects/datasets/{project_repository_id}/{subject_repository_id}/{item_repository_id}", name="datasets_browse", methods="GET")
     */
    public function browse_datasets(Connection $conn, Request $request, ProjectsController $projects, SubjectsController $subjects)
    {
        // Database tables are only created if not present.
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
        $subject_data = $subjects->get_subject((int)$subject_repository_id);

        // Truncate the item_description so the breadcrumb don't blow up.
        $more_indicator = (strlen($item->item_data->item_description) > 50) ? '...' : '';
        $item->item_data->item_description_truncated = substr($item->item_data->item_description, 0, 50) . $more_indicator;

        return $this->render('datasets/browse_datasets.html.twig', array(
            'page_title' => isset($item->item_data->item_description_truncated) ? 'Item: ' . $item->item_data->item_description_truncated : 'Item',
            'project_repository_id' => $project_repository_id,
            'subject_repository_id' => $subject_repository_id,
            'item_repository_id' => $item_repository_id,
            'project_data' => $project_data,
            'subject_data' => $subject_data,
            'item_data' => $item->item_data,
            'destination' => $project_repository_id . '|' . $subject_repository_id . '|' . $item_repository_id,
            'uploads_path' => $this->uploads_path,
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
        $dataset->capture_methods_lookup_options = $this->get_capture_methods();
        $dataset->dataset_types_lookup_options = $this->get_dataset_types();
        $dataset->item_position_types_lookup_options = $this->get_item_position_types();
        $dataset->focus_types_lookup_options = $this->get_focus_types();
        $dataset->light_source_types_lookup_options = $this->get_light_source_types();
        $dataset->background_removal_methods_lookup_options = $this->get_background_removal_methods();
        $dataset->camera_cluster_types_lookup_options = $this->get_camera_cluster_types();
        $dataset->calibration_object_type_options = $this->get_calibration_object_types();

        // Create the form
        $form = $this->createForm(Dataset::class, $dataset);
        // Handle the request
        $form->handleRequest($request);

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
        
        $dataset->capture_dataset_repository_id = !empty($id) ? $id : false;

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
        $datasets = $this->get_datasets($item_repository_id);

        foreach ($datasets as $key => $value) {

            // Check for child dataset records so the 'children' key can be set accordingly.
            $dataset_elements_data = $dataset_elements->get_dataset_elements((int)$value['capture_dataset_repository_id']);
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
    public function get_dataset($capture_dataset_repository_id = false)
    {
      $query_params = array(
        'capture_dataset_repository_id' => $capture_dataset_repository_id,
      );
      $data = $this->repo_storage_controller->execute('getCaptureDataset', $query_params);
      return $data;
    }

    /**
     * Get capture_methods
     * @return  array|bool  The query result
     */
    public function get_capture_methods()
    {
      $data = array();
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
    public function get_dataset_types()
    {
      $data = array();
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
    public function get_item_position_types()
    {
      $data = array();
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
    public function get_focus_types()
    {
      $data = array();
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
    public function get_light_source_types()
    {
      $data = array();
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
    public function get_background_removal_methods()
    {
      $data = array();
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
    public function get_camera_cluster_types()
    {
      $data = array();
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
