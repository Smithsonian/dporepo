<?php

namespace AppBundle\Service;

use Doctrine\DBAL\Driver\Connection;

class RepoStorageStructureHybrid implements RepoStorageStructure {

  private $connection;

  public function __construct(Connection $connection) {
    $this->connection = $connection;
  }

  /***
   * @param null $schema_part name of schema definition/atom.
   * Return JSON schema for the repository.
   * If $schema_part is specified, return the schema for that atom.
   * @return mixed schema in JSON schema format.
   */
  public function getSchema($schema_atom = NULL){

    //@todo here return valid JSON representing the currently implemented schema.
    // If $schema_atom is specified, return the schema from that point.

    $temp = array();

    return json_encode($temp);

  }

  /***
   * @param $schema schema in JSON schema format.
   * @param $diff_only will return a diff between the existing schema and the newly specified schema.
   * Given $schema, generate the necessary structures in the data storage layer.
   * @return mixed array containing success/fail value, and any messages;
   * If $diff_only, returns success + messages indicating the differences.
   */
  public function setSchema($schema_json, $diff_only){
    //@todo implement the back-end updates or creation for structures
    // to support the schema specified in $schema_json



  }

  public function checkDatabaseExists() {

    try {
    $params = $this->connection->getParams();
    $dbname = $params['dbname'];
      $statement = $this->connection->prepare("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '$dbname'");
      $statement->execute();
      $ret = $statement->fetchAll();
      if(count($ret) <> 1) {
        return array('installed' => false, 'error' => 'Information_schema has ' . count($ret) . ' records for the database ' . $dbname);
      }
    }
    catch(\Throwable $ex) {
      return array('installed' => false, 'error' => 'Unable to retrieve database information from Schemata. ' . $ex->getMessage());
    }

    // Check if primary tables exist.
    $tables = array("project", "subject", "item", "model");

    $count_tables = 0;
      foreach ($tables as $table) {
      $table_exists = $this->connection->fetchAll("show tables like '$table'");
      if(count($table_exists) > 0) {
        $count_tables++;
      }
    }
    if(0 == $count_tables) {
      return array('installed' => false, 'error' => '');
    }
    elseif($count_tables == count($tables))
    {
      return array('installed' => true, 'error' => '');
    }
    else {
      return array('installed' => false, 'error' => 'The database is in an awkward state. Checked for ' . count($tables) . ' tables and ' . $count_tables . ' of these exist.');
    }
        }
        
  public function installDatabase() {

    $ret = $this->checkDatabaseExists();

    if (!is_array($ret) || (isset($ret['error']) && strlen(trim($ret['error'])) > 0)) {
      return $ret;
    }
    elseif($ret['installed'] == true) {
      $ret['error'] = 'The database already exists.';
      return $ret;
      }

    $ret = $this->install();
    return $ret;

    }

  public function install(){
    $file = 'database_create.sql';

    if (file_exists($file)) {

      try {
      $params = $this->connection->getParams();
      $dbname = $params['dbname'];

        $sql = 'USE ' . $dbname . '; ';
        $sql .= file_get_contents($file);

        $statement = $this->connection->prepare($sql);
        $ret = $statement->execute();

        return array('installed' => $ret, 'error' => '');
      }
      catch(\Throwable $ex) {
        return array('installed' => false, 'error' => 'Unable to generate database. ' . $ex->getMessage());
      }

    }
    else {
      return array('installed' => false, 'error' => 'Source file not found for database creation. Missing: \\web\\' . $file);
    }
    
  }
}