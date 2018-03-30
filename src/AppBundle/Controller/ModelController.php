<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use Symfony\Component\DependencyInjection\Container;
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
     * @Route("/admin/projects/model/datatables_browse", name="model_browse_datatables", methods="POST")
     *
     * @param Request $request
     * @return JsonResponse The query result in JSON
     */
    public function datatablesBrowse(Request $request)
    {
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $query_params = array(
          'record_type' => 'model',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
          'parent_id' => $req['parent_id'],
          'parent_id_field' => isset($req['parent_type']) ? 'parent_item_repository_id' : 'parent_capture_dataset_repository_id',
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $this->repo_storage_controller->setContainer($this->container);
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
        $post = $request->request->all();
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        $this->repo_storage_controller->setContainer($this->container);
        if(!empty($id) && empty($post)) {
          $rec = $this->repo_storage_controller->execute('getModel', array(
            'model_repository_id' => $id));
          if(isset($rec)) {
            $data = (object)$rec;
          }
        }
        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Add the parent_id to the $data object
        $data->parent_capture_dataset_repository_id = $parent_id;

        // Back link
        $back_link = $request->headers->get('referer');
        if(isset($data->project_repository_id)) {
            $back_link = "/admin/projects/dataset_elements/{$data->project_repository_id}/{$data->subject_repository_id}/{$data->parent_item_repository_id}/{$data->parent_capture_dataset_repository_id}";
        }
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
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
            'back_link' => $back_link,
        ));
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

            $this->repo_storage_controller->setContainer($this->container);

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
     * /\/\/\/ Route("/admin/projects/model/{id}", name="model_browse", methods="GET", defaults={"id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     */
    // public function browse(Connection $conn, Request $request)
    // {
    //     $Model = new Model;

    //     // Database tables are only created if not present.
    //     $Model->createTable();

    //     return $this->render('datasetElements/model_browse.html.twig', array(
    //         'page_title' => "Browse Model",
    //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
    //     ));
    // }
  
}
