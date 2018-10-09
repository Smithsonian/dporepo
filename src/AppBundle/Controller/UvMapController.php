<?php
namespace AppBundle\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;
use PDO;
use AppBundle\Form\UvMapForm;
use AppBundle\Entity\UvMap;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoUserAccess;

class UvMapController extends Controller
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
   * @Route("/admin/projects/uv_map/datatables_browse", name="uv_map_browse_datatables", methods="POST")
   *
   * @param Connection $conn
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function datatablesBrowse(Connection $conn, Request $request)
  {
    $req = $request->request->all();
    $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
    $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
    $sort_order = $req['order'][0]['dir'];
    $start_record = !empty($req['start']) ? $req['start'] : 0;
    $stop_record = !empty($req['length']) ? $req['length'] : 20;

    $query_params = array(
      'record_type' => 'uv_map',
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
   * Matches /admin/projects/uv_map/manage/*
   *
   * @Route("/admin/projects/uv_map/manage/{parent_id}/{id}", name="uv_map_manage", methods={"GET","POST"}, defaults={"parent_id" = null, "id" = null})
   *
   * @param Connection $conn
   * @param Request $request
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
   */
  function formView(Connection $conn, Request $request)
  {
    $data = new UvMap();
    $post = $request->request->all();
    $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
    $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

    // If no parent_id is passed, throw a createNotFoundException (404).
    if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

    // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
    
    if(empty($post) && !empty($id)) {
      $data = $this->repo_storage_controller->execute('getRecordById', array(
        'record_type' => 'uv_map',
        'record_id' => (int)$id));
      $data = (object)$data;
    }
    if(!$data) throw $this->createNotFoundException('The record does not exist');

    // Add the parent_id to the $data object
    $data->parent_capture_dataset_repository_id = $parent_id;

    // Create the form
    $form = $this->createForm(UvMapForm::class, $data);

    // Handle the request
    $form->handleRequest($request);

    // If form is submitted and passes validation, insert/update the database record.
    if ($form->isSubmitted() && $form->isValid()) {

      $data = (array)$form->getData();
      $id = $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => 'uv_map',
        'record_id' => $id,
        'user_id' => $this->getUser()->getId(),
        'values' => $data
      ));

      $this->addFlash('message', 'Record successfully updated.');
      return $this->redirect('/admin/projects/uv_map/manage/' . $data['parent_capture_dataset_repository_id'] . '/' . $id);
    }
    return $this->render('datasets/uv_map_form.html.twig', array(
      'page_title' => !empty($id) ? 'UV Map: ' . $data->map_type : 'Create UV Map',
      'data' => $data,
      'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      'form' => $form->createView(),
    ));
  }

  /**
   * @Route("/admin/projects/uv_map/delete", name="uv_map_remove_records", methods={"GET"})
   *
   * @param Connection $conn
   * @param Request $request
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
   */
  public function deleteMultiple(Connection $conn, Request $request)
  {
    if(!empty($request->query->get('ids'))) {

      // Create the array of ids.
      $ids_array = explode(',', $request->query->get('ids'));

      

      // Loop thorough the ids.
      foreach ($ids_array as $key => $id) {
        // Run the query against a single record.
        $ret = $this->repo_storage_controller->execute('markRecordInactive', array(
          'record_type' => 'uv_map',
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
   * /\/\/\/ Route("/admin/projects/uv_map/{id}", name="uv_map_browse", methods="GET", defaults={"id" = null})
   *
   * @param Connection $conn
   * @param Request $request
   */
  // public function browse(Connection $conn, Request $request)
  // {
  //     $UvMap = new UvMap;
  //     // Database tables are only created if not present.
  //     $UvMap->createTable();
  //     return $this->render('datasetElements/uv_map_browse.html.twig', array(
  //         'page_title' => "Browse UV Maps",
  //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
  //     ));
  // }


}