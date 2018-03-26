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
  public function createTable($parameters) {

      if(!array_key_exists('table_name', $parameters)) {
        return;
      }
      $table_name = $parameters['table_name'];

      $sql = '';
      switch($table_name) {
        case 'background_removal_method':
          $sql = "CREATE TABLE IF NOT EXISTS `background_removal_method` (
            `background_removal_method_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`background_removal_method_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'calibration_object_type':
          $sql = "CREATE TABLE IF NOT EXISTS `calibration_object_type` (
            `calibration_object_type_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`calibration_object_type_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'camera_cluster_type':
          $sql = "CREATE TABLE IF NOT EXISTS `camera_cluster_type` (
            `camera_cluster_type_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`camera_cluster_type_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'capture_data_element':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_data_element` (
            `capture_data_element_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `capture_dataset_repository_id` int(11) NOT NULL,
            `capture_device_configuration_id` varchar(255) DEFAULT '',
            `capture_device_field_id` int(11) DEFAULT NULL,
            `capture_sequence_number` int(11) DEFAULT NULL,
            `cluster_position_field_id` int(11) DEFAULT NULL,
            `position_in_cluster_field_id` int(11) DEFAULT NULL,
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_data_element_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
            KEY `dataset_element_guid` (`capture_device_configuration_id`)
          )";
          break;
        case 'capture_data_file':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_data_file` (
              `capture_data_file_repository_id` int(11) NOT NULL AUTO_INCREMENT,
              `capture_data_element_repository_id` int(11),
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
        case 'capture_dataset':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_dataset` (
            `capture_dataset_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `capture_dataset_guid` varchar(255) NOT NULL DEFAULT '',
            `project_repository_id` int(255) DEFAULT NULL,
            `item_repository_id` int(11) NOT NULL,
            `capture_dataset_field_id` int(11) NOT NULL,
            `capture_method` int(11) DEFAULT NULL,
            `capture_dataset_type` int(11) DEFAULT NULL,
            `capture_dataset_name` varchar(255) NOT NULL DEFAULT '',
            `collected_by` varchar(255) NOT NULL DEFAULT '',
            `date_of_capture` datetime NOT NULL,
            `capture_dataset_description` text,
            `collection_notes` text,
            `support_equipment` varchar(255) DEFAULT NULL,
            `item_position_type` int(11) DEFAULT NULL,
            `item_position_field_id` int(11) NOT NULL,
            `item_arrangement_field_id` int(11) NOT NULL,
            `positionally_matched_capture_datasets` varchar(255) DEFAULT '',
            `focus_type` int(11) DEFAULT NULL,
            `light_source_type` int(11) DEFAULT NULL,
            `background_removal_method` int(11) DEFAULT NULL,
            `cluster_type` int(11) DEFAULT NULL,
            `cluster_geometry_field_id` int(11) DEFAULT NULL,
            `resource_capture_datasets` varchar(255) DEFAULT '',
            `calibration_object_used` varchar(255) DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_dataset_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
          )";
          break;
        case 'capture_dataset_right':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_data_file` (
            `capture_data_file_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `capture_data_element_repository_id` int(11),
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
            `capture_data_element_repository_id` int(11),
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
            `capture_device_repository_id` int(11),
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
        case 'capture_method':
          $sql = "CREATE TABLE IF NOT EXISTS `capture_method` (
            `capture_method_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_method_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
          )";
          break;
        case 'data_rights_restriction_type':
          $sql = "CREATE TABLE IF NOT EXISTS `data_rights_restriction_type` (
            `data_rights_restriction_types_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`data_rights_restriction_types_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'dataset_type':
          $sql = "CREATE TABLE IF NOT EXISTS `dataset_type` (
            `dataset_type_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`dataset_type_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'focus_type':
          $sql = "CREATE TABLE IF NOT EXISTS `focus_type` (
            `focus_type_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`focus_type_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'isni_data':
          $sql = $this->connection->prepare("CREATE TABLE IF NOT EXISTS `isni_data` (
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
        case 'item_position_type':
          $sql = "CREATE TABLE IF NOT EXISTS `item_position_type` (
            `item_position_type_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `label_alias` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`item_position_type_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'item_type':
          $sql = "CREATE TABLE IF NOT EXISTS `item_type` (
            `item_type_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`item_type_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'item':
          $sql = "CREATE TABLE IF NOT EXISTS `item` (
            `item_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `subject_repository_id` int(11) NOT NULL,
            `local_item_id` varchar(255) DEFAULT '',
            `item_guid` varchar(255) DEFAULT '',
            `item_description` mediumtext,
            `item_type` varchar(255) DEFAULT NULL,
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            `status_type_repository_id` int(11) NOT NULL DEFAULT '0',
            PRIMARY KEY (`item_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
            KEY `item_guid` (`item_guid`,`subject_repository_id`) )";
          break;
        case 'light_source_type':
          $sql = "CREATE TABLE IF NOT EXISTS `light_source_type` (
            `light_source_type_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label_field_name_raw` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`light_source_type_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'model':
          $sql = "CREATE TABLE IF NOT EXISTS `model` (
            `model_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `capture_dataset_repository_id` int(11),
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
            `capture_dataset_repository_id` int(11),
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
            `photogrammetry_scale_bar_repository_id` int(11),
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
        case 'project':
          $sql = "CREATE TABLE IF NOT EXISTS `project` (
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
        case 'scale_bar_barcode_type':
          $sql = "CREATE TABLE IF NOT EXISTS `scale_bar_barcode_type` (
            `scale_bar_barcode_types_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`scale_bar_barcode_types_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'status_type':
          $sql = "CREATE TABLE IF NOT EXISTS `status_type` (
            `status_type_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`status_type_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'subject':
          $sql = "CREATE TABLE IF NOT EXISTS `subject` (
            `subject_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `project_repository_id` int(11) NOT NULL,
            `local_subject_id` varchar(255) DEFAULT '',
            `subject_guid` varchar(255) DEFAULT '',
            `subject_name` varchar(255) DEFAULT '',
            `holding_entity_name` varchar(255) DEFAULT '',
            `holding_entity_guid` varchar(255) DEFAULT '',
            `date_created` varchar(255) NOT NULL DEFAULT '',
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`subject_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`),
            KEY `project_repository_id` (`project_repository_id`,`subject_name`)
          )";
          break;
        case 'target_type':
          $sql = "CREATE TABLE IF NOT EXISTS `target_type` (
            `target_types_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`target_types_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'unit_stakeholder':
          $sql = "CREATE TABLE IF NOT EXISTS `unit_stakeholder` (
            `unit_stakeholder_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `isni_id` varchar(255) DEFAULT NULL,
            `unit_stakeholder_label` varchar(255) NOT NULL DEFAULT '',
            `unit_stakeholder_label_aliases` text DEFAULT NULL,
            `unit_stakeholder_full_name` varchar(255) NOT NULL DEFAULT '',
            `unit_stakeholder_guid` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`unit_stakeholder_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ";
          break;
        case 'unit':
          $sql = "CREATE TABLE IF NOT EXISTS `unit` (
            `units_id` int(11) NOT NULL AUTO_INCREMENT,
            `label` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`units_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            )";
          break;
        case 'uv_map':
          $sql = "CREATE TABLE IF NOT EXISTS `uv_map` (
            `uv_map_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `capture_dataset_repository_id` int(11),
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
        return;
        // die('CREATE TABLE `' . $table_name . '` failed. Table name not recognized. Could not build SQL for CREATE statement.');
      }

      $statement = $this->connection->prepare($sql . " ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='This table stores " . $table_name . " metadata'");
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