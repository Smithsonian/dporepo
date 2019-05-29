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


  public function user_has_access_by_stakeholder($username, $permission, $stakeholder_id = NULL) {

    // Get user access for the specified params.
    $user_has_access = $this->repo_storage_controller->execute('getUserAccessByStakeholder',
      array(
        'username_canonical' => $username,
        'permission_name' => $permission,
        'stakeholder_id' => $stakeholder_id,
      )
    );

    if(is_array($user_has_access) && array_key_exists('username_canonical', $user_has_access) && isset($user_has_access['username_canonical'])) {
      return true;
    }
    else {
      return false;
    }

  }

  public function user_has_access_by_project($username, $permission, $project_id = NULL) {

    // Get user access for the specified params.
    $user_has_access = $this->repo_storage_controller->execute('getUserAccessByProject',
      array(
        'username_canonical' => $username,
        'permission_name' => $permission,
        'project_id' => $project_id
      )
    );

    if(is_array($user_has_access) && array_key_exists('username_canonical', $user_has_access) && isset($user_has_access['username_canonical'])) {
      return true;
    }
    else {
      return false;
    }

  }

  public function get_user_access($username, $permission, $project_id = NULL, $stakeholder_id = NULL) {

    if(NULL == $stakeholder_id && NULL == $project_id) {
      // Get user access for the specified params.
      $user_has_access = $this->repo_storage_controller->execute('getUserAccessByStakeholder',
        array(
          'username_canonical' => $username,
          'permission_name' => $permission,
        )
      );
    }
    elseif(NULL == $stakeholder_id) {
      // Get user access for the specified params.
      $user_has_access = $this->repo_storage_controller->execute('getUserAccessByProject',
        array(
          'username_canonical' => $username,
          'permission_name' => $permission,
          'project_id' => $project_id
        )
      );
    }
    else {
      // Get user access for the specified params.
      $user_has_access = $this->repo_storage_controller->execute('getUserAccessByStakeholder',
        array(
          'username_canonical' => $username,
          'permission_name' => $permission,
          'stakeholder_id' => $stakeholder_id,
        )
      );
    }

    // Returns an array with a single row containing keys username_canonical, permission_name, project_ids.
    // Project_ids contains a string which is a comma-separated list of project_ids for which the user has this permission.
    if(is_array($user_has_access) && array_key_exists('username_canonical', $user_has_access)
      && isset($user_has_access['username_canonical']) && $user_has_access['username_canonical'] == $username) {

      // If $user_has_access['project_ids'] not an array, make it an array.
      if (!empty($user_has_access['project_ids']) && !is_array($user_has_access['project_ids'])) {
        $user_has_access['project_ids'] = !is_array($user_has_access['project_ids']) ? array($user_has_access['project_ids']) : $user_has_access['project_ids'];
      }

      // If project_ids is empty, the user has access to all projects.
      // Load that key's value with all available projects
      if(empty($user_has_access['project_ids'])) {

        $p = $this->repo_storage_controller->execute('getAllProjectIds', array());
        $user_has_access['project_ids'] = !is_array($p['project_ids']) ? explode(',', $p['project_ids']) : $p['project_ids'];

      }
      return $user_has_access;
    }
    else {
      return array();
    }
  }

  public function get_user_access_any($username, $permission) {

    // Get user access for the specified params.
    $user_has_access = $this->repo_storage_controller->execute('getUserAccessAny',
      array(
        'username_canonical' => $username,
        'permission_name' => $permission,
      )
    );

    // Returns an array with a single row containing keys username_canonical, permission_name, project_ids.
    // Project_ids contains a string which is a comma-separated list of project_ids for which the user has this permission.
    if(is_array($user_has_access) && array_key_exists('username_canonical', $user_has_access)
      && isset($user_has_access['username_canonical']) && $user_has_access['username_canonical'] == $username) {

      // If project_ids is empty, the user has access to all projects.
      // Load that key's value with all available projects
      if(empty($user_has_access['project_ids'])) {
        $p = $this->repo_storage_controller->execute('getAllProjectIds', array());
        $user_has_access['project_ids'] = explode(',', $p['project_ids']);
      }

      if(!is_array($user_has_access['project_ids'])) {
        $user_has_access['project_ids'] = explode(',', $user_has_access['project_ids']);
      }

      return $user_has_access;
    }
    else {
      return array();
    }
  }

}