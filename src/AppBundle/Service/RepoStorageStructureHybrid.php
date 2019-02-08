<?php

namespace AppBundle\Service;

use \Symfony\Component\DependencyInjection\ContainerAware;
use AppBundle\Controller\RepoStorageStructureHybridController;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Filesystem;
use PDO;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use AppBundle\Utils\AppUtilities;


class RepoStorageStructureHybrid implements RepoStorageStructure {

  private $connection;
  protected $flysystem;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * @var string $external_file_storage_path
   */
  private $external_file_storage_path;

  public function __construct(Connection $connection) { //, $uploads_directory, $external_file_storage_path) {
    $this->connection = $connection;
    //$this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    //$this->external_file_storage_path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $external_file_storage_path) : $external_file_storage_path;;
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

  public function createBackupFile($include_schema = true, $include_data = true) {

    $db_exists = $this->checkDatabaseExists();

    if($db_exists['installed'] == false) {
      return array('return' => 'fail', 'errors' => array($db_exists['error']));
    }

    // Write backup to local file.
    $backup_results = $this->writeBackupToFile($include_schema = true, $include_data = true);
    if(isset($backup_results['errors']) && count($backup_results['errors']) > 0) {
      return $backup_results;
    }

  }

  private function writeBackupToFile($include_schema = true, $include_data = true) {

    $backup_dir = $this->uploads_directory . '/mysqlbackups/';

    if(!file_exists($backup_dir)) {
      $filesystem = new Filesystem\Filesystem();
      try {
        $filesystem->mkdir($backup_dir);
        $mode = 0664;
        $umask = umask();
        $filesystem->chmod($backup_dir, $mode, $umask);
      } catch (IOException $e) {
        // discard chmod failure (some filesystem may not support it)
        return array('return' => 'fail', 'errors' => array('Could not create backup directory. ' . $e->getMessage()));
      }
    }

    $backup_filename = 'repository_backup_' . (string)time() . '.sql';
    $backup_file_path = $backup_dir . $backup_filename;

    try {
      $params = $this->connection->getParams();
      //$username = $params['user'];
      //$password = $params['password'];
      $dbname = $params['dbname'];

      //@todo dump schema if $include_schema

      $sql = "SELECT `TABLE_NAME` FROM information_schema.`TABLES` where table_schema like '" . $dbname . "' ORDER BY `TABLE_NAME` ASC";
      $statement = $this->connection->prepare($sql);
      $statement->execute();
      $tables = $statement->fetchAll(PDO::FETCH_ASSOC);

      if(true === $include_data) {

        $handle = fopen($backup_file_path, "c");
        foreach($tables as $t) {
          $table_name = $t['TABLE_NAME'];

          // Get the column info.
          $sql_schema = "SELECT COLUMN_NAME FROM information_schema.`COLUMNS` WHERE table_schema LIKE '" . $dbname
            . "' AND TABLE_NAME LIKE '" . $table_name . "' ORDER BY ORDINAL_POSITION";
          $statement = $this->connection->prepare($sql_schema);
          $statement->execute();
          $columns = $statement->fetchAll(PDO::FETCH_COLUMN);
          $column_names = "INSERT INTO `" . $table_name . "` (" . implode(', ', $columns) . ") VALUES \r\n";

          // Dump the table data.
          $sql_data = "SELECT * FROM " . $table_name;
          $statement = $this->connection->prepare($sql_data);
          $statement->execute();
          $rows = $statement->fetchAll(PDO::FETCH_ASSOC);

          if(count($rows) > 0) {
            $row_count = count($rows);
            $counter = 0;
            fwrite($handle, $column_names);
            foreach($rows as $row) {
              $counter++;
              $row_values_array = array();
              foreach($row as $row_value) {
                $row_values_array[] = $this->connection->quote($row_value);
              }
              //$row_values = '(' . $this->connection->quote(implode(',', array_values($row))) . ");\r\n";
              $row_values = '(' . implode(',', array_values($row_values_array)) . ")";
              if($counter == $row_count) {
                $row_values .= ';';
              }
              else {
                $row_values .= ",\r\n";
              }
              fwrite($handle, $row_values);
            }
            fwrite($handle, "\r\n");
          }
        }
        fclose($handle);
      }

      //$mysqldump_output = shell_exec("mysqldump -u " . $username . " -p" . $password . " " . $dbname . " > " . $backup_file_path);
      //echo $mysqldump_output; /* Your output of the restore command */

      return array('result' => 'success', 'backup_filename' => $backup_filename, 'backup_filepath' => $backup_file_path);
    }
    catch(\Throwable $ex) {
      if($handle) {
        fclose($handle);
      }
      return array('return' => 'fail', 'errors' => array('Unable to dump database. ' . $ex->getMessage()));
    }

  }


  /**
   * @param $local_file_path The directory which contains files to be transferred.
   * @param $source_filename The remote path.
   * @return mixed array containing success/fail value, and any messages.
   */
  /*
  private function pushFileToDrastic($source_file_fullpath = null, $destination_filename = null, $flysystem) {
    $data = array();

    if (!file_exists($source_file_fullpath)) {
      return (array('errors' => array('Backup not copied to Drastic. Source backup file not found: ' . $source_file_fullpath)));
    }
    else {

      // Write the file to Drastic.
      try {

        $container = $this->getContainer();
        $flysystem = $container->get('oneup_flysystem.assets_filesystem');

        $stream = fopen($source_file_fullpath, 'r+');
        $flysystem->writeStream($destination_filename, $stream);
        // Before calling fclose on the resource, check if it’s still valid using is_resource.
        if (is_resource($stream)) {
          fclose($stream);
        }
      } // Catch the error.
      catch (Exception $e) {
        return(
          array(
            'result' => 'fail',
            'errors' => array($e->getMessage()
            )
          )
        );
      }

      $data['result'] = 'success';
      return $data;
    }

  }
  */

}