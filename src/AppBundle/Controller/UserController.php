<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Controller\RepoStorageHybridController;
use PDO;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

// Subjects methods
use AppBundle\Controller\SubjectsController;
// Items methods
use AppBundle\Controller\ItemsController;

class UserController extends Controller {
  /**
   * @var object $u
   */
  public $u;
  private $repo_storage_controller;

  /**
   * Constructor
   * @param object $u Utility functions object
   */
  public function __construct(AppUtilities $u, Connection $conn) {
    // Usage: $this->u->dumper($variable);
    $this->u = $u;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
  }

  /**
   * @Route("/admin/datatables_browse_roles/", name="datatables_browse_roles", methods={"GET","POST"})
   */
  public function datatables_browse_roles(Request $request) {

    $req = $request->request->all();

    $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
    $sort_field = array_key_exists('order', $req) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : 'role_slug';
    $sort_order = array_key_exists('order', $req) ? $req['order'][0]['dir'] : '';
    $start_record = !empty($req['start']) ? $req['start'] : 0;
    $stop_record = !empty($req['length']) ? $req['length'] : 20;

    $query_params = array(
      'sort_field' => $sort_field,
      'sort_order' => $sort_order,
      'start_record' => $start_record,
      'stop_record' => $stop_record,
    );
    if ($search) {
      $query_params['search_value'] = $search;
    }

    $data = $this->repo_storage_controller->execute('getDatatableRoles', $query_params);

    foreach($data['aaData'] as $k => $r) {
      $data['aaData'][$k]['permissions'] = str_replace(',', ', ', $r['permissions']);
    }
    return $this->json($data);

  }

  /**
   * @Route("/admin/roles/", name="roles_list", methods={"GET","POST"})
   */
  public function showRoles(Request $request) {

    return $this->render('users/browse_roles.html.twig', array(
      'page_title' => 'User Roles',
    ));
  }

  /**
   *
   * @Route("/admin/users/role_edit/{role_slug}", name="role_edit", methods={"GET","POST"}, defaults={"role_slug" = null})
   *
   * @param   string  $role_slug    The slug/shortname for the role
   * @param   object  Request       Request object
   * @return  array|bool            The query result
   */
  public function editRole(Request $request, $role_slug) {

    $data = $this->repo_storage_controller->execute('getRecords', array(
        'base_table' => 'role',
        'sort_fields' => array(
          0 => array('field_name' => 'role_title')
        ),
      )
    );

    return $this->render('users/role_edit_form.html.twig', array(
      'page_title' => 'User Roles',
      'role_data' => $data,
    ));
  }

  /**
   * @Route("/admin/datatables_browse_users/", name="datatables_browse_users", methods={"GET","POST"})
   */
  public function datatables_browse_users(Request $request) {

    $req = $request->request->all();

    $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
    $sort_field = array_key_exists('order', $req) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : 'username';
    $sort_order = array_key_exists('order', $req) ? $req['order'][0]['dir'] : '';
    $start_record = !empty($req['start']) ? $req['start'] : 0;
    $stop_record = !empty($req['length']) ? $req['length'] : 20;

    $query_params = array(
      'sort_field' => $sort_field,
      'sort_order' => $sort_order,
      'start_record' => $start_record,
      'stop_record' => $stop_record,
    );
    if ($search) {
      $query_params['search_value'] = $search;
    }

    $data = $this->repo_storage_controller->execute('getDatatableUsers', $query_params);

    foreach($data['aaData'] as $k => $u){
      $data['aaData'][$k]['project'] = $u['project_name'];
      $data['aaData'][$k]['stakeholder'] = trim($u['unit_stakeholder_label'] . ' ' . $u['unit_stakeholder_full_name']);
    }

    return $this->json($data);

  }

  /**
   * @Route("/admin/users/", name="users_list", methods={"GET","POST"})
   */
  public function browse_users(Request $request)
  {

    return $this->render('users/browse_users.html.twig', array(
      'page_title' => 'Users',
    ));

  }



  /**
   * @Route("/admin/users/{username_canonical}/edit", name="user_edit", methods={"GET","POST"}, defaults={"username_canonical" = null})
   *
   * @param   string  username_canonical   The canonical username
   * @param   object  Request           Request object
   * @return  array|bool                The query result
   */
  public function editUser(Request $request, $username_canonical) {

    $data = $this->repo_storage_controller->execute('getUsers', array('test' => 'test'));

    return $this->render('users/edit_user.html.twig', array(
      'page_title' => 'Edit User',
      'role_data' => $data,
    ));
  }

  /**
   * @Route("/admin/users/{username_canonical}/route_name/project_id", name="check_user_permission", methods={"GET","POST"} )
   *
   * @param   string  username_canonical   The canonical username
   * @param   int  route_name   The Symfony route name
   * @param   int  project_id   The project ID
   * @param   object  Request           Request object
   * @return  array|bool                The query result
   */
  public function userHasPermission(Request $request, $username_canonical, $route_name, $project_id) {

    $data = $this->repo_storage_controller->execute('getUsers', array('test' => 'test'));

    return $this->render('users/edit_user.html.twig', array(
      'page_title' => 'Edit User',
      'role_data' => $data,
    ));
  }


}