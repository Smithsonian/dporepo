<?php

namespace AppBundle\Service;

use Doctrine\DBAL\Driver\Connection;

use PDO;


use AppBundle\Controller\RepoStorageHybridController;

class RepoUserAccess {

  private $repo_storage_controller;

  /**
   * Constructor
   * @param object  $conn  Connection object
   */
  public function __construct(\Doctrine\DBAL\Connection $conn)
  {
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
  }


  public function user_has_access($username, $permission, $project_id = NULL) {

    // Get user access for the specified params.
    $user_has_access = $this->repo_storage_controller->execute('getUserAccess',
      array(
        'username_canonical' => $username,
        'permission_name' => $permission,
        'project_id' => $project_id
      )
    );

    return $user_has_access;

  }



}