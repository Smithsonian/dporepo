<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\RepoStorageHybridController;
use Symfony\Component\HttpKernel\KernelInterface;

use AppBundle\Form\ModelForm;
use AppBundle\Entity\Model;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoUserAccess;

class ModelController extends Controller
{
    /**
     * @var object $u
     */
    public $u;

    /**
     * @var object $repo_storage_controller
     */
    private $repo_storage_controller;

    /**
     * @var object $kernel
     */
    public $kernel;

    /**
     * @var string $project_directory
     */
    private $project_directory;

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
    public function __construct(AppUtilities $u, KernelInterface $kernel, string $uploads_directory, string $external_file_storage_path, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->kernel = $kernel;
        $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
        $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
        $this->external_file_storage_path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $external_file_storage_path) : $external_file_storage_path;;
    }

    /**
     * @Route("/admin/datatables_browse_models", name="model_browse_datatables", methods="POST")
     *
     * @param Request $request
     * @return JsonResponse The query result in JSON
     */
    public function datatablesBrowse(Request $request)
    {
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = isset($req['columns']) && isset($req['order'][0]['column']['data']) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : '';
        $sort_order = isset($req['order'][0]['dir']) ? $req['order'][0]['dir'] : 'asc';
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        $parent_id = !empty($req['parent_id']) ? $req['parent_id'] : 0;
        $parent_id_field = isset($req['parent_type']) ? $req['parent_type'] : 'capture_dataset_id';

        $query_params = array(
          'record_type' => 'model',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
          'parent_id' => $parent_id,
          'parent_id_field' => $parent_id_field,
        );

        if ($search) {
          $query_params['search_value'] = $search;
        }

        $data = $this->repo_storage_controller->execute('getDatatableModels', $query_params);

        return $this->json($data);
    }

    /**
     * Matches /admin/model/manage/*
     *
     * @Route("/admin/model/add/{parent_id}", name="model_add", methods={"GET","POST"}, defaults={"id" = null})
     * @Route("/admin/model/manage/{id}", name="model_manage", methods={"GET","POST"})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    function formView(Connection $conn, Request $request, CaptureDatasetController $dataset)
    {
        $data = new Model();
        $parent_type = null;
        $item_id = NULL;

        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;

        if(false != $parent_id) {
          $parent_type = $request->attributes->get('parent_type');
          if(empty($parent_type)) {
            $parent_type = !empty($request->query->get('parent_type')) ? $request->query->get('parent_type') : false;
          }
          if($parent_type != "item_id") {
            $parent_type = "capture_dataset_id";
          }
        }

        $post = $request->request->all();

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id && !$id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        if(!empty($id) && empty($post)) {
          $rec = $this->repo_storage_controller->execute('getModel', array(
            'model_id' => $id));
          if(isset($rec)) {
            $data = (object)$rec;
          }
        }
        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Back link
        $back_link = $request->headers->get('referer');

        // Add the parent_id to the $data object
        if(empty($id)) {
          if($parent_type == "item_id" && !empty($parent_id)) {
            $data->item_id = $parent_id;
          }
          else {
            $data->capture_dataset_id = $parent_id;

            $dataset_data = $dataset->getDataset((int)$parent_id);
            $data->item_id = $dataset_data['item_id'];
          }
        }
        else {
          //@todo we need a way to get the backlink for new models, too
          if($parent_type == "item_id") {
            $back_link = "/admin/capture_datasets/{$data->item_id}";
          }
          else {
            //@todo- this is problematic since subjects are only linked through items
            // A model might be linked to a capture dataset, which links to project and item; or it may be linked to an item, which links to a project and a subject.
            // We'll need to modify the URL for capture datasets- we shouldn't need the subject repository ID.
            //$back_link = "/admin/projects/dataset_elements/{$data->project_id}/{$data->subject_id}/{$data->item_id}/{$data->capture_dataset_id}";
          }
        }

        // Get data from lookup tables.
        $data->unit_options = $this->getUnit();

        // Create the form
        $form = $this->createForm(ModelForm::class, $data);
        
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            // Set the item_id if adding a Model from an Item record.
            if(empty($id) && (!empty($request->query->get('parent_type')) && $request->query->get('parent_type') === 'item_id')) {
                $data->item_id = $parent_id;
                $data->capture_dataset_id = 0;
            }
            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'model',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => (array)$data,
              'back_link' => $back_link,
            ));

            $this->addFlash('message', 'Record successfully updated.');
            return $this->redirect('/admin/model/view/' . $id);
        }

        return $this->render('datasets/model_form.html.twig', array(
            'page_title' => !empty($id) ? 'Model: ' . $data->model_guid : 'Create Model',
            'data' => $data,
            'uploads_path' => $this->uploads_directory,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
            'back_link' => $back_link,
        ));
    }

    /**
     * Get Unit
     * @return  array|bool  The query result
     */
    public function getUnit()
    {
      $data = array();
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'unit',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        $label = $value['label'];
        $data[$label] = $value['unit_id'];
      }

      return $data;
    }

    /**
     * Get Model Purpose
     * @return  array|bool  The query result
     */
    public function getModelPurpose()
    {
      $data = array();
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'model_purpose',
          'sort_fields' => array(
            0 => array('field_name' => 'model_purpose')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        $label = $value['model_purpose'];
        $data[$label] = $value['model_purpose_id'];
      }

      return $data;
    }

    /**
     * @Route("/admin/model/delete", name="model_remove_records", methods={"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    public function deleteMultiple(Request $request)
    {
        if(!empty($request->query->get('ids'))) {

            // Create the array of ids.
            $ids_array = explode(',', $request->query->get('ids'));

            // Loop thorough the ids.
            foreach ($ids_array as $key => $id) {
              // Run the query against a single record.
              $ret = $this->repo_storage_controller->execute('markRecordInactive', array(
                'record_type' => 'model',
                'record_id' => $id,
                'user_id' => $this->getUser()->getId(),
              ));
            }

            $this->addFlash('message', 'Records successfully removed.');

        } else {
            $this->addFlash('message', 'Missing data. No records removed.');
        }

        $referer = $request->headers->get('referer');
        return $this->redirect($referer);
    }

    /**
     * @Route("/admin/model/view/{id}", name="model_detail", methods="GET", defaults={"id" = null})
     *
     * @param $id The model ID
     * @param Connection $conn
     * @param Request $request
     */
    public function modelDetail($id = null, Connection $conn, Request $request)
      {
      $data = array();

      if (!empty($id)) {

        // Get the model record.
        $data = $this->repo_storage_controller->execute('getModel', array(
          'model_id' => $id));

        // If there are no results, throw a createNotFoundException (404).
        if (empty($data)) throw $this->createNotFoundException('Model not found (404)');

        // The repository's upload path.
        $data['uploads_path'] = $this->uploads_directory;

      }
      // $this->u->dumper($data);

      return $this->render('datasets/model_detail.html.twig', array(
        'page_title' => 'Model Detail',
        'data' => $data,
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      ));
    }

    /**
     * @Route("/admin/model/viewer/{id}", name="model_viewer", methods="GET", defaults={"id" = null})
     *
     * @param $id The model ID
     * @param Connection $conn
     * @param Request $request
     */
    public function modelViewer($id = null, Connection $conn, Request $request)
    {
      //@TODO use incoming model $id to retrieve model assets.
      // $model_url = "/lib/javascripts/voyager/assets/f1986_19-mesh-smooth-textured/f1986_19-mesh-smooth-textured-item.json";

      $data = array();
      $model_url = NULL;

      if (!empty($id)) {

        // Get the model record.
        $data = $this->repo_storage_controller->execute('getModel', array(
          'model_id' => $id));

        // If there are no results, throw a createNotFoundException (404).
        //if (empty($data) || empty($data['viewable_model'])) throw $this->createNotFoundException('Model not found (404)');
        if (empty($data)) throw $this->createNotFoundException('Model not found (404)');

        // $this->u->dumper($data);

        // If the file_path key doesn't exist, throw a createNotFoundException (404).
        if (!array_key_exists('file_path', $data['viewable_model'])) throw $this->createNotFoundException('Model not found (404)');

        $model_url = $data['viewable_model']['file_path'];
      }

      $data['model_url'] = $model_url;

      // Twig template will serve the file using admin/get_file?path=blah
      // The get_file function will figure out pathing, we can pass it all kinds of nonsense.
      return $this->render('datasets/model_viewer.html.twig', array(
        'page_title' => 'Model Viewer',
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        'data' => $data,
      ));
    }

    /**
     * @Route("/admin/model/files/datatables_browse", name="datatables_browse_files", methods={"POST","GET"})
     *
     * @param Connection $conn
     * @param Request $request
     */
    public function browseModelFiles(Connection $conn, Request $request)
    {
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = isset($req['columns']) && isset($req['order'][0]['column']['data']) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : '';
        $sort_order = isset($req['order'][0]['dir']) ? $req['order'][0]['dir'] : 'asc';
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        $parent_id = !empty($req['parent_id']) ? $req['parent_id'] : 0;

        $query_params = array(
          'record_type' => 'model_file',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
          'parent_id' => $parent_id,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $data = $this->repo_storage_controller->execute('getDatatable', $query_params);

        return $this->json($data);

    }
    /**
     * @Route("/admin/datatables_browse_derivative_models", name="datatables_browse_derivative_models", methods="POST")
     *
     * @param Connection $conn
     * @param Request $request
     */
    public function browseDerivativeModels(Connection $conn, Request $request)
    {
      $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = isset($req['columns']) && isset($req['order'][0]) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : '';
        $sort_order = isset($req['order'][0]['dir']) ? $req['order'][0]['dir'] : 'asc';
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $query_params = array(
          'record_type' => 'model',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
          'parent_id' => isset($req['parent_id']) ? $req['parent_id'] : 0,
          'parent_id_field' => 'parent_model_id',
        );

        if ($search) {
          $query_params['search_value'] = $search;
        }

        $data = $this->repo_storage_controller->execute('getDatatableModels', $query_params);

        return $this->json($data);
        /*
      $parent_id = $request->request->get("parent_id");
      $models = $conn->fetchAll("SELECT * FROM model WHERE parent_model_id=$parent_id");
      return new JsonResponse($models);
      */
    }
}
