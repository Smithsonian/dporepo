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
   * Matches /admin/uv_map/manage/*
   *
   * @Route("/admin/uv_map/add/{parent_id}", name="uv_map_add", methods={"GET","POST"}, defaults={"parent_id" = null})
   * @Route("/admin/uv_map/manage/{id}", name="uv_map_manage", methods={"GET","POST"})
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
    if(!$id && !$parent_id) throw $this->createNotFoundException('The record does not exist');

    // Get the parent project ID.
    $parent_records = $this->repo_storage_controller->execute('getParentRecords', array(
      'base_record_id' => $parent_id ? $parent_id : $id,
      'record_type' => $parent_id ? 'capture_dataset' : 'uv_map_with_model_id',
    ));

    // Check user's permissions.
    // If parent records exist and there's no parent_id, the record is being edited. Otherwise, the record is being added.
    if(is_array($parent_records) && array_key_exists('project_id', $parent_records) && !$parent_id) {
      $permission = 'edit_project_details';
    } else {
      $permission = 'create_project_details';
    }

    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access($username, $permission, $parent_records['project_id']);
    if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
    if(empty($post) && !empty($id)) {
      $data = $this->repo_storage_controller->execute('getRecordById', array(
        'record_type' => 'uv_map',
        'record_id' => (int)$id));
      if(empty($data)) throw $this->createNotFoundException('The record does not exist');
      $data = (object)$data;
    }

    // Add the parent_id to the $data object
    $data->model_id = $parent_id;

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
      return $this->redirect('/admin/projects/uv_map/manage/' . $data['model_id'] . '/' . $id);
    }
    return $this->render('datasets/uv_map_form.html.twig', array(
      'page_title' => !empty($id) ? 'UV Map: ' . $data->map_type : 'Create UV Map',
      'data' => $data,
      'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      'form' => $form->createView(),
    ));
  }

  /**
   * @Route("/admin/uv_map/delete", name="uv_map_remove_records", methods={"GET"})
   *
   * @param Connection $conn
   * @param Request $request
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
   */
  public function deleteMultiple(Connection $conn, Request $request)
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
   * /\/\/\/ Route("/admin/uv_map/{id}", name="uv_map_browse", methods="GET", defaults={"id" = null})
   *
   * @param Connection $conn
   * @param Request $request
   */
  // public function browse(Connection $conn, Request $request)
  // {
  //     $UvMap = new UvMap;

  //     return $this->render('datasetElements/uv_map_browse.html.twig', array(
  //         'page_title' => "Browse UV Maps",
  //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
  //     ));
  // }


}