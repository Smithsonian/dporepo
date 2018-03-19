<?php

namespace AppBundle\Controller;

use AppBundle\Service\RepoStorageHybrid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\Container;
use Doctrine\DBAL\Driver\Connection;

class RepoStorageHybridController extends Controller
{

  private $repo_storage;

  public function __construct() {
    //$this->container = $this->container->get('doctrine.dbal.default_connection');
  }

  public function execute($function, $parameters) {
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
}
