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
     * @Route("/admin/projects/model/datatables_browse", name="model_browse_datatables", methods="POST")
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
        $parent_id_field = isset($req['parent_type']) ? $req['parent_type'] : 'parent_capture_dataset_repository_id';

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

        $data = $this->repo_storage_controller->execute('getDatatable', $query_params);

        return $this->json($data);
    }

    /**
     * Matches /admin/projects/model/manage/*
     *
     * @Route("/admin/projects/model/manage/{parent_id}/{id}", name="model_manage", methods={"GET","POST"}, defaults={"parent_id" = null, "id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    function formView(Connection $conn, Request $request)
    {
        $data = new Model();
      $get = $request->query->all();
      $parent_type = "capture_dataset";

      if(!empty($request->attributes->get('type'))) {
        $parent_type = $request->attributes->get('type');
        if($parent_type !== "item") {
          $parent_type = "capture_dataset";
        }
      }

        $post = $request->request->all();
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        if(!empty($id) && empty($post)) {
          $rec = $this->repo_storage_controller->execute('getModel', array(
            'model_repository_id' => $id));
          if(isset($rec)) {
            $data = (object)$rec;
          }
        }
        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Back link
        $back_link = $request->headers->get('referer');

      // Add the parent_id to the $data object
      if(empty($id)) {
        if($parent_type == "item") {
          $data->parent_item_repository_id = $parent_id;
        }
        else {
          $data->parent_capture_dataset_repository_id = $parent_id;
        }
      }
      else {
        //@todo we need a way to get the backlink for new models, too
        if($parent_type == "item") {
          $back_link = "/admin/projects/datasets/{$data->project_repository_id}/{$data->subject_repository_id}/{$data->parent_item_repository_id}";
        }
        else {
          //@todo- this is problematic since subjects are only linked through items
          // A model might be linked to a capture dataset, which links to project and item; or it may be linked to an item, which links to a project and a subject.
          // We'll need to modify the URL for capture datasets- we shouldn't need the subject repository ID.
          //$back_link = "/admin/projects/dataset_elements/{$data->project_repository_id}/{$data->subject_repository_id}/{$data->parent_item_repository_id}/{$data->parent_capture_dataset_repository_id}";
        }
        }

        // Get data from lookup tables.
        $data->unit_options = $this->get_unit();

        // Create the form
        $form = $this->createForm(ModelForm::class, $data);
        
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();

            // Set the parent_item_repository_id if adding a Model from an Item record.
            if(empty($id) && (!empty($request->query->get('from')) && $request->query->get('from') === 'item')) {
                $data->parent_item_repository_id = $parent_id;
                $data->parent_capture_dataset_repository_id = 0;
            }
            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'model',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => (array)$data,
              'back_link' => $back_link,
            ));

            $this->addFlash('message', 'Record successfully updated.');
            return $this->redirect('/admin/projects/model/manage/' . $parent_id . '/' . $id);
        }

        return $this->render('datasets/model_form.html.twig', array(
            'page_title' => !empty($id) ? 'Model: ' . $data->model_guid : 'Create Model',
            'data' => $data,
            'uploads_path' => $this->uploads_path,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
            'back_link' => $back_link,
        ));
    }

    /**
     * Get Unit
     * @return  array|bool  The query result
     */
    public function get_unit()
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
        $data[$label] = $value['unit_repository_id'];
      }

      return $data;
    }

    /**
     * @Route("/admin/projects/model/delete", name="model_remove_records", methods={"GET"})
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
     * @Route("/admin/projects/model/{id}/detail", name="model_detail", methods="GET", defaults={"id" = null})
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
        'model_repository_id' => $id));

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
   * @Route("/admin/projects/model/{id}/viewer", name="model_viewer", methods="GET", defaults={"id" = null})
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
        'model_repository_id' => $id));

      // If there are no results, throw a createNotFoundException (404).
      //if (empty($data) || empty($data['viewable_model'])) throw $this->createNotFoundException('Model not found (404)');
      if (empty($data)) throw $this->createNotFoundException('Model not found (404)');

      // $this->u->dumper($data);

      //@todo in the future perhaps this should be an array of all files
      // Replace local path with Drastic path. Twig template will serve the file using admin/get_file?path=blah
      $uploads_path = str_replace('web', '', $this->uploads_directory);
      // Windows fix for the file path.
      $uploads_path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $uploads_path) : $uploads_path;
      // Final model URL.
      $model_url = str_replace($uploads_path, $this->external_file_storage_path, $data['viewable_model']['file_path']);
    }
              
    $data['model_url'] = $model_url;

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
    public function browse_model_files(Connection $conn, Request $request)
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
     * @Route("/admin/model/datatables_browse_derivative_models", name="datatables_browse_derivative_models", methods="POST")
     *
     * @param Connection $conn
     * @param Request $request
     */
    public function browse_derivative_models(Connection $conn, Request $request)
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

        $data = $this->repo_storage_controller->execute('getDatatable', $query_params);

        return $this->json($data);
        /*
      $parent_id = $request->request->get("parent_id");
      $models = $conn->fetchAll("SELECT * FROM model WHERE parent_model_id=$parent_id");
      return new JsonResponse($models);
      */
    }
}
