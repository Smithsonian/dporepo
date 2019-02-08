<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpKernel\KernelInterface;

use AppBundle\Controller\RepoStorageHybridController;
use PDO;

use AppBundle\Form\CaptureDatasetForm;
use AppBundle\Entity\CaptureDataset;
use AppBundle\Entity\Item;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoUserAccess;

class CaptureDatasetController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;
    private $repo_user_access;

    /**
     * @var string $uploads_directory
     */
    private $uploads_directory;
    // private $uploads_path;

    /**
     * @var string $external_file_storage_path
     */
    private $external_file_storage_path;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, string $uploads_directory, string $external_file_storage_path, Connection $conn, KernelInterface $kernel)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->repo_user_access = new RepoUserAccess($conn);
        $this->kernel = $kernel;
        $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
        $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
        $this->external_file_storage_path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $external_file_storage_path) : $external_file_storage_path;;
    }

    /**
     * @Route("/admin/datatables_browse_datasets/{item_id}", name="datasets_browse_datatables", methods={"POST","GET"})
     *
     * Browse datasets
     *
     * Run a query to retrieve all datasets in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatablesBrowseDatasets(Request $request)
    {
        $req = $request->request->all();
        $item_id = !empty($request->attributes->get('item_id')) ? $request->attributes->get('item_id') : false;

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
          'item_id' => $item_id,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $data = $this->repo_storage_controller->execute('getDatatableCaptureDataset', $query_params);
        if (is_array($data) && isset($data['aaData'])) {
          //@todo bad practice- instead get the dataset files in the above function, getDatatableCaptureDataset.
          foreach($data['aaData'] as $k => $row) {
            $id = $row['manage'];

            $dataset_file = $this->repo_storage_controller->execute('getDatasetFiles',
              array(
                'capture_dataset_id' => $id,
                'limit' => 1)
            );
            $data['aaData'][$k]['file_path'] = '';
            if (count($dataset_file) > 0) {
              $path = 'web' . str_replace("\\", "/",  $dataset_file[0]['file_path']);
              $path = str_replace($this->uploads_directory, '', $path);
              $path = str_replace("\\", "/", $path);
              $path = str_replace("//", "/", $path);
              // The complete path should look like this:
              // 1E155C38-DC69-E33B-4208-7757D5CDAA35/data/cc/camera/f1978_40-cc_j3a.JPG
              $data['aaData'][$k]['file_path'] = $path;
            }
          }
        }

        return $this->json($data);
    }

    /**
     * @Route("/admin/datatables_browse_model_datasets/{model_id}", name="datasets_browse_model_datatables", methods={"POST","GET"})
     *
     * Browse datasets that were used to construct a specific model.
     *
     * Run a query to retrieve all datasets in the database, for the specified model_id.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatablesBrowseModelDatasets(Request $request)
    {
      $req = $request->request->all();
      $model_id = !empty($request->attributes->get('model_id')) ? $request->attributes->get('model_id') : false;

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
        'model_id' => $model_id,
      );
      if ($search) {
        $query_params['search_value'] = $search;
      }

      $data = $this->repo_storage_controller->execute('getDatatableCaptureDataset', $query_params);

      if (is_array($data) && isset($data['aaData'])) {
        //@todo bad practice- instead get the dataset files in the above function, getDatatableCaptureDataset.
        foreach($data['aaData'] as $k => $row) {
          $id = $row['manage'];

          $dataset_file = $this->repo_storage_controller->execute('getDatasetFiles',
            array(
              'capture_dataset_id' => $id,
              'limit' => 1)
          );
          $data['aaData'][$k]['file_path'] = '';
          if (count($dataset_file) > 0) {
            $path = 'web' . str_replace("\\", "/",  $dataset_file[0]['file_path']);
            $path = str_replace($this->uploads_directory, '', $path);
            $path = str_replace("\\", "/", $this->external_file_storage_path . $path);
            $path = str_replace("//", "/", $path);
            // The complete path should look like this:
            // /3DRepo/uploads/1E155C38-DC69-E33B-4208-7757D5CDAA35/data/cc/camera/f1978_40-cc_j3a.JPG
            //$model_url = str_replace($uploads_path, $this->external_file_storage_path, $data['viewable_model']['file_path']);
            $data['aaData'][$k]['file_path'] = $path;
          }
        }
      }

      return $this->json($data);
    }

  /**
     * Matches /admin/capture_dataset/*
     *
     * @Route("/admin/capture_dataset/add/{item_id}", name="dataset_add", methods={"GET","POST"}, defaults={"capture_dataset_id" = null})
     * @Route("/admin/capture_dataset/manage/{capture_dataset_id}", name="datasets_manage", methods={"GET","POST"})
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function showDatasetsForm( Connection $conn, Request $request )
    {
        $dataset = new CaptureDataset();
        $dataset->access_model_purpose = NULL;
        $dataset->inherit_publication_default = '';

        $post = $request->request->all();
        $id = false;
        $ajax = false;

        if (!empty($request->attributes->get('capture_dataset_id'))) {
          if ($request->attributes->get('capture_dataset_id') !== 'ajax') {
            $id = $request->attributes->get('capture_dataset_id');
          }
          else {
            $ajax = true;
          }
        }

        $item_id = !empty($request->attributes->get('item_id')) ? $request->attributes->get('item_id') : false;

        // Retrieve data from the database.
        if (!empty($id) && empty($post)) {
            $dataset_array = $this->repo_storage_controller->execute('getCaptureDataset', array(
              'capture_dataset_id' => $id,
            ));
            if(is_array($dataset_array)) {
              $dataset = (object)$dataset_array;
            }

            $dataset->access_model_purpose = NULL;
            $dataset->inherit_publication_default = '';
            $dataset->api_publication_picker = NULL;
            $picker_val = (string)$dataset->api_published;
            $picker_val .= (string)$dataset->api_discoverable;
            $dataset->api_publication_picker = $picker_val;

            $dataset->model_purpose_picker = $dataset->access_model_purpose;

            $tmp = '';
            if(NULL == $dataset->inherit_api_published) {
              $tmp = "Publication not set, ";
            }
            elseif($dataset->inherit_api_published == 1) {
              $tmp = "Published, ";
            }
            elseif($dataset->inherit_api_published == 0) {
              $tmp = "Not Published, ";
            }
            if(NULL == $dataset->inherit_api_discoverable) {
              $tmp .= "Discoverable not set";
            }
            elseif($dataset->inherit_api_discoverable == 1) {
              $tmp .= "Discoverable";
            }
            elseif($dataset->inherit_api_discoverable == 0) {
              $tmp .= "Not Discoverable";
            }
            $dataset->inherit_publication_default = $tmp;

        }
        elseif(empty($id)) {
          $dataset->item_id = $item_id;
        }

        // Get data from lookup tables.
        $dataset->capture_methods_lookup_options = $this->getCaptureMethods();
        $dataset->dataset_types_lookup_options = $this->getDatasetTypes();
        $dataset->item_position_types_lookup_options = $this->getItemPositionTypes();
        $dataset->focus_types_lookup_options = $this->getFocusTypes();
        $dataset->light_source_types_lookup_options = $this->getLightSourceTypes();
        $dataset->background_removal_methods_lookup_options = $this->getBackgroundRemovalMethods();
        $dataset->camera_cluster_types_lookup_options = $this->getCameraClusterTypes();
        $dataset->calibration_object_type_options = $this->getCalibrationObjectTypes();

        $dataset->api_publication_options = array(
          'Published, Discoverable' => '11',
          'Published, Not Discoverable' => '10',
          'Not Published' => '00',
        );
        $model_purpose_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_purpose',
          'value_field' => 'model_purpose_description',
          'id_field' => 'model_purpose_id',
        ));
        $model_face_count_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_face_count',
          'value_field' => 'model_face_count',
          'id_field' => 'model_face_count_id',
        ));
        $uv_map_size_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'uv_map_size',
          'value_field' => 'uv_map_size',
          'id_field' => 'uv_map_size_id',
        ));

        $dataset->model_face_count_options = $model_face_count_options;
        $dataset->uv_map_size_options = $uv_map_size_options;
        $dataset->model_purpose_options = $model_purpose_options;


        // Create the form
        $form = $this->createForm(CaptureDatasetForm::class, $dataset);
        // Handle the request
        $form->handleRequest($request);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $dataset = $form->getData();

            if(isset($dataset->api_publication_picker)) {
              if($dataset->api_publication_picker == '11') {
                $dataset->api_published = 1;
                $dataset->api_discoverable = 1;
              }
              elseif($dataset->api_publication_picker == '10') {
                $dataset->api_published = 1;
                $dataset->api_discoverable = 0;
              }
              elseif($dataset->api_publication_picker == '00') {
                $dataset->api_published = 0;
                $dataset->api_discoverable = 0;
              }
            }
            else {
              $dataset->api_published = NULL;
              $dataset->api_discoverable = NULL;
            }


            $dataset_array = (array)$dataset;
            //$dataset_array['item_id'] = $dataset_array['item_id'];

            $id = $this->repo_storage_controller->execute('saveCaptureDataset', array(
              'base_table' => 'capture_dataset',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => $dataset_array
            ));

            if ($ajax) {
              // Return the ID of the new record.
              $response = new JsonResponse(array('id' => $id));
              return $response;
            } else {
              $this->addFlash('message', 'Capture Dataset successfully updated.');
              return $this->redirect('/admin/capture_dataset/view/' . $id);
            }
        }

        $dataset->capture_dataset_id = !empty($id) ? $id : false;

        if ($ajax) {
          $response = new JsonResponse($dataset);
          return $response;
        } else {
          return $this->render('datasets/dataset_form_page.html.twig', array(
              'page_title' => !empty($id) ? 'Dataset: ' . $dataset->capture_dataset_name : 'Create Dataset',
              'dataset_data' => $dataset,
              'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
              'form' => $form->createView(),
          ));
        }

    }

    /**
     * @Route("/admin/capture_dataset/view/{capture_dataset_id}", name="dataset_elements_browse", methods="GET")
     */
    public function browseDatasetElements(Connection $conn, Request $request, ItemController $item)
    {
      $id = !empty($request->attributes->get('capture_dataset_id')) ? $request->attributes->get('capture_dataset_id') : false;

      // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
      $dataset_data = $this->getDataset((int)$id);
      if(!$dataset_data) throw $this->createNotFoundException('The record does not exist');

      $item_id = $dataset_data['item_id'];
      $item_data = $item->getItem((int)$item_id);

      $project_id = $item_data['project_id'];
      $project_data = $this->repo_storage_controller->execute('getProject', array('project_id' => $project_id));

      // Truncate the item_description.
      $more_indicator = (strlen($item_data['item_description']) > 50) ? '...' : '';
      $item_data['item_description_truncated'] = substr($item_data['item_description'], 0, 50) . $more_indicator;

      //@todo- get rid of this, thumbs should be included with elements
      $dataset_files = $this->repo_storage_controller->execute('getDatasetFiles',
        array(
          'limit' => 10,
          'capture_dataset_id' => $id //@todo
        )
      );

      return $this->render('datasetElements/browse_dataset_elements.html.twig', array(
        'page_title' => 'Capture Dataset: ' .  $dataset_data['capture_dataset_name'],
        'project_id' => $project_id,
        'item_id' => $item_id,
        'capture_dataset_id' => $id,
        'project_data' => $project_data,
        'item_data' => $item_data,
        'dataset_data' => $dataset_data,
        'dataset_files' => $dataset_files,
        'uploads_path' => $this->uploads_directory,
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      ));
    }


  /**
     * Get Datasets
     *
     * Get datasets from the database.
     *
     * @param       int $item_id    The item ID
     * @return      array|bool       The query result
     */
    public function getDatasets($item_id = false)
    {

      $query_params = array(
        'item_id' => $item_id,
      );
      $data = $this->repo_storage_controller->execute('getDatasets', $query_params);

      return $data;

    }

    /**
     * Get Datasets (for the tree browser)
     *
     * @Route("/admin/projects/get_datasets/{item_id}", name="get_datasets_tree_browser", methods="GET")
     */
    public function getDatasetsTreeBrowser(Request $request, CaptureDatasetElementController $dataset_elements)
    {      
        $item_id = !empty($request->attributes->get('item_id')) ? $request->attributes->get('item_id') : false;
        $datasets = $this->getDatasets($item_id);

        foreach ($datasets as $key => $value) {

            // Check for child dataset records so the 'children' key can be set accordingly.
            $dataset_elements_data = $dataset_elements->getDatasetElements((int)$value['capture_dataset_id']);
            $data[$key] = array(
                'id' => 'datasetId-' . $value['capture_dataset_id'],
                'children' => count($dataset_elements_data) ? true : false,
                'text' => $value['capture_dataset_name'],
                'a_attr' => array('href' => '/admin/projects/dataset_elements/' . $value['project_id'] . '/' . $value['subject_id'] . '/' . $value['item_id'] . '/' . $value['capture_dataset_id']),
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
     * @param       int $capture_dataset_id    The data value
     * @return      array|bool              The query result
     */
    public function getDataset($capture_dataset_id = false)
    {
      $query_params = array(
        'capture_dataset_id' => $capture_dataset_id,
      );
      $data = $this->repo_storage_controller->execute('getCaptureDataset', $query_params);
      return $data;
    }

    /**
     * Get capture_methods
     * @return  array|bool  The query result
     */
    public function getCaptureMethods()
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
        $data[$label] = $value['capture_method_id'];
      }

      return $data;
    }

    /**
     * Get dataset_types
     * @return  array|bool  The query result
     */
    public function getDatasetTypes()
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
        $data[$label] = $value['dataset_type_id'];
      }

      return $data;
    }

    /**
     * Get item_position_types
     * @return  array|bool  The query result
     */
    public function getItemPositionTypes()
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
        $data[$label] = $value['item_position_type_id'];
      }

      return $data;
    }

    /**
     * Get focus_types
     * @return  array|bool  The query result
     */
    public function getFocusTypes()
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
        $data[$label] = $value['focus_type_id'];
      }

      return $data;
    }

    /**
     * Get light_source_types
     * @return  array|bool  The query result
     */
    public function getLightSourceTypes()
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
        $data[$label] = $value['light_source_type_id'];
      }

      return $data;
    }

    /**
     * Get background_removal_methods
     * @return  array|bool  The query result
     */
    public function getBackgroundRemovalMethods()
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
        $data[$label] = $value['background_removal_method_id'];
      }

      return $data;
    }

    /**
     * Get camera_cluster_types
     * @return  array|bool  The query result
     */
    public function getCameraClusterTypes()
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
        $data[$label] = $value['camera_cluster_type_id'];
      }

      return $data;
    }

    /**
     * Get calibration_object_types
     * @return  array|bool  The query result
     */
    public function getCalibrationObjectTypes()
    {
        $data = array();

        $records = $this->repo_storage_controller->execute('getRecords',
          array(
            'base_table' => 'calibration_object_type',
          )
        );
        foreach ($records as $key => $value) {
            $label = $this->u->removeUnderscoresTitleCase($value['label']);
            $data[$label] = $value['calibration_object_type_id'];
        }

        return $data;
    }

    /**
     * Delete Multiple Datasets
     *
     * @Route("/admin/capture_dataset/delete/{item_id}", name="datasets_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $request  Request object
     * @return  void
     */
    public function deleteMultipleDatasets(Request $request)
    {
        $ids = $request->query->get('ids');
        $item_id = !empty($request->attributes->get('item_id')) ? $request->attributes->get('item_id') : false;

        if(!empty($ids) && $item_id) {

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

        return $this->redirectToRoute('item_view', array('item_id' => $item_id));
    }

}
