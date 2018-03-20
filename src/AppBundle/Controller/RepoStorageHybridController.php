<?php

namespace AppBundle\Controller;

use AppBundle\Service\RepoStorageHybrid;
use AppBundle\Service\RepoStorageStructureHybrid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\DBAL\Driver\Connection;

class RepoStorageHybridController extends Controller
{

  private $repo_storage;
  private $repo_storage_structure;
  protected $container;
  private $connection;

  public function __construct() {
    //$this->container = $this->container->get('doctrine.dbal.default_connection');
  }

  public function execute($function, $parameters) {

    if(!is_object($this->container)) {
      //@todo log error
      return NULL;
    }
    $this->connection = $this->container->get('doctrine.dbal.default_connection');
    $this->repo_storage = new RepoStorageHybrid($this->connection);

    if(!method_exists($this->repo_storage, $function)) {
      //@todo
      return NULL;
    }
    else {
      return $this->repo_storage->$function($parameters);
    }

  }

  public function build($function, $parameters) {
    $this->connection = $this->container->get('doctrine.dbal.default_connection');
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
