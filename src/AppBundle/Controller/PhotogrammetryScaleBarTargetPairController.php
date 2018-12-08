<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use PDO;

use AppBundle\Form\PhotogrammetryScaleBarTargetPairForm;
use AppBundle\Entity\PhotogrammetryScaleBarTargetPair;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoUserAccess;

class PhotogrammetryScaleBarTargetPairController extends Controller
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
     * @Route("/admin/projects/photogrammetry_scale_bar_target_pair/datatables_browse", name="photogrammetry_scale_bar_target_pair_browse_datatables", methods="POST")
     *
     * @param Request $request
     * @return JsonResponse The query result in JSON
     */
    public function datatablesBrowse(Request $request)
    {
        $params = array();
        $params['pdo_params'] = array();
        $params['search_sql'] = $params['sort'] = '';

        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $query_params = array(
          'record_type' => 'photogrammetry_scale_bar_target_pair',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
          'search_value' => $search,
          'parent_id' => $req['parent_id']
        );
        
        $results = $this->repo_storage_controller->execute('getDatatable', $query_params);

        return $this->json($results);
    }

    /**
     * Matches /admin/projects/photogrammetry_scale_bar_target_pair/manage/*
     *
     * @Route("/admin/projects/photogrammetry_scale_bar_target_pair/manage/{parent_id}/{id}", name="photogrammetry_scale_bar_target_pair_manage", methods={"GET","POST"}, defaults={"parent_id" = null, "id" = null})
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

        $data = new PhotogrammetryScaleBarTargetPair();
        $post = $request->request->all();
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        
        if(!empty($id) && empty($post)) {
          $rec = $this->repo_storage_controller->execute('getPhotogrammetryScaleBarTargetPair', array(
            'photogrammetry_scale_bar_target_pair_id' => $id));
          if(isset($rec)) {
            $data = (object)$rec;
          }
        }
        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Add the parent_id to the $data object
        $data->parent_photogrammetry_scale_bar_id = $parent_id;

        // Get data from lookup tables.
        $data->unit_options = $this->get_unit();
        $data->target_type_options = $this->get_target_type();
        
        // Create the form
        $form = $this->createForm(PhotogrammetryScaleBarTargetPairForm::class, $data);
        
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'photogrammetry_scale_bar_target_pair',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => (array)$data
            ));

            $this->addFlash('message', 'Record successfully updated.');
            return $this->redirect('/admin/projects/photogrammetry_scale_bar_target_pair/manage/' . $data->photogrammetry_scale_bar_id . '/' . $id);
        }

        return $this->render('datasets/photogrammetry_scale_bar_target_pair_form.html.twig', array(
            'page_title' => !empty($id) ? 'Photogrammetry Scale Bar Target Pair: ' . $data->target_type : 'Create Photogrammetry Scale Bar Target Pair',
            'data' => $data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
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
        $data[$label] = $value['unit_id'];
      }

      return $data;
    }

    /**
     * Get Target Types
     * @return  array|bool  The query result
     */
    public function get_target_type()
    {
      $data = array();
      
      $temp = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'target_type',
          'sort_fields' => array(
            0 => array('field_name' => 'label')
          ),
        )
      );

      foreach ($temp as $key => $value) {
        $label = $value['label'];
        $data[$label] = $value['target_type_id'];
      }

      return $data;
    }

    /**
     * @Route("/admin/projects/photogrammetry_scale_bar_target_pair/delete", name="photogrammetry_scale_bar_target_pair_remove_records", methods={"GET"})
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
                'record_type' => 'photogrammetry_scale_bar_target_pair',
                'record_id' => $id,
                'user_id' => $this->getUser()->getId(),
              ));
            }

            $this->addFlash('message', 'Records successfully removed.');

        } else {
            $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('photogrammetry_scale_bar_target_pair_browse');
    }

    /**
     * /\/\/\/ Route("/admin/projects/photogrammetry_scale_bar_target_pair/{id}", name="photogrammetry_scale_bar_target_pair_browse", methods="GET", defaults={"id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     */
    // public function browse(Connection $conn, Request $request)
    // {
    //     $PhotogrammetryScaleBarTargetPair = new PhotogrammetryScaleBarTargetPair;

    //     return $this->render('datasetElements/photogrammetry_scale_bar_target_pair_browse.html.twig', array(
    //         'page_title' => "Browse Photogrammetry Scale Bar Target Pair",
    //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
    //     ));
    // }
  
}
