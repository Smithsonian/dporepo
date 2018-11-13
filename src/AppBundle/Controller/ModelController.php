<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\RepoStorageHybridController;
use PDO;

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
    private $repo_storage_controller;
    private $uploads_path;

    /**
     * Constructor
     * @param object  $u  Utility functions object
     */
    public function __construct(AppUtilities $u, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->uploads_path = '/uploads/repository';
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
     * @param Connection $conn
     * @param Request $request
     */
    public function modelDetail(Connection $conn, Request $request,$id)
    {
        $model =[];
         //$Model = new Model;
        if ($id !== null) {
          $model = $this->repo_storage_controller->execute('getModelDetail', array(
            'model_repository_id' => $id));
        }
        
         return $this->render('datasets/model_detail.html.twig', array(
             'page_title' => "Model Detail",
             'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),"modeldetail"=>$model,
         ));
    }
    /**
   * @Route("/admin/projects/{type}/{id}/viewer", name="voyager_viewer", methods="GET", defaults={"id" = null,"type"=null})
   * @Route("/admin/projects/{type}/{id}/viewer/{file_id}", name="voyager_viewer", methods="GET", defaults={"id" = null,"type"=null,"file_id"=null})
   * @param Connection $conn
   * @param Request $request
   */
  public function voyagerViewer(Connection $conn, Request $request,$id,$type,$file_id)
  {
    $file = $files = [];
    if ($id == null || $type == null) {
      return $this->redirectToRoute('admin_home');
    }
    if ($file_id != null) {
      $file = $this->repo_storage_controller->execute('getFile',array("file_id"=>$file_id));
    }else{
      $files = $this->repo_storage_controller->execute('getFiles',array("parent_record_id"=>$id,"parent_record_type"=>$type,"limit"=>1));
    }
    
    if (count($files) == 0 && count($file) == 0) {
      return $this->redirectToRoute('admin_home');
    }
    return $this->render('datasets/model_viewer.html.twig', array(
      'page_title' => "Model Viewer",
      'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      "files" => ($file_id == null) ? $files : $file
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
          'parent_id' => isset($req['parent_model_id']) ? $req['parent_model_id'] : 0,
          //'parent_id_field' => isset($req['parent_type']) ? 'parent_item_repository_id' : 'parent_capture_dataset_repository_id',
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
