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
   * @Route("/admin/users/roles/", name="roles_list", methods="GET")
   */
  public function showRoles(Connection $conn, Request $request) {

    $data = $this->repo_storage_controller->execute('getRecords', array(
        'base_table' => 'role',
        'sort_fields' => array(
          0 => array('field_name' => 'role_title')
        ),
      )
    );

    return $this->render('users/browse_roles.html.twig', array(
      'page_title' => 'User Roles',
      'role_data' => $data,
    ));
  }

  /**
   * Matches /admin/users/role_edit/*
   *
   * @Route("/admin/users/role_edit/{role_slug}", name="role_edit", methods={"GET","POST"}, defaults={"role_slug" = null})
   *
   * @param   int     $role_slug           The dataset_type ID
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
   * @Route("/admin/users/", name="users_list", methods="GET")
   */
  public function showUsers(Connection $conn, Request $request) {

    $data = $this->repo_storage_controller->execute('getRecords', array(
        'base_table' => 'user',
        'sort_fields' => array(
          0 => array('field_name' => 'username')
        ),
      )
    );

    return $this->render('users/browse_users.html.twig', array(
      'page_title' => 'Users',
      'role_data' => $data,
    ));
  }


}