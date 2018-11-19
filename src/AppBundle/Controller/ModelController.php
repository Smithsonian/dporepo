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
     * Constructor
     * @param object  $u  Utility functions object
     */
    public function __construct(AppUtilities $u, KernelInterface $kernel, string $uploads_directory, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->kernel = $kernel;
        $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
        $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
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
      $data = $this->repo_storage_controller->execute('getRecord', array(
          'base_table' => 'model',
          'id_field' => 'model_repository_id',
          'id_value' => (int)$id,
        )
      );

      // If there are no results, throw a createNotFoundException (404).
      if (empty($data)) throw $this->createNotFoundException('Model not found (404)');

      $data['capture_dataset'] = array();

      // The repository's upload path.
      $data['uploads_path'] = $this->uploads_directory;

      // Get all of the parent records.
      $record_type = !empty($data['parent_capture_dataset_repository_id']) ? 'model_with_capture_dataset_id' : 'model_with_item_id';
      // Execute.
      $data['parent_records'] = $this->repo_storage_controller->execute('getParentRecords', array(
        'base_record_id' => $id,
        'record_type' => $record_type,
      ));

      // Set the item ID.
      $item_id = $data['parent_records']['item_repository_id'];

      // Example output of getParentRecords:
      // array(4) {
      //   ["project_repository_id"]=>
      //   string(1) "2"
      //   ["subject_repository_id"]=>
      //   string(3) "795"
      //   ["item_repository_id"]=>
      //   string(4) "2570"
      //   ["model_repository_id"]=>
      //   string(1) "9"
      // }

      if (!empty($data['parent_capture_dataset_repository_id'])) {
        // Get the capture dataset record.
        $capture_dataset = $this->repo_storage_controller->execute('getRecord', array(
            'base_table' => 'capture_dataset',
            'id_field' => 'capture_dataset_repository_id',
            'id_value' => $data['parent_capture_dataset_repository_id'],
          )
        );
        // Modify the item ID and set the capture dataset.
        if (!empty($capture_dataset)) {
          $item_id = $capture_dataset['parent_item_repository_id'];
          $data['capture_dataset'] = $capture_dataset;
        }
      }

      // Get the item record.
      $item = $this->repo_storage_controller->execute('getRecord', array(
          'base_table' => 'item',
          'id_field' => 'item_repository_id',
          'id_value' => $item_id,
        )
      );
      
      if (!empty($item)) {
        // Get the subject record.
        $subject = $this->repo_storage_controller->execute('getRecord', array(
            'base_table' => 'subject',
            'id_field' => 'subject_repository_id',
            'id_value' => $item['subject_repository_id'],
          )
        );

        if (!empty($subject)) {
          // Get the project record.
          $project = $this->repo_storage_controller->execute('getRecord', array(
              'base_table' => 'project',
              'id_field' => 'project_repository_id',
              'id_value' => $subject['project_repository_id'],
            )
          );

          $data['subject_name'] = $subject['subject_name'];
          $data['item_description'] = $item['item_description'];
          $data['project_name'] = $project['project_name'];
        }
      }
      
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
   * @param Connection $conn
   * @param Request $request
   */
  public function modelViewer(Connection $conn, Request $request,$id)
  {

    //@todo use incoming model $id to retrieve model assets.
    $model_url = "/lib/javascripts/voyager/assets/f1986_19-mesh-smooth-textured/f1986_19-mesh-smooth-textured-item.json";

    return $this->render('datasets/model_viewer.html.twig', array(
      'page_title' => "Model Viewer",
      'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      "model_url" => $model_url,
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
