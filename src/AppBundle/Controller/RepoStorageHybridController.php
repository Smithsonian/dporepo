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
  private $project_dir;

  public function __construct(Connection $conn) { // , string $uploads_directory, string $external_file_storage_path) {
    $this->connection = $conn;
    $this->project_dir = realpath(__DIR__.'/../../../');

    //$this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    //$this->external_file_storage_path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $external_file_storage_path) : $external_file_storage_path;;
  }

  public function execute($function, $parameters = array()) {

    $this->repo_storage = new RepoStorageHybrid($this->connection, $this->project_dir);

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

  /*
  public function build($function, $parameters) {

    $this->repo_storage_structure = new RepoStorageStructureHybrid($this->connection, $this->project_dir);

    if(!method_exists($this->repo_storage_structure, $function)) {
      //@todo
      return NULL;
    }
    else {
      if ($parameters) {
        return $this->repo_storage_structure->$function($parameters);
      }else{
        return $this->repo_storage_structure->$function();
      }
      
    }

  }
*/
}
