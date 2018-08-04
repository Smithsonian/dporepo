<?php

namespace AppBundle\Controller;

use AppBundle\Service\RepoStorageHybrid;
use AppBundle\Service\RepoStorageStructureHybrid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

class RepoStorageHybridController extends Controller
{

  private $repo_storage;
  private $repo_storage_structure;
  private $connection;

  public function __construct(Connection $conn) {
    $this->connection = $conn;
  }

  public function execute($function, $parameters = array()) {

    $this->repo_storage = new RepoStorageHybrid($this->connection);

    if(!method_exists($this->repo_storage, $function)) {
      //@todo
      return NULL;
    }
    else {
      if(count($parameters) > 0) {
        return $this->repo_storage->$function($parameters);
      }
      else {
        return $this->repo_storage->$function();
      }
    }

  }

  public function build($function, $parameters) {

    $this->repo_storage_structure = new RepoStorageStructureHybrid($this->connection);

    if(!method_exists($this->repo_storage_structure, $function)) {
      //@todo
      return NULL;
    }
    else {
      return $this->repo_storage_structure->$function($parameters);
    }

  }
}
