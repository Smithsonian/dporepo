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

  /**
   * Create Table
   *
   * This is a placeholder and may be either called by
   * or superseded by the setSchema function.
   *
   * @param   string $table_name  Name of table to create
   * @return  TRUE or die
   */
  public function createTable($table_name) {

    print_r($table_name);
    return;

      $sql = '';
      switch($table_name) {
        case 'projects':
          $sql = "CREATE TABLE IF NOT EXISTS `projects` (
            `project_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `project_name` varchar(255) DEFAULT '',
            `stakeholder_guid` varchar(255) DEFAULT '',
            `project_description` text,
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`project_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
            KEY `projects_label` (`project_name`,`stakeholder_guid`)
          )";
        break;
        case 'isni_data':
          $statement = $this->connection->prepare("CREATE TABLE IF NOT EXISTS `isni_data` (
            `isni_id` int(11) NOT NULL AUTO_INCREMENT,
            `isni_label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`isni_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
          ) ");
        break;
        case 'capture_data_file':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_data_file` (
              `capture_data_file_repository_id` int(11) NOT NULL AUTO_INCREMENT,
              `parent_capture_data_element_repository_id` int(11),
              `capture_data_file_name` varchar(255),
              `capture_data_file_type` varchar(255),
              `is_compressed_multiple_files` varchar(255),
              `date_created` datetime NOT NULL,
              `created_by_user_account_id` int(11) NOT NULL,
              `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `last_modified_user_account_id` int(11) NOT NULL,
              `active` tinyint(1) NOT NULL DEFAULT '1',
              PRIMARY KEY (`capture_data_file_repository_id`),
              KEY `created_by_user_account_id` (`created_by_user_account_id`),
              KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ";
          break;
        case 'capture_dataset_rights':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_data_file` (
            `capture_data_file_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_data_element_repository_id` int(11),
            `capture_data_file_name` varchar(255),
            `capture_data_file_type` varchar(255),
            `is_compressed_multiple_files` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_data_file_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ";
          break;
        case 'capture_device':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_device` (
            `capture_device_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_data_element_repository_id` int(11),
            `calibration_file` varchar(255),
            `capture_device_component_ids` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_device_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'capture_device_component':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_device_component` (
            `capture_device_component_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_device_repository_id` int(11),
            `serial_number` varchar(255),
            `capture_device_component_type` varchar(255),
            `manufacturer` varchar(255),
            `model_name` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_device_component_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'model':
          $sql = "CREATE TABLE IF NOT EXISTS `model` (
            `model_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_dataset_repository_id` int(11),
            `model_guid` varchar(255),
            `date_of_creation` datetime,
            `model_file_type` varchar(255),
            `derived_from` varchar(255),
            `creation_method` varchar(255),
            `model_modality` varchar(255),
            `units` varchar(255),
            `is_watertight` varchar(255),
            `model_purpose` varchar(255),
            `point_count` varchar(255),
            `has_normals` varchar(255),
            `face_count` varchar(255),
            `vertices_count` varchar(255),
            `has_vertex_color` varchar(255),
            `has_uv_space` varchar(255),
            `model_maps` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`model_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'photogrammetry_scale_bar':
          $sql = "CREATE TABLE IF NOT EXISTS `photogrammetry_scale_bar` (
            `photogrammetry_scale_bar_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_dataset_repository_id` int(11),
            `scale_bar_id` varchar(255),
            `scale_bar_manufacturer` varchar(255),
            `scale_bar_barcode_type` varchar(255),
            `scale_bar_target_pairs` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`photogrammetry_scale_bar_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'photogrammetry_scale_bar_target_pair':
          $sql = "CREATE TABLE IF NOT EXISTS `photogrammetry_scale_bar_target_pair` (
            `photogrammetry_scale_bar_target_pair_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_photogrammetry_scale_bar_repository_id` int(11),
            `target_type` varchar(255),
            `target_pair_1_of_2` varchar(255),
            `target_pair_2_of_2` varchar(255),
            `distance` varchar(255),
            `units` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`photogrammetry_scale_bar_target_pair_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'processing_action':
          $sql = "CREATE TABLE IF NOT EXISTS `processing_action` (
            `processing_action_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `target_model_repository_id` int(11),
            `preceding_processing_action_repository_id` int(11),
            `date_of_action` datetime,
            `action_method` varchar(255),
            `software_used` varchar(255),
            `action_description` mediumtext,
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`processing_action_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'uv_map':
          $sql = "CREATE TABLE IF NOT EXISTS `uv_map` (
            `uv_map_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_dataset_repository_id` int(11),
            `map_type` varchar(255),
            `map_file_type` varchar(255),
            `map_size` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`uv_map_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
      }

      if(strlen($sql) == 0) {
        die('CREATE TABLE `' . $table_name . '` failed. Table name not recognized. Could not build SQL for CREATE statement.');
      }

      $this->connection->prepare($sql . " ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='This table stores " . $table_name . " metadata'");
      $statement->execute();
      $error = $this->connection->errorInfo();

      if ($error[0] !== '00000') {
        var_dump($this->connection->errorInfo());
        die('CREATE TABLE `' . $table_name . '` failed.');
      } else {
        return TRUE;
      }

  }

}