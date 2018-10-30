<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use PDO;

use AppBundle\Form\PhotogrammetryScaleBarForm;
use AppBundle\Entity\PhotogrammetryScaleBar;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoUserAccess;

class PhotogrammetryScaleBarController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;
    private $repo_user_access;

    /**
     * Constructor
     * @param object  $u  Utility functions object
     */
    public function __construct(AppUtilities $u, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->repo_user_access = new RepoUserAccess($conn);
    }

    /**
     * @Route("/admin/projects/photogrammetry_scale_bar/datatables_browse", name="photogrammetry_scale_bar_browse_datatables", methods="POST")
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
        'record_type' => 'photogrammetry_scale_bar',
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'start_record' => $start_record,
        'stop_record' => $stop_record,
        'parent_id' => $req['parent_id']
      );
      if ($search) {
        $query_params['search_value'] = $search;
      }

      
      $data = $this->repo_storage_controller->execute('getDatatable', $query_params);

        return $this->json($data);
    }

    /**
     * Matches /admin/projects/photogrammetry_scale_bar/manage/*
     *
     * @Route("/admin/projects/photogrammetry_scale_bar/manage/{parent_id}/{id}", name="photogrammetry_scale_bar_manage", methods={"GET","POST"}, defaults={"parent_id" = null, "id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    function formView(Connection $conn, Request $request)
    {
      $username = $this->getUser()->getUsernameCanonical();
      $access = $this->repo_user_access->get_user_access_any($username, 'create_edit_lookups');

      if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
      }

        $data = new PhotogrammetryScaleBar();
        $post = $request->request->all();
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        
        if(!empty($id) && empty($post)) {
          $rec = $this->repo_storage_controller->execute('getPhotogrammetryScaleBar', array(
            'photogrammetry_scale_bar_repository_id' => $id));
          if(isset($rec)) {
            $data = (object)$rec;
          }
          $data->photogrammetry_scale_bar_repository_id = $id;
        }
        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Add the parent_id to the $data object
        $data->parent_capture_dataset_repository_id = $parent_id;

        $back_link = $request->headers->get('referer');
        if(isset($data->project_repository_id)) {
            $back_link = "/admin/projects/dataset_elements/{$data->project_repository_id}/{$data->subject_repository_id}/{$data->parent_item_repository_id}/{$data->parent_capture_dataset_repository_id}";
        }

        // Get data from lookup tables.
        $data->scale_bar_barcode_type_options = $this->get_scale_bar_barcode_type();

        // Create the form
        $form = $this->createForm(PhotogrammetryScaleBarForm::class, (array)$data);
        
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'photogrammetry_scale_bar',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => (array)$data
            ));

            $this->addFlash('message', 'Record successfully updated.');
            return $this->redirect('/admin/projects/photogrammetry_scale_bar/manage/' . $parent_id . '/' . $id);
        }

        return $this->render('datasets/photogrammetry_scale_bar_form.html.twig', array(
            'page_title' => !empty($id) ? 'Photogrammetry Scale Bar: ' . $id : 'Create Photogrammetry Scale Bar',
            'data' => $data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
            'back_link' => $back_link,
        ));
    }

    /**
     * Get Scale Bar Barcode Type
     * @return  array|bool  The query result
     */
    public function get_scale_bar_barcode_type()
    {
      $data = array();
      
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'scale_bar_barcode_type',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        $label = $value['label'];
        $data[$label] = $value['scale_bar_barcode_type_repository_id'];
      }

      return $data;
    }

    /**
     * @Route("/admin/projects/photogrammetry_scale_bar/delete", name="photogrammetry_scale_bar_remove_records", methods={"GET"})
     *
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    public function deleteMultiple(Request $request)
    {
      $username = $this->getUser()->getUsernameCanonical();
      $access = $this->repo_user_access->get_user_access_any($username, 'create_edit_lookups');

      if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
      }

        if(!empty($request->query->get('ids'))) {

            // Create the array of ids.
            $ids_array = explode(',', $request->query->get('ids'));

            

            // Loop thorough the ids.
            foreach ($ids_array as $key => $id) {
              // Run the query against a single record.
              $ret = $this->repo_storage_controller->execute('markRecordInactive', array(
                'record_type' => 'photogrammetry_scale_bar',
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
     * /\/\/\/ Route("/admin/projects/photogrammetry_scale_bar/{id}", name="photogrammetry_scale_bar_browse", methods="GET", defaults={"id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     */
    // public function browse(Connection $conn, Request $request)
    // {
    //     $PhotogrammetryScaleBar = new PhotogrammetryScaleBar;

    //     // Database tables are only created if not present.
    //     $PhotogrammetryScaleBar->createTable();

    //     return $this->render('datasetElements/photogrammetry_scale_bar_browse.html.twig', array(
    //         'page_title' => "Browse Photogrammetry Scale Bar",
    //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
    //     ));
    // }
  
}
