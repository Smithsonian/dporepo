<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Role;
use AppBundle\Entity\UserRole;
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
use AppBundle\Service\RepoUserAccess;

class UserController extends Controller {
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
   * @Route("/admin/datatables_browse_roles/", name="datatables_browse_roles", methods={"GET","POST"})
   */
  public function datatablesBrowseRoles(Request $request) {

    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      return $this->json(array());
    }

    $req = $request->request->all();
    if(empty($req)) {
      $req = $request->query->all();
    }

    $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
    $sort_field = array_key_exists('order', $req) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : 'rolename_canonical';
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
   * @Route("/admin/roles", name="roles_list", methods={"GET","POST"})
   */
  public function showRoles(Request $request) {

    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    return $this->render('users/browse_roles.html.twig', array(
      'page_title' => 'User Roles',
    ));
  }

  /**
   * Delete Role
   *
   * @Route("/admin/roles/delete", name="role_delete", methods={"GET"})
   * Run a query to delete one or more role and related user_role records.
   *
   * @param   int     $ids      The record ids
   * @param   object  $request  Request object
   * @return  void
   */
  public function deleteRole(Request $request)
  {
    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    $ids = $request->query->get('ids');

    if(!empty($ids) && !empty($username)) {

      $ids_array = explode(',', $ids);

      foreach ($ids_array as $key => $id) {
        $ret = $this->repo_storage_controller->execute('deleteRole', array(
          'role_id' => $id,
          'username_canonical' => $username,
          'user_id' => $this->getUser()->getId(),
        ));
      }

      $this->addFlash('message', 'Roles successfully removed.');

    } else {
      $this->addFlash('message', 'Missing data. No records removed.');
    }

    return $this->redirectToRoute('roles_list');
  }

  /**
   *
   * @Route("/admin/role/add", name="role_add", methods={"GET","POST"}, defaults={"role_slug" = null})
   * @Route("/admin/role/view/{role_slug}", name="role_edit", methods={"GET","POST"})
   *
   * @param   string  $role_slug    The slug/shortname for the role
   * @param   object  Request       Request object
   * @return  array|bool            The query result
   */
  public function editRole( $role_slug, Connection $conn, Request $request) {

    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    $role = new Role();
    $post = $request->request->all();
    if(!empty($post)) {
      $role_slug = !empty($request->attributes->get('role_slug')) ? $request->attributes->get('role_slug') : false;
    }

    // Retrieve data from the database.
    if (!empty($role_slug) && $role_slug !== "add" && empty($post)) {
      $role = $this->repo_storage_controller->execute('getRole',
        array('rolename_canonical' => $role_slug)
      );
    }
    else {
      // Get default options.
      $r = $this->repo_storage_controller->execute('getRole',
          array('rolename_canonical' => '')
        );
      $role->role_permissions = $r['role_permissions'];
    }

    // Create the form
    $form = $this->createForm(\AppBundle\Form\RoleForm::class, $role);

    // Handle the request
    $form->handleRequest($request);

    // If form is submitted and passes validation, insert/update the database record.
    if ($form->isSubmitted() && $form->isValid()) {

      $role = $form->getData();
      $role = array_merge((array)$role, array('user_id' => $this->getUser()->getId()));
      if(array_key_exists('rolename_canonical', $role) && $role['rolename_canonical'] == 'new') {
        $role['rolename_canonical'] = '';
      }

      $role_slug = $this->repo_storage_controller->execute('saveRole', $role );

      $this->addFlash('message', 'Role successfully updated.');
      //@todo return $this->redirect('/admin/roles/' . $role_slug);
    }

    $role = (array)$role;
    return $this->render('users/role_form.html.twig', array(
      'page_title' => !empty($role_slug) && $role_slug !== 'new' ? 'Role: ' . $role['rolename'] : 'Create Role',
      'role_data' => $role,
      'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      'form' => $form->createView(),
    ));

  }

  /**
   * @Route("/admin/datatables_browse_users/", name="datatables_browse_users", methods={"GET","POST"})
   */
  public function datatablesBrowseUsers(Request $request) {

    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      return $this->json(array());
    }

    $req = $request->request->all();
    if(empty($req)) {
      $req = $request->query->all();
    }

    $search = !empty($req['search']['value']) ? $req['search']['value'] : false;

    $sort_field = array_key_exists('order', $req) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : 'username_canonical';
    $sort_order = array_key_exists('order', $req) ? $req['order'][0]['dir'] : '';
    $start_record = !empty($req['start']) ? $req['start'] : 0;
    $stop_record = !empty($req['length']) ? $req['length'] : 20;
    $role_slug = array_key_exists('role_slug', $req) ? $req['role_slug'] : NULL;

    $query_params = array(
      'sort_field' => $sort_field,
      'sort_order' => $sort_order,
      'start_record' => $start_record,
      'stop_record' => $stop_record,
    );
    if(NULL !== $role_slug) {
      $query_params['role_slug'] = $role_slug;
    }
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
  public function browseUsers(Request $request)
  {
    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    return $this->render('users/browse_users.html.twig', array(
      'page_title' => 'Users',
    ));

  }


  /**
   * @Route("/admin/datatables_browse_user_roles/{username_canonical}", name="datatables_browse_user_roles", methods={"GET","POST"})
   */
  public function datatablesBrowseUserRoles(Request $request) {

    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      return $this->json(array());
    }

    $req = $request->request->all();
    $username_canonical = !empty($request->attributes->get('username_canonical')) ? $request->attributes->get('username_canonical') : NULL;

    $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
    $sort_field = array_key_exists('order', $req) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : 'username_canonical';
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
    if(NULL !== $username_canonical) {
      $query_params['username_canonical'] = $username_canonical;
    }

    $data = $this->repo_storage_controller->execute('getDatatableUserRoles', $query_params);

    foreach($data['aaData'] as $k => $u){
      $data['aaData'][$k]['project'] = $u['project_name'];
      $data['aaData'][$k]['stakeholder'] = trim($u['unit_stakeholder_label'] . ' ' . $u['unit_stakeholder_full_name']);
    }

    return $this->json($data);

  }

  /**
   * @Route("/admin/user/view/{username_canonical}", name="user_roles_list", methods={"GET","POST"})
   */
  public function browseUserRoles(Request $request, $username_canonical)
  {

    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    return $this->render('users/browse_user_roles.html.twig', array(
      'page_title' => 'User Roles for ' . $username_canonical,
      'username_canonical' => $username_canonical,
    ));

  }


  /**
   * @Route("/admin/user/role/{username_canonical}/add", name="user_role_add", methods={"GET","POST"}, defaults={"user_role_id" = null})
   * @Route("/admin/user/role/{username_canonical}/{user_role_id}", name="user_role_edit", methods={"GET","POST"})
   *
   * @param   string  username_canonical   The canonical username
   * @param   object  Request           Request object
   * @return  array|bool                The query result
   */
  public function editUserRole(Request $request, $username_canonical, $user_role_id) {

    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    $user_role = new UserRole();
    $user_role = (array)$user_role;
    $post = $request->request->all();
    if(!empty($post)) {
      $username_canonical = !empty($request->attributes->get('username_canonical')) ? $request->attributes->get('username_canonical') : false;
      $user_role_id = !empty($request->attributes->get('user_role_id')) ? $request->attributes->get('user_role_id') : false;
    }

    // Get roles, projects, stakeholders.
    $all_roles = $this->repo_storage_controller->execute('getValues',
      array('tablename' => 'role')
    );
    $all_projects = $this->repo_storage_controller->execute('getValues',
      array('tablename' => 'project')
    );
    $all_stakeholders = $this->repo_storage_controller->execute('getValues',
      array('tablename' => 'stakeholder')
    );

    // Retrieve data from the database.
    if (!empty($user_role_id) && empty($post)) {
      $user_role = $this->repo_storage_controller->execute('getUserRole',
        array('user_role_id' => $user_role_id)
      );
    }

    $user_role['stakeholders_array'] = $all_stakeholders;
    $user_role['projects_array'] = $all_projects;
    $user_role['roles_array'] = $all_roles;
    $user_role['username_canonical'] = $username_canonical;

    // Create the form
    $form = $this->createForm(\AppBundle\Form\UserRoleForm::class, $user_role);

    // Handle the request
    $form->handleRequest($request);

    // If form is submitted and passes validation, insert/update the database record.
    if ($form->isSubmitted() && $form->isValid()) {
      $user_role = $form->getData();
      $user_role = array_merge((array)$user_role, array('user_id' => $this->getUser()->getId()));
      if(array_key_exists('user_role_id', $user_role) && $user_role['user_role_id'] == 'new') {
        $user_role['user_role_id'] = '';
      }
      $user_role_id = $this->repo_storage_controller->execute('saveUserRole', $user_role );

      $this->addFlash('message', 'User role successfully updated.');
      return $this->redirect('/admin/user/view/' . $username_canonical);
    }

    return $this->render('users/userrole_form.html.twig', array(
      'page_title' => (!empty($user_role_id) ? 'Edit Role' : 'Add Role') . ' for ' . $user_role['username_canonical'],
      'data' => (array)$user_role,
      'form' => $form->createView(),
    ));
  }

  /**
   * Delete Multiple User Roles
   *
   * @Route("/admin/user/role/{username_canonical}/delete", name="user_role_delete", methods={"GET"})
   * Run a query to delete multiple records.
   *
   * @param   int     $ids      The record ids
   * @param   object  $request  Request object
   * @return  void
   */
  public function deleteUserRole(Request $request, $username_canonical)
  {
    $username = $this->getUser()->getUsernameCanonical();
    $access = $this->repo_user_access->get_user_access_any($username, 'user_edit');
    if(!array_key_exists('username_canonical', $access) || !isset($access['username_canonical'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    $ids = $request->query->get('ids');

    if(!empty($ids) && !empty($username_canonical)) {

      $ids_array = explode(',', $ids);

      foreach ($ids_array as $key => $id) {
        $ret = $this->repo_storage_controller->execute('deleteUserRole', array(
          'user_role_id' => $id,
          'username_canonical' => $username_canonical,
          'user_id' => $this->getUser()->getId(),
        ));
      }

      $this->addFlash('message', 'User roles successfully removed.');

    } else {
      $this->addFlash('message', 'Missing data. No records removed.');
    }

    return $this->redirectToRoute('user_roles_list', array('username_canonical' => $username_canonical));
  }


}