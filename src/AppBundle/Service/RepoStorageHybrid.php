<?php

namespace AppBundle\Service;

use Doctrine\DBAL\Driver\Connection;

use PDO;

class RepoStorageHybrid implements RepoStorage {

  private $connection;

  public function __construct($connection) {
    $this->connection = $connection;
  }

  /**
   * ----------------------------------------------------------------
   * Getters for single records.
   * ----------------------------------------------------------------
   */

  /***
   * @param $params
   * @return mixed
   */
  public function getProject($params) {
    //$params will be something like array('project_repository_id' => '123');
    $return_data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'project',
      'search_params' => array(
        0 => array('field_names' => array('project.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('project.project_repository_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_repository_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'project_name',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'stakeholder_guid',
    );
    $query_params['fields'][] = array(
      'field_name' => 'project_description',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'date_created',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'created_by_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'last_modified_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'isni_data',
      'field_name' => 'isni_label',
      'field_alias' => 'stakeholder_label',
    );
    $query_params['fields'][] = array(
      'table_name' => 'unit_stakeholder',
      'field_name' => 'unit_stakeholder_repository_id',
      //'field_alias' => 'stakeholder_guid',
    );

    // Joins.
    $query_params['related_tables'][] = array(
      'table_name' => 'isni_data',
      'table_join_field' => 'isni_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'project',
      'base_join_field' => 'stakeholder_guid',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'unit_stakeholder',
      'table_join_field' => 'isni_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'project',
      'base_join_field' => 'stakeholder_guid',
    );

    $query_params['records_values'] = array();
    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;

  }

  public function getSubject($params) {
    //$params will be something like array('subject_repository_id' => '123');
    $return_data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'subject',
      'search_params' => array(
        0 => array('field_names' => array('subject.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('subject.subject_repository_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND'
    );

    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getItem($params) {
      //$params will be something like array('item_repository_id' => '123');
    $return_data = array();

      $query_params = array(
        'fields' => array(),
        'base_table' => 'item',
        'search_params' => array(
          0 => array('field_names' => array('item.active'), 'search_values' => array(1), 'comparison' => '='),
          1 => array('field_names' => array('item.item_repository_id'), 'search_values' => $params, 'comparison' => '=')
        ),
        'search_type' => 'AND',
        'related_tables' => array(),
      );

      // Fields.
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'item_guid',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'local_item_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'item_description',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'item_type',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'status_type_repository_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'last_modified',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'item_repository_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item_type',
        'field_name' => 'label',
        'field_alias' => 'item_type_label',
      );

      // Joins.
      $query_params['related_tables'][] = array(
        'table_name' => 'item_type',
        'table_join_field' => 'item_type_repository_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'item',
        'base_join_field' => 'item_type',
      );

      $query_params['records_values'] = array();
      $ret = $this->getRecords($query_params);
      //@todo do something if $ret has errors

      if(array_key_exists(0, $ret)) {
        $return_data = $ret[0];
      }
      return $return_data;
  }

  public function getModel($params) {
    //$params will be something like array('model_repository_id' => '123');
    $return_data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'model',
      'search_params' => array(
        0 => array('field_names' => array('model.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('model.model_repository_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'model_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'parent_capture_dataset_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'model_guid',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'date_of_creation',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'model_file_type',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'derived_from',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'creation_method',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'model_modality',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'units',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'is_watertight',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'model_purpose',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'point_count',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'has_normals',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'face_count',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'vertices_count',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'has_vertex_color',
    );

    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'has_uv_space',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'model_maps',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'parent_item_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'subject_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'project_repository_id',
    );

    // Joins.
    $query_params['related_tables'][] = array(
      'table_name' => 'capture_dataset',
      'table_join_field' => 'capture_dataset_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'model',
      'base_join_field' => 'parent_capture_dataset_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'item',
      'table_join_field' => 'item_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'parent_item_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'subject',
      'table_join_field' => 'subject_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'subject_repository_id',
    );

    $query_params['records_values'] = array();
    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getCaptureDataset($params) {
    //$params will be something like array('capture_dataset_repository_id' => '123');
    $return_data = array();

    $capture_dataset_repository_id = array_key_exists('capture_dataset_repository_id', $params) ? $params['capture_dataset_repository_id'] : NULL;
    $sql = "SELECT
          capture_dataset.capture_dataset_guid
          ,capture_dataset.capture_dataset_field_id
          ,capture_dataset.capture_method
          ,capture_dataset.capture_dataset_type
          ,capture_dataset.capture_dataset_name
          ,capture_dataset.collected_by
          ,capture_dataset.date_of_capture
          ,capture_dataset.capture_dataset_description
          ,capture_dataset.collection_notes
          ,capture_dataset.support_equipment
          ,capture_dataset.item_position_type
          ,capture_dataset.item_position_field_id
          ,capture_dataset.item_arrangement_field_id
          ,capture_dataset.positionally_matched_capture_datasets
          ,capture_dataset.focus_type
          ,capture_dataset.light_source_type
          ,capture_dataset.background_removal_method
          ,capture_dataset.cluster_type
          ,capture_dataset.cluster_geometry_field_id
          ,capture_dataset.resource_capture_datasets
          ,capture_dataset.calibration_object_used
          ,capture_dataset.date_created
          ,capture_dataset.created_by_user_account_id
          ,capture_dataset.last_modified
          ,capture_dataset.last_modified_user_account_id
          ,capture_method.label AS capture_method_label
          ,dataset_type.label AS capture_dataset_type_label
          ,item_position_type.label_alias AS item_position_type_label
          ,focus_type.label AS focus_type_label
          ,light_source_type.label AS light_source_type_label
          ,background_removal_method.label AS background_removal_method_label
          ,camera_cluster_type.label AS camera_cluster_type_label
        FROM capture_dataset
        LEFT JOIN capture_method ON capture_method.capture_method_repository_id = capture_dataset.capture_method
        LEFT JOIN dataset_type ON dataset_type.dataset_type_repository_id = capture_dataset.capture_dataset_type
        LEFT JOIN item_position_type ON item_position_type.item_position_type_repository_id = capture_dataset.item_position_type
        LEFT JOIN focus_type ON focus_type.focus_type_repository_id = capture_dataset.focus_type
        LEFT JOIN light_source_type ON light_source_type.light_source_type_repository_id = capture_dataset.light_source_type
        LEFT JOIN background_removal_method ON background_removal_method.background_removal_method_repository_id = capture_dataset.background_removal_method
        LEFT JOIN camera_cluster_type ON camera_cluster_type.camera_cluster_type_repository_id = capture_dataset.cluster_type
        WHERE capture_dataset.active = 1
        AND capture_dataset.capture_dataset_repository_id = :capture_dataset_repository_id";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, PDO::PARAM_INT);
    $statement->execute();
    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getCaptureDevice($params) {
    //$params will be something like array('capture_device_repository_id' => '123');
    $return_data = array();

    $capture_device_repository_id = array_key_exists('capture_device_repository_id', $params) ? $params['capture_device_repository_id'] : NULL;
    $sql = "SELECT
              capture_device.capture_device_repository_id,
              capture_device.parent_capture_data_element_repository_id,
              capture_device.calibration_file,
              capture_device.capture_device_component_ids,
              capture_data_element.capture_dataset_repository_id,
              capture_dataset.parent_item_repository_id,
              item.subject_repository_id,
              subject.project_repository_id
            FROM capture_device
            LEFT JOIN capture_data_element ON capture_data_element.capture_data_element_repository_id = capture_device.parent_capture_data_element_repository_id
            LEFT JOIN capture_dataset ON capture_dataset.capture_dataset_repository_id = capture_data_element.capture_dataset_repository_id
            LEFT JOIN item ON item.item_repository_id = capture_dataset.parent_item_repository_id
            LEFT JOIN subject ON item.subject_repository_id =subject.subject_repository_id
            WHERE capture_device.active = 1
            AND capture_device.capture_device_repository_id = :capture_device_repository_id";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":capture_device_repository_id", $capture_device_repository_id, PDO::PARAM_INT);
    $statement->execute();
    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getPhotogrammetryScaleBarTargetPair($params) {
    //$params will be something like array('photogrammetry_scale_bar_target_pair_repository_id' => '123');
    $return_data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'photogrammetry_scale_bar_target_pair',
      'search_params' => array(
        0 => array('field_names' => array('photogrammetry_scale_bar_target_pair.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('photogrammetry_scale_bar_target_pair.photogrammetry_scale_bar_target_pair_repository_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'photogrammetry_scale_bar_target_pair_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'parent_photogrammetry_scale_bar_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'target_type',
    );
    $query_params['fields'][] = array(
      'field_name' => 'target_pair_1_of_2',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'target_pair_2_of_2',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'distance',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'units',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'subject_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'project_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'created_by_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'last_modified_user_account_id',
    );

    // Joins.
    $query_params['related_tables'][] = array(
      'table_name' => 'photogrammetry_scale_bar',
      'table_join_field' => 'photogrammetry_scale_bar_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'photogrammetry_scale_bar_target_pair',
      'base_join_field' => 'parent_photogrammetry_scale_bar_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'capture_dataset',
      'table_join_field' => 'capture_dataset_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'photogrammetry_scale_bar',
      'base_join_field' => 'parent_capture_dataset_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'item',
      'table_join_field' => 'item_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'parent_item_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'subject',
      'table_join_field' => 'subject_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'subject_repository_id',
    );

    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getPhotogrammetryScaleBar($params) {
    //$params will be something like array('photogrammetry_scale_bar_repository_id' => '123');
    $return_data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'photogrammetry_scale_bar',
      'search_params' => array(
        0 => array('field_names' => array('photogrammetry_scale_bar.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('photogrammetry_scale_bar.photogrammetry_scale_bar_repository_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    /*
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'parent_photogrammetry_scale_bar_repository_id',
    );
    */
    $query_params['fields'][] = array(
      'field_name' => 'photogrammetry_scale_bar_repository_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'parent_capture_dataset_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar',
      'field_name' => 'scale_bar_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'scale_bar_manufacturer',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar',
      'field_name' => 'scale_bar_barcode_type',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar',
      'field_name' => 'scale_bar_target_pairs',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'parent_item_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'subject_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'project_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar',
      'field_name' => 'created_by_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar',
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar',
      'field_name' => 'last_modified_user_account_id',
    );

    // Joins.
    $query_params['related_tables'][] = array(
      'table_name' => 'capture_dataset',
      'table_join_field' => 'capture_dataset_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'photogrammetry_scale_bar',
      'base_join_field' => 'parent_capture_dataset_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'item',
      'table_join_field' => 'item_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'parent_item_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'subject',
      'table_join_field' => 'subject_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'subject_repository_id',
    );

    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getRecordById($params) {

    $record_type = array_key_exists('record_type', $params) ? $params['record_type'] : NULL;
    $record_id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    if(NULL == $record_type || NULL == $record_id) {
      return array();
    }

    $data = $this->getRecord(array(
      'base_table' => $record_type,
      'id_field' => $record_type . '_repository_id',
      'id_value' => $record_id));
    return $data;
  }

  public function getIsniRecordById($params) {

    $record_id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    $data = $this->getRecord(array(
      'base_table' => 'isni_data',
      'id_field' => 'isni_id',
      'id_value' => $record_id));
    return $data;
  }

  public function getStakeholderByIsniId($params) {

    $isni_id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    if(NULL == $isni_id) {
      return array();
    }

    $query_params = array(
      'fields' => array(),
      'base_table' => 'unit_stakeholder',
      'search_params' => array(
        0 => array(
          'field_names' => array('active'),
          'search_values' => array(1),
          'comparison' => '='
        ),
      ),
      'search_type' => 'AND',
    );

    $query_params['search_params'][1] = array(
      'field_names' => array(
        'isni_id',
      ),
      'search_values' => array(
        (int)$isni_id
      ),
      'comparison' => '=',
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'unit_stakeholder',
      'field_name' => 'unit_stakeholder_repository_id',
    );

    $query_params['records_values'] = array();

    $return_data = array();
    $ret = $this->getRecords($query_params);

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;

  }

  /**
   * ----------------------------------------------------------------
   * Getters for multiple records.
   * ----------------------------------------------------------------
   */

  public function getDatasets($params) {

    $item_repository_id = array_key_exists('item_repository_id', $params) ? $params['item_repository_id'] : NULL;

    $query_params = array(
      'fields' => array(),
      'base_table' => 'capture_dataset',
      'search_params' => array(
        0 => array('field_names' => array('capture_dataset.active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    if($item_repository_id && is_numeric($item_repository_id)) {
      $query_params['search_params'][1] = array(
        'field_names' => array(
          'capture_dataset.parent_item_repository_id',
        ),
        'search_values' => array((int)$item_repository_id),
        'comparison' => '=',
      );
    }

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => '*',
    );

    // Joins.
    $query_params['related_tables'][] = array(
      'table_name' => 'item',
      'table_join_field' => 'item_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'parent_item_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'subject',
      'table_join_field' => 'subject_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'subject_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'project',
      'table_join_field' => 'project_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'subject',
      'base_join_field' => 'project_repository_id',
    );

    $query_params['records_values'] = array();
    $return_data = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    return $return_data;
  }

  public function getElementsForCaptureDataset($params) {

      $capture_dataset_repository_id = array_key_exists('capture_dataset_repository_id', $params) ? $params['capture_dataset_repository_id'] : NULL;
      $sql = "SELECT
                project.project_repository_id,
                subject.subject_repository_id,
                item.item_repository_id,
                capture_data_element.capture_data_element_repository_id,
                capture_data_element.capture_dataset_repository_id,
                capture_data_element.capture_device_configuration_id,
                capture_data_element.capture_device_field_id,
                capture_data_element.capture_sequence_number,
                capture_data_element.cluster_position_field_id,
                capture_data_element.position_in_cluster_field_id,
                capture_data_element.date_created,
                capture_data_element.created_by_user_account_id,
                capture_data_element.last_modified,
                capture_data_element.last_modified_user_account_id,
                capture_data_element.active
            FROM capture_data_element
            LEFT JOIN capture_dataset ON capture_dataset.capture_dataset_repository_id = capture_data_element.capture_dataset_repository_id
            LEFT JOIN item ON item.item_repository_id = capture_dataset.parent_item_repository_id
            LEFT JOIN subject ON subject.subject_repository_id = item.subject_repository_id
            LEFT JOIN project ON project.project_repository_id = subject.project_repository_id
            WHERE capture_data_element.active = 1
            AND capture_data_element.capture_dataset_repository_id = :capture_dataset_repository_id";

      $statement = $this->connection->prepare($sql);

      $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getItemsBySubjectId($params) {
    //$params will be something like array('subject_repository_id' => '123');

    $subject_repository_id = array_key_exists('subject_repository_id', $params) ? $params['subject_repository_id'] : NULL;
    $query_params = array(
      'base_table' => 'item',
      'related_tables' => array(
        0 =>
          array(
            'table_name' => 'subject',
            'table_join_field' => 'subject_repository_id',
            'join_type' => 'LEFT JOIN',
            'base_join_table' => 'item',
            'base_join_field' => 'subject_repository_id',
          ),
        1 => array(
          'table_name' => 'project',
          'table_join_field' => 'project_repository_id',
          'join_type' => 'LEFT JOIN',
          'base_join_table' => 'subject',
          'base_join_field' => 'project_repository_id',
        )
      ),
      'fields' => array(
        0 => array(
          'table_name' => 'project',
          'field_name' => 'project_repository_id',
        ),
        1 => array(
          'table_name' => 'subject',
          'field_name' => 'subject_repository_id',
        ),
        2 => array(
          'table_name' => 'item',
          'field_name' => 'item_repository_id',
        ),
        3 => array(
          'table_name' => 'item',
          'field_name' => 'item_guid',
        ),
        4 => array(
          'table_name' => 'item',
          'field_name' => 'subject_repository_id',
        ),
        5 => array(
          'table_name' => 'item',
          'field_name' => 'local_item_id',
        ),
        6 => array(
          'table_name' => 'item',
          'field_name' => 'item_description',
        ),
        7 => array(
          'table_name' => 'item',
          'field_name' => 'date_created',
        ),
        8 => array(
          'table_name' => 'item',
          'field_name' => 'created_by_user_account_id',
        ),
        9 => array(
          'table_name' => 'item',
          'field_name' => 'last_modified',
        ),
        10 => array(
          'table_name' => 'item',
          'field_name' => 'last_modified_user_account_id',
        ),
        11 => array(
          'table_name' => 'item',
          'field_name' => 'active',
        ),
        12 => array(
          'table_name' => 'item',
          'field_name' => 'status_type_repository_id',
        ),
      ),
      'sort_fields' => array(
        0 => array('field_name' => 'item.local_item_id')
      ),
      'search_params' => array(
        0 => array('field_names' => array('item.active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND'
    );

    if($subject_repository_id) {
      $query_params['search_params'][1] = array('field_names' => array('item.subject_repository_id'), 'search_values' => array($subject_repository_id), 'comparison' => '=');
    }

    $query_params['records_values'] = array();
    $return_data = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    return $return_data;
  }

  public function getItemGuidsBySubjectId($params) {
    //$params will be something like array('subject_repository_id' => '123');

    $subject_repository_id = array_key_exists('subject_repository_id', $params) ? $params['subject_repository_id'] : NULL;
    $query_params = array(
      'base_table' => 'item',
      'fields' => array(
        0 => array(
          'table_name' => 'item',
          'field_name' => 'item_guid',
        ),
      ),
      'search_params' => array(
        0 => array('field_names' => array('item.active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND'
    );

    if($subject_repository_id) {
      $query_params['search_params'][1] = array('field_names' => array('item.subject_repository_id'), 'search_values' => array($subject_repository_id), 'comparison' => '=');
    }

    $query_params['records_values'] = array();
    $return_data = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    return $return_data;
  }

  public function getStakeholderGuids() {
    $sql = "
      SELECT project.project_repository_id
          ,project.stakeholder_guid
          ,isni_data.isni_label AS stakeholder_label
      FROM project
      LEFT JOIN isni_data ON isni_data.isni_id = project.stakeholder_guid
      GROUP BY isni_data.isni_label
      ORDER BY isni_data.isni_label ASC";

    $statement = $this->connection->prepare($sql);

    $statement->execute();
    $records_values = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $records_values;

  }

  /**
   * ----------------------------------------------------------------
   * Delete for single record.
   * ----------------------------------------------------------------
   */
  /**
   * @param $params
   * @return array|mixed
   */
  public function deleteRecordById($params) {

    $record_type = array_key_exists('record_type', $params) ? $params['record_type'] : NULL;
    $record_id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    if(NULL == $record_type || NULL == $record_id) {
      return array();
    }

    $data = $this->deleteRecords(array(
      'base_table' => $record_type,
      'search_params' => array(
        'field_names' => array($record_type . '_repository_id'),
        'search_values' => array($record_id)
        ),
      )
    );
    return $data;
  }

  /**
   * ----------------------------------------------------------------
   * Datatables queries- returns rows needed for rendering client-side tables
   * ----------------------------------------------------------------
   */

  /**
   * Generic function for getting datatable data.
   * @param $params
   * @return mixed
   */
  public function getDatatable($params) {

    $record_type = array_key_exists('record_type', $params) ? $params['record_type'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    $parent_id_field = array_key_exists('parent_id_field', $params) ? $params['parent_id_field'] : NULL;
    $parent_id = array_key_exists('parent_id', $params) ? $params['parent_id'] : NULL;

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    //@todo- allow match on ID- specify ID field and value $record_match = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;

    $query_params = array(
      'distinct' => true, // @todo Do we always want this to be true?
      'base_table' => $record_type,
      'fields' => array(),
    );

    $query_params['limit'] = array(
      'limit_start' => $start_record,
      'limit_stop' => $stop_record,
    );

    if (!empty($sort_field) && !empty($sort_order)) {
      $query_params['sort_fields'][] = array(
        'field_name' => $sort_field,
        'sort_order' => $sort_order,
      );
    } else {
      $query_params['sort_fields'][] = array(
        'field_name' => $record_type . '.last_modified',
        'sort_order' => 'DESC',
      );
    }
    $query_params['search_type'] = 'AND';


    switch($record_type) {
      case 'capture_data_file':
        //@todo is this case used?
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'capture_data_file_name',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'capture_data_file_type',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'is_compressed_multiple_files',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );
        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');

        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.active',
              $record_type . '.data_rights_restriction',
              $record_type . '.start_date',
              $record_type . '.end_date',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        if (NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              'parent_capture_data_element_repository_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'capture_dataset_rights':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );

        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'data_rights_restriction',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'start_date',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'end_date',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );
        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');

        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.active',
              $record_type . '.data_rights_restriction',
              $record_type . '.start_date',
              $record_type . '.end_date',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        if (NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              'parent_capture_dataset_repository_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'capture_device':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'calibration_file',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'capture_device_component_ids',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][] = array(
            'field_names' => array(
              $record_type . '.calibration_file',
              $record_type . '.capture_device_component_ids',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        if (isset($parent_id_field) && NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              $parent_id_field,
            ),
            'search_values' => array(
              $parent_id
            ),
            'comparison' => '=',
          );
        }
        break;

      case 'capture_device_component':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'serial_number',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'capture_device_component_type',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'manufacturer',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'model_name',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.serial_number',
              $record_type . '.capture_device_component_type',
              $record_type . '.manufacturer',
              $record_type . '.model_name',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        if (NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              'parent_capture_device_repository_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'item_position_type':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'label',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'label_alias',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              'label',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }

        break;

      case 'model':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'model_guid',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'date_of_creation',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'model_file_type',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'derived_from',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'creation_method',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'model_modality',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'units',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'is_watertight',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'model_purpose',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'point_count',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'has_normals',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'face_count',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'vertices_count',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'has_vertex_color',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'has_uv_space',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'model_maps',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array(
          'field_names' => array($record_type . '.active'),
          'search_values' => array(1),
          'comparison' => '='
        );
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.model_guid',
              $record_type . '.date_of_creation',
              $record_type . '.model_file_type',
              $record_type . '.derived_from',
              $record_type . '.creation_method',
              $record_type . '.model_modality',
              $record_type . '.units',
              $record_type . '.is_watertight',
              $record_type . '.model_purpose',
              $record_type . '.point_count',
              $record_type . '.has_normals',
              $record_type . '.face_count',
              $record_type . '.vertices_count',
              $record_type . '.has_vertex_color',
              $record_type . '.has_uv_space',
              $record_type . '.model_maps',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        if (isset($parent_id_field) && NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              $parent_id_field,
            ),
            'search_values' => array(
              $parent_id
            ),
            'comparison' => '=',
          );
        }
        break;

      case 'processing_action':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'parent_model_repository_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'preceding_processing_action_repository_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'date_of_action',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'action_method',
        );
        $query_params['fields'][] = array(
          'field_name' => 'software_used',
        );
        $query_params['fields'][] = array(
          'field_name' => 'action_description',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.preceding_processing_action_repository_id',
              $record_type . '.action_method',
              $record_type . '.action_description',
              $record_type . '.software_used',
              $record_type . '.last_modified'
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        if (NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              'parent_model_repository_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'photogrammetry_scale_bar':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'scale_bar_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'scale_bar_manufacturer',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'scale_bar_barcode_type',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'scale_bar_target_pairs',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.scale_bar_id',
              $record_type . '.scale_bar_manufacturer',
              $record_type . '.scale_bar_barcode_type',
              $record_type . '.scale_bar_target_pairs',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        if (NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              'parent_capture_dataset_repository_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'photogrammetry_scale_bar_target_pair':

        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'target_type',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'target_pair_1_of_2',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'target_pair_2_of_2',
        );
        $query_params['fields'][] = array(
          'field_name' => 'distance',
        );
        $query_params['fields'][] = array(
          'field_name' => 'units',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');

        if (NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              'parent_photogrammetry_scale_bar_repository_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }

        break;

      case 'project':
        $query_params['related_tables'][] = array(
          'table_name' => 'isni_data',
          'table_join_field' => 'isni_id',
          'join_type' => 'LEFT JOIN',
          'base_join_table' => 'project',
          'base_join_field' => 'stakeholder_guid',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'project_repository_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'project_name',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'stakeholder_guid',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'date_created',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );
        $query_params['fields'][] = array(
          'table_name' => 'isni_data',
          'field_name' => 'isni_label',
          'field_alias' => 'stakeholder_label',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.project_name',
              'isni_data.isni_label',
              $record_type . '.date_created',
              $record_type . '.last_modified',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        break;

      case 'unit_stakeholder':

        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'unit_stakeholder_label',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'unit_stakeholder_full_name',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.unit_stakeholder_label',
              $record_type . '.unit_stakeholder_full_name',
              $record_type . '.last_modified'
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
      break;

      case 'uv_map':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'map_type',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'map_file_type',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'map_size',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.map_type',
              $record_type . '.map_file_type',
              $record_type . '.map_size',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }
        if (NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              'parent_capture_dataset_repository_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      default:
        // Handles any case where we only search the label field,
        // and we only return the 5 fields specified below.

        /*
          camera_cluster_type
          capture_method
          dataset_type
          data_rights_restriction_type
          focus_type
          item_type
          light_source_type
          scale_bar_barcode_type
          status_type
          target_type
          unit
        */
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'label',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_repository_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.label',
            ),
            'search_values' => array($search_value),
            'comparison' => 'LIKE',
          );
        }

        break;


    }

    $data = $this->getRecordsDatatable($query_params);
    return $data;

  }

  /**
   * Generic function for getting datatable data.
   * @param $params
   * @return mixed
   */
  public function getDatatableProject($params) {

    $record_type = 'project';
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $date_range_start = array_key_exists('date_range_start', $params) ? $params['date_range_start'] : NULL;
    $date_range_end = array_key_exists('date_range_end', $params) ? $params['date_range_end'] : NULL;

    $query_params = array(
      'distinct' => true, // @todo Do we always want this to be true?
      'base_table' => $record_type,
      'fields' => array(),
    );

    $query_params['limit'] = array(
      'limit_start' => $start_record,
      'limit_stop' => $stop_record,
    );

    if (!empty($sort_field) && !empty($sort_order)) {
      $query_params['sort_fields'][] = array(
        'field_name' => $sort_field,
        'sort_order' => $sort_order,
      );
    } else {
      $query_params['sort_fields'][] = array(
        'field_name' => $record_type . '.last_modified',
        'sort_order' => 'DESC',
      );
    }

    $query_params['related_tables'][] = array(
      'table_name' => 'isni_data',
      'table_join_field' => 'isni_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'project',
      'base_join_field' => 'stakeholder_guid',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => $record_type . '_repository_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => 'project_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => 'project_name',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => 'stakeholder_guid',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => 'date_created',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => 'active',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => $record_type . '_repository_id',
      'field_alias' => 'DT_RowId',
    );
    $query_params['fields'][] = array(
      'table_name' => 'isni_data',
      'field_name' => 'isni_label',
      'field_alias' => 'stakeholder_label',
    );

    $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
    $query_params['search_type'] = 'AND';
    if (NULL !== $search_value) {
      $query_params['search_params'][1] = array(
        'field_names' => array(
          $record_type . '.project_name',
          'isni_data.isni_label',
          $record_type . '.date_created',
          $record_type . '.last_modified',
        ),
        'search_values' => array($search_value),
        'comparison' => 'LIKE',
      );
    }

    if(NULL !== $date_range_start) {
      $c = count($query_params['search_params']);
      $query_params['search_params'][$c] = array(
        'field_names' => array('project.last_modified'),
        'search_values' => array($date_range_start),
        'comparison' => '<',
      );
    }
    if(NULL !== $date_range_end) {
      $c = count($query_params['search_params']);
      $query_params['search_params'][$c] = array(
        'field_names' => array('project.last_modified'),
        'search_values' => array($date_range_end),
        'comparison' => '>',
      );
    }

    $data = $this->getRecordsDatatable($query_params);
    return $data;

  }

  /**
   * @param $params
   * @return mixed
   */
  public function getDatatableSubject($params) {

      $project_repository_id = array_key_exists('project_repository_id', $params) ? $params['project_repository_id'] : NULL;
      $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
      $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
      $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
      $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
      $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

      $query_params = array(
        'fields' => array(),
        'base_table' => 'subject',
        'distinct' => true,
        'search_params' => array(
          0 => array('field_names' => array('subject.active'), 'search_values' => array(1), 'comparison' => '='),
        ),
        'search_type' => 'AND',
      );
      $query_params['limit'] = array(
        'limit_start' => $start_record,
        'limit_stop' => $stop_record,
      );

      $query_params['related_tables'][] = array(
        'table_name' => 'item',
        'table_join_field' => 'subject_repository_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'subject',
        'base_join_field' => 'subject_repository_id',
      );

      if ($search_value) {
        $query_params['search_params'][1] = array(
          'field_names' => array(
            'subject.subject_name',
            'subject.holding_entity_guid',
            'subject.last_modified',
          ),
          'search_values' => array($search_value),
          'comparison' => 'LIKE',
        );
      }

      if($project_repository_id && is_numeric($project_repository_id)) {
        $count_params = count($query_params['search_params']);
        $query_params['search_params'][$count_params] = array(
          'field_names' => array(
            'subject.project_repository_id',
          ),
          'search_values' => array((int)$project_repository_id),
          'comparison' => '=',
        );
      }

      // Fields.
      $query_params['fields'][] = array(
        'table_name' => 'subject',
        'field_name' => 'subject_repository_id',
        'field_alias' => 'manage',
      );
      $query_params['fields'][] = array(
        'table_name' => 'subject',
        'field_name' => 'subject_repository_id',
      );
      $query_params['fields'][] = array(
        'field_name' => 'holding_entity_guid',
      );
      $query_params['fields'][] = array(
        'field_name' => 'local_subject_id',
      );
      $query_params['fields'][] = array(
        'field_name' => 'subject_guid',
      );
      $query_params['fields'][] = array(
        'field_name' => 'subject_name',
      );
      $query_params['fields'][] = array(
        'table_name' => 'subject',
        'field_name' => 'last_modified',
      );
      $query_params['fields'][] = array(
        'table_name' => 'subject',
        'field_name' => 'active',
      );
      $query_params['fields'][] = array(
        'table_name' => 'subject',
        'field_name' => 'subject_repository_id',
        'field_alias' => 'DT_RowId',
      );

      $query_params['records_values'] = array();

      if (!empty($sort_field) && !empty($sort_order)) {
        $query_params['sort_fields'][] = array(
          'field_name' => $sort_field,
          'sort_order' => $sort_order,
        );
      } else {
        $query_params['sort_fields'][] = array(
          'field_name' => 'subject.last_modified',
          'sort_order' => 'DESC',
        );
      }

      $data = $this->getRecordsDatatable($query_params);
      return $data;

  }

  /**
   * @param $params
   * @return mixed
   */
  public function getDatatableSubjectItem($params) {

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    // GROUP BY subjects.holding_entity_guid, subjects.local_subject_id, subjects.subject_guid, subjects.subject_name, subjects.last_modified, subjects.active, subjects.subject_repository_id
    $query_params = array(
      'distinct' => true,
      'base_table' => 'subject',
      'fields' => array(),
      'search_params' => array(
        0 => array('field_names' => array('subject.active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND',
    );

    $query_params['related_tables'][] = array(
      'table_name' => 'item',
      'table_join_field' => 'subject_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'subject',
      'base_join_field' => 'subject_repository_id',
    );
    if ($search_value) {
      $query_params['search_params'][1] = array(
        'field_names' => array(
          'subject.subject_name',
          'subject.holding_entity_guid',
          'subject.last_modified'
        ),
        'search_values' => array($search_value),
        'comparison' => 'LIKE',
      );
    }

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_repository_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_repository_id',
      'field_alias' => 'DT_RowId',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_repository_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'holding_entity_guid',
    );
    $query_params['fields'][] = array(
      'field_name' => 'local_subject_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'subject_guid',
    );
    $query_params['fields'][] = array(
      'field_name' => 'subject_name',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'active',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'last_modified',
    );
    $query_params['records_values'] = array();

    $query_params['limit'] = array(
      'limit_start' => $start_record,
      'limit_stop' => $stop_record,
    );

    if (!empty($sort_field) && !empty($sort_order)) {
      $query_params['sort_fields'][] = array(
        'field_name' => $sort_field,
        'sort_order' => $sort_order,
      );
    } else {
      $query_params['sort_fields'][] = array(
        'field_name' => 'subject.last_modified',
        'sort_order' => 'DESC',
      );
    }

    $data = $this->getRecordsDatatable($query_params);

    return $data;
  }

  /**
   * @param $params
   * @return mixed
   */
  public function getDatatableCaptureDataFile($params) {

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;
    $parent_id = array_key_exists('parent_id', $params) ? $params['parent_id'] : NULL;

    $query_params = array(
      'fields' => array(),
      'distinct' => true,
      'base_table' => 'capture_data_file',
      'search_params' => array(
        0 => array('field_names' => array('capture_data_file.active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND',
    );

    if ($search_value) {
      $query_params['search_params'][] = array(
        'field_names' => array(
          'capture_data_file.capture_data_file_name',
          'capture_data_file.capture_data_file_type',
          'capture_data_file.is_compressed_multiple_files'
        ),
        'search_values' => array($search_value),
        'comparison' => 'LIKE',
      );
    }
    if ($parent_id) {
      $query_params['search_params'][] = array(
        'field_names' => array(
          'parent_capture_data_element_repository_id'
        ),
        'search_values' => array($parent_id),
        'comparison' => '=',
      );
    }

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'capture_data_file',
      'field_name' => 'capture_data_file_repository_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_data_file',
      'field_name' => 'capture_data_file_repository_id',
      'field_alias' => 'DT_RowId',
    );
    $query_params['fields'][] = array(
      'field_name' => 'capture_data_file_name',
    );
    $query_params['fields'][] = array(
      'field_name' => 'capture_data_file_type',
    );
    $query_params['fields'][] = array(
      'field_name' => 'is_compressed_multiple_files',
    );
    $query_params['fields'][] = array(
      'field_name' => 'active',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_data_file',
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_data_file',
      'field_name' => 'last_modified_user_account_id',
    );

    $query_params['records_values'] = array();

    $query_params['limit'] = array(
      'limit_start' => $start_record,
      'limit_stop' => $stop_record,
    );

    if (!empty($sort_field) && !empty($sort_order)) {
      $query_params['sort_fields'][] = array(
        'field_name' => $sort_field,
        'sort_order' => $sort_order,
      );
    } else {
      $query_params['sort_fields'][] = array(
        'field_name' => 'capture_data_file.last_modified',
        'sort_order' => 'DESC',
      );
    }

    $data = $this->getRecordsDatatable($query_params);

    return $data;

  }

  /**
   * @param $params
   * @return mixed
   */
  public function getDatatableCaptureDataset($params) {

    $item_repository_id = array_key_exists('item_repository_id', $params) ? $params['item_repository_id'] : NULL;
    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    $query_params = array(
      'fields' => array(),
      'base_table' => 'capture_dataset',
      'search_params' => array(
        0 => array('field_names' => array('capture_dataset.active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND',
    );

    $query_params['related_tables'][] = array(
      'table_name' => 'capture_method',
      'table_join_field' => 'capture_method_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'capture_method',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'dataset_type',
      'table_join_field' => 'dataset_type_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'capture_dataset_type',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'item_position_type',
      'table_join_field' => 'item_position_type_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'item_position_type',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'focus_type',
      'table_join_field' => 'focus_type_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'focus_type',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'light_source_type',
      'table_join_field' => 'light_source_type_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'light_source_type',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'background_removal_method',
      'table_join_field' => 'background_removal_method_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'background_removal_method',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'camera_cluster_type',
      'table_join_field' => 'camera_cluster_type_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'cluster_type',
    );

    if ($search_value) {
      $query_params['search_params'][1] = array(
        'field_names' => array(
          'capture_dataset.capture_dataset_guid',
          'capture_dataset.capture_dataset_field_id',
          'capture_dataset.capture_method',
          'capture_dataset.capture_dataset_type',
          'capture_dataset.capture_dataset_name',
          'capture_dataset.collected_by',
          'capture_dataset.date_of_capture',
          'capture_dataset.capture_dataset_description',
          'capture_dataset.collection_notes',
          'capture_dataset.support_equipment',
          'capture_dataset.item_position_type',
          'capture_dataset.item_position_field_id',
          'capture_dataset.item_arrangement_field_id',
          'capture_dataset.positionally_matched_capture_datasets',
          'capture_dataset.focus_type',
          'capture_dataset.light_source_type',
          'capture_dataset.background_removal_method',
          'capture_dataset.cluster_type',
          'capture_dataset.cluster_geometry_field_id',
          'capture_dataset.resource_capture_datasets',
          'capture_dataset.calibration_object_used',
          'capture_dataset.date_created',
          'capture_dataset.created_by_user_account_id',
          'capture_dataset.last_modified',
          'capture_dataset.last_modified_user_account_id',
        ),
        'search_values' => array($search_value),
        'comparison' => 'LIKE',
      );
    }

    if($item_repository_id && is_numeric($item_repository_id)) {
      $count_params = count($query_params['search_params']);
      $query_params['search_params'][$count_params] = array(
        'field_names' => array(
          'capture_dataset.parent_item_repository_id',
        ),
        'search_values' => array((int)$item_repository_id),
        'comparison' => '=',
      );
      //          AND capture_dataset.item_repository_id = " . (int)$item_repository_id . "");
    }

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'capture_dataset_repository_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'capture_dataset_guid',
    );
    $query_params['fields'][] = array(
      'field_name' => 'capture_dataset_field_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'capture_dataset_name',
    );
    $query_params['fields'][] = array(
      'field_name' => 'collected_by',
    );
    $query_params['fields'][] = array(
      'field_name' => 'date_of_capture',
    );
    $query_params['fields'][] = array(
      'field_name' => 'capture_dataset_description',
    );
    $query_params['fields'][] = array(
      'field_name' => 'collection_notes',
    );
    $query_params['fields'][] = array(
      'field_name' => 'support_equipment',
    );
    $query_params['fields'][] = array(
      'field_name' => 'item_position_field_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'item_arrangement_field_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'positionally_matched_capture_datasets',
    );
    $query_params['fields'][] = array(
      'field_name' => 'cluster_geometry_field_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'resource_capture_datasets',
    );
    $query_params['fields'][] = array(
      'field_name' => 'calibration_object_used',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'date_created',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'cluster_type',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'created_by_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'last_modified_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'capture_dataset_repository_id',
      'field_alias' => 'DT_RowId',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_method',
      'field_name' => 'label',
      'field_alias' => 'capture_method'
    );
    $query_params['fields'][] = array(
      'table_name' => 'dataset_type',
      'field_name' => 'label',
      'field_alias' => 'capture_dataset_type'
    );
    $query_params['fields'][] = array(
      'table_name' => 'item_position_type',
      'field_name' => 'label',
      'field_alias' => 'item_position_type'
    );
    $query_params['fields'][] = array(
      'table_name' => 'focus_type',
      'field_name' => 'label',
      'field_alias' => 'focus_type'
    );
    $query_params['fields'][] = array(
      'table_name' => 'light_source_type',
      'field_name' => 'label',
      'field_alias' => 'light_source_type'
    );
    $query_params['fields'][] = array(
      'table_name' => 'background_removal_method',
      'field_name' => 'label',
      'field_alias' => 'background_removal_method'
    );
    $query_params['fields'][] = array(
      'table_name' => 'camera_cluster_type',
      'field_name' => 'label',
      'field_alias' => 'camera_cluster_type'
    );

    $query_params['records_values'] = array();

    $query_params['limit'] = array(
      'limit_start' => $start_record,
      'limit_stop' => $stop_record,
    );

    if (!empty($sort_field) && !empty($sort_order)) {
      $query_params['sort_fields'][] = array(
        'field_name' => $sort_field,
        'sort_order' => $sort_order,
      );
    } else {
      $query_params['sort_fields'][] = array(
        'field_name' => 'capture_dataset.last_modified',
        'sort_order' => 'DESC',
      );
    }

    $data = $this->getRecordsDatatable($query_params);

    return $data;

  }

  public function getDatatableCaptureDataElement($params) {

      $record_type = 'capture_data_element';
      $capture_dataset_repository_id = array_key_exists('capture_dataset_repository_id', $params) ? $params['capture_dataset_repository_id'] : NULL;
      $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
      $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
      $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
      $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

      $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
      //@todo- allow match on ID- specify ID field and value $record_match = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;

      $query_params = array(
        'distinct' => true, // @todo Do we always want this to be true?
        'base_table' => $record_type,
        'fields' => array(),
      );

      $query_params['limit'] = array(
        'limit_start' => $start_record,
        'limit_stop' => $stop_record,
      );

      if (!empty($sort_field) && !empty($sort_order)) {
        $query_params['sort_fields'][] = array(
          'field_name' => $sort_field,
          'sort_order' => $sort_order,
        );
      } else {
        $query_params['sort_fields'][] = array(
          'field_name' => $record_type . '.last_modified',
          'sort_order' => 'DESC',
        );
      }

      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => $record_type . '_repository_id',
        'field_alias' => 'manage',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'capture_dataset_repository_id',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'capture_device_configuration_id',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'capture_device_field_id',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'capture_sequence_number',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'cluster_position_field_id',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'position_in_cluster_field_id',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'date_created',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'created_by_user_account_id',
      );

      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'last_modified',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'last_modified_user_account_id',
      );
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => $record_type . '_repository_id',
        'field_alias' => 'DT_RowId',
      );
      $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
      $query_params['search_type'] = 'AND';
      if (NULL !== $search_value) {
        $query_params['search_params'][1] = array(
          'field_names' => array(
            $record_type . '.capture_device_configuration_id',
            $record_type . '.capture_device_field_id',
            $record_type . '.capture_sequence_number',
            $record_type . '.cluster_position_field_id',
            $record_type . '.position_in_cluster_field_id',
          ),
          'search_values' => array($search_value),
          'comparison' => 'LIKE',
        );
      }
      if(NULL !== $capture_dataset_repository_id) {
        $c = count($query_params['search_params']);
        $query_params['search_params'][$c] = array(
          'field_names' => array(
            $record_type . '.capture_dataset_repository_id',
          ),
          'search_values' => array((int)$capture_dataset_repository_id),
          'comparison' => '=',
        );
      }

      $data = $this->getRecordsDatatable($query_params);
      return $data;

  }

  /**
   * @param $params
   * @return mixed
   */
  public function getDatatableItem($params) {

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;
    $subject_repository_id = array_key_exists('subject_repository_id', $params) ? $params['subject_repository_id'] : NULL;

    $query_params = array(
      'fields' => array(),
      'distinct' => true,
      'base_table' => 'item',
      'search_params' => array(
        0 => array('field_names' => array('item.active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND',
    );

    if ($search_value) {
      $query_params['search_params'][1] = array(
        'field_names' => array(
          'item.item_description',
          'item.local_item_id',
          'item.date_created',
          'item.last_modified',
          'status_type.label',
        ),
        'search_values' => array($search_value),
        'comparison' => 'LIKE',
      );
    }
    if($subject_repository_id && is_numeric($subject_repository_id)) {
      $count_params = count($query_params['search_params']);
      $query_params['search_params'][$count_params] = array(
        'field_names' => array(
          'item.subject_repository_id',
        ),
        'search_values' => array((int)$subject_repository_id),
        'comparison' => '=',
      );
    }

    $query_params['related_tables'][] = array(
      'table_name' => 'status_type',
      'table_join_field' => 'status_type_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'status_type_repository_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'capture_dataset',
      'table_join_field' => 'parent_item_repository_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'item_repository_id',
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'item_repository_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'subject_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'local_item_id',
    );
    $query_params['fields'][] = array(
      'field_name' => "CONCAT(SUBSTRING(item.item_description,1, 50), '...')",
      'field_alias' => 'item_description',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'status_type_repository_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'date_created',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'item_repository_id',
      'field_alias' => 'DT_RowId',
    );

    $query_params['fields'][] = array(
      'table_name' => 'status_type',
      'field_name' => 'label',
      'field_alias' => 'status_label'
    );
    $query_params['fields'][] = array(
      'field_name' => 'count(distinct capture_dataset.parent_item_repository_id)',
      'field_alias' => 'datasets_count',
    );

    // Need to group by since we're doing a count
    $query_params['group_by'] = array(
      'item.item_repository_id'
    );

    $query_params['records_values'] = array();

    $query_params['limit'] = array(
      'limit_start' => $start_record,
      'limit_stop' => $stop_record,
    );

    if (!empty($sort_field) && !empty($sort_order)) {
      $query_params['sort_fields'][] = array(
        'field_name' => $sort_field,
        'sort_order' => $sort_order,
      );
    } else {
      $query_params['sort_fields'][] = array(
        'field_name' => 'item.last_modified',
        'sort_order' => 'DESC',
      );
    }

    $data = $this->getRecordsDatatable($query_params);

    return $data;

  }

  public function markProjectInactive($params) {
    $user_id = $params['user_id'];
    $project_id = $params['record_id'];

    //@todo trap for missing user_id or record_id.
    $sql = "UPDATE project
                LEFT JOIN subject ON subject.project_repository_id = project.project_repository_id
                LEFT JOIN item ON item.subject_repository_id = subject.subject_repository_id
                LEFT JOIN capture_dataset ON capture_dataset.parent_item_repository_id = item.item_repository_id
                LEFT JOIN capture_data_element ON capture_data_element.capture_dataset_repository_id = capture_dataset.capture_dataset_repository_id
                SET project.active = 0,
                    project.last_modified_user_account_id = :last_modified_user_account_id,
                    subject.active = 0,
                    subject.last_modified_user_account_id = :last_modified_user_account_id,
                    item.active = 0,
                    item.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE project.project_repository_id = :id
            ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $project_id, PDO::PARAM_INT);
    $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);
    $statement->execute();

    // Can't return records- causes PDO error.
    //$return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success'); //, 'data' => $return);

}

  public function markSubjectInactive($params) {

    $user_id = $params['user_id'];
    $subject_id = $params['record_id'];

    //@todo trap for missing user_id or record_id.
    $sql = "UPDATE subject
                LEFT JOIN item ON item.subject_repository_id = subject.subject_repository_id
                LEFT JOIN capture_dataset ON capture_dataset.parent_item_repository_id = item.item_repository_id
                LEFT JOIN capture_data_element ON capture_data_element.capture_dataset_repository_id = capture_dataset.capture_dataset_repository_id
                SET subject.active = 0,
                    subject.last_modified_user_account_id = :last_modified_user_account_id,
                    item.active = 0,
                    item.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE subject.subject_repository_id = :id
            ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $subject_id, PDO::PARAM_INT);
    $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);
    $statement->execute();

    // Can't return records- causes PDO error.
    //$return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success'); //, 'data' => $return);

  }

  public function markCaptureDatasetInactive($params) {
    $user_id = $params['user_id'];
    $capture_dataset_repository_id = $params['record_id'];

    //@todo trap for missing user_id or record_id.
    $sql = "UPDATE capture_dataset
                LEFT JOIN capture_data_element ON capture_data_element.capture_data_element_repository_id = capture_dataset.capture_dataset_repository_id
                SET capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE capture_dataset.capture_dataset_repository_id = :id
            ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $capture_dataset_repository_id, PDO::PARAM_INT);
    $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);
    $statement->execute();

    // Can't return records- causes PDO error.
    //$return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success'); //, 'data' => $return);

  }

  public function markItemInactive($params) {
    $user_id = $params['user_id'];
    $item_id = $params['record_id'];

    //@todo trap for missing user_id or record_id.
    $sql = "UPDATE item
                LEFT JOIN capture_dataset ON capture_dataset.parent_item_repository_id = item.item_repository_id
                LEFT JOIN capture_data_element ON capture_data_element.capture_dataset_repository_id = capture_dataset.capture_dataset_repository_id
                SET item.active = 0,
                    item.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE item.item_repository_id = :id
            ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $item_id, PDO::PARAM_INT);
    $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);
    $statement->execute();

    // Can't return records- causes PDO error.
    //$return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success'); //, 'data' => $return);

  }

  public function updateItemStatus($params) {
    $status_id = $params['status_type_id'];
    $item_id = $params['item_guid'];

    //@todo trap for missing user_id or record_id.
    $sql = "UPDATE item
                SET status_type_repository_id = :statusid
                WHERE item_guid = :itemguid";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":statusid", $status_id, PDO::PARAM_INT);
    $statement->bindValue(":itemguid", $item_id, PDO::PARAM_INT);
    $statement->execute();

    $return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success', 'data' => $return);

  }

  /**
   * Save function.
   */
  /**
   * @param $params
   * @return null
   */
  public function saveRecord($params) {

    $base_table = array_key_exists('base_table', $params) ? $params['base_table'] : NULL;
    $values = array_key_exists('values', $params) ? $params['values'] : NULL;
    $user_id = array_key_exists('user_id', $params) ? $params['user_id'] : NULL;
    $id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    if(!isset($id) || strlen(trim($id)) < 1) {
      $id = NULL;
    }

    if(NULL == $base_table || NULL == $values || NULL == $user_id) {
      return NULL;
    }

    // Get the expected fields for this table from MySQL.
    $query_params = array('base_table' => $base_table);

    $table_columns = $this->getTableColumns($base_table);
    // returns an array with 'id', 'fields',
    // where 'id' specifies the primary key field and fields is an array of other field names.

    $record = array();
    $fields = array();
    // Fill in values for fields this table is expecting, using $values.
    // (Rather than just blindly attempting to submit $values to the INSERT or UPDATE).
    foreach($table_columns['fields'] as $col) {

      if($col == 'last_modified') {
        // last_modified will default to current timestamp if not set.
        // We don't need to add this field to the update or insert statement.
        continue;
      }
      if($col == 'date_created' && NULL !== $id) {
        // Don't reset date_created if we're editing.
        continue;
      }
      if($col == 'created_by_user_account_id' && NULL !== $id) {
        // Don't reset creating user if we're editing.
        continue;
      }
      if($col == 'active' && !array_key_exists($col, $values)) {
        // Don't set value for active unless the caller explicitly sets it.
        continue;
      }

      $f = array('field_name' => $col);
      if(array_key_exists($col, $values)) {
        $f['field_value'] = $values[$col];
      }

      // Certain values should be auto-set.
      if($col == 'last_modified_user_account_id') {
        if(NULL !== $user_id) {
          $f['field_value'] = $user_id;
        }
        else {
          $f['field_value'] = 0;
        }
      }
      if(NULL == $id) {
        if($col == 'created_by_user_account_id') {
          $f['field_value'] = $user_id;
        }
      }

      $fields[] = $f;
    }

    $record['fields'] = $fields;

    if(NULL !== $id) {
      $id_field = array(
        'field_name' => $table_columns['id'],
        'field_value' => $id,
      );
      $record['id'] = $id_field;
    }

    $query_params['records_values'][] = $record;

    // Submit for save.
    $ret = $this->setRecords($query_params);

    if(array_key_exists('return', $ret) && $ret['return'] == 'success') {
      $ids = $ret['ids'];
      if(count($ids) == 1) {
        // This should always be true!
        return $ids[0];
      }
      else {
        return $ids;
      }
    }
    else {
      return NULL;
      //@todo return $ret['error']
    }

  }

  public function saveIsniRecord($params) {

    $label = array_key_exists('record_label', $params) ? $params['record_label'] : NULL;
    $user_id = array_key_exists('user_id', $params) ? $params['user_id'] : NULL;
    $id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    if(!isset($id) || strlen(trim($id)) < 1 || !isset($label) || strlen(trim($label)) < 1) {
      // ISNI data requires an ID.
      return; //@todo with error
    }

    // See if record exists; update if so, insert otherwise.
    $sql = "Select isni_id FROM isni_data WHERE isni_id=:id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $id, PDO::PARAM_INT);
    $statement->execute();

    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);
    if(count($ret) > 0) {
      // update
      $sql ="UPDATE isni_data set isni_label=:label, last_modified=NOW(), last_modified_user_account_id=:user_id WHERE isni_id=:id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":id", $id, PDO::PARAM_INT);
      $statement->bindValue(":label", $label, PDO::PARAM_INT);
      $statement->bindValue(":user_id", $user_id, PDO::PARAM_INT);
      $statement->execute();
    }
    else {
      // insert
      $sql ="INSERT INTO isni_data (isni_id, isni_label, date_created, last_modified,
        created_by_user_account_id, last_modified_user_account_id	
        ) 
        VALUES (:id, :label, NOW(), NOW(), :user_id, :user_id)";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":id", $id, PDO::PARAM_INT);
      $statement->bindValue(":label", $label, PDO::PARAM_INT);
      $statement->bindValue(":user_id", $user_id, PDO::PARAM_INT);
      $statement->execute();
    }

    return $id;

  }


  /**
   * ----------------------------------------------------------------
   * Generic functions for getting, setting, deleting and marking inactive.
   * ----------------------------------------------------------------
   */
  /**
   * @param $parameters
   * @return array
   */
  public function getRecord($parameters) {

    //@todo confirm params exist
    $base_table = $parameters['base_table'];
    $id_field = $parameters['id_field'];
    $id_value = $parameters['id_value'];

    $query_params = array(
      'fields' => array(),
      'base_table' => $base_table,
      'search_params' => array(
        0 => array('field_names' => array('active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array($id_field), 'search_values' => array($id_value), 'comparison' => '=')
      ),
      'search_type' => 'AND'
    );

    $return_data = array();
    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getRecords(array $query_parameters) {

    /*
     * $query_parameters should contain:
     * ------------
     * base_table - string indicating base table for query
     * fields - array of field_name, field_alias, field_table
     *    if fields array is empty, query will return all fields for base_table
     * related_tables - optional array of table,
     *    where each table is an array of
     *    table_name, table_alias, join_type, table_join_field, base_join_table, base_join_field
     * sort_fields - array containing fields, where each field value is an array of field_name and optionally sort_order
     * limit - array containing limit_start, limit_stop
     * search_params - array containing search values.
     *          Each array value is an array containing field_names, search_values.
     *          field_names and search_values are also arrays.
     *          This makes it possible to structure OR queries or test fields for multiple values
     *          as well as doing a simple 1-to-1 search.
     */

    $base_table = NULL;
    $search_params = array();
    $limit_start = $limit_stop = NULL;
    $select_sql = $join_sql = $search_sql = $sort_sql = $limit_sql = '';
    $data = array();

    // We need certain values: fields, base table. Fail if those aren't provided.
    if(!array_key_exists('fields', $query_parameters) || !isset($query_parameters['fields']) || !is_array($query_parameters['fields'])) {
      $fields = array(
        0 => array(
          'field_name' => 'label',
        ),
        1 => array(
          'field_name' => $base_table . '_repository_id',
        ),
      );
    }
    if(!array_key_exists('base_table', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No base_table parameter specified.'));
    }

    // Table
    $base_table = $query_parameters['base_table'];

    // Fields
    $select_fields_array = array();
    if(array_key_exists('fields', $query_parameters)) {
      foreach($query_parameters['fields'] as $k => $field) {
        if(array_key_exists('field_name', $field)) {
          $this_field = '';
          if(array_key_exists('table_name', $field)) {
            $this_field = $field['table_name'] . '.';
          }
          $this_field .= $field['field_name'];
          if(array_key_exists('field_alias', $field)) {
            $this_field .= ' as ' . $field['field_alias'];
          }
          $select_fields_array[] = $this_field;
        }
      }
    }
    if(count($select_fields_array) < 1) {
      $select_fields_array[] = $base_table . '.*';
    }
    $select_sql = implode(', ', $select_fields_array);
    if(array_key_exists('distinct', $query_parameters)) {
      $select_sql = ' DISTINCT ' . $select_sql;
    }

    if(array_key_exists('related_tables', $query_parameters) && is_array($query_parameters['related_tables'])
      && count($query_parameters['related_tables']) > 0) {
      $joins = array();
      foreach($query_parameters['related_tables'] as $rt) {
        if(!array_key_exists('table_name', $rt) || !array_key_exists('join_type', $rt)
          || !array_key_exists('base_join_table', $rt) || !array_key_exists('base_join_field', $rt)
          || !array_key_exists('table_join_field', $rt)
        ) {
          continue;
        }

        if(strtoupper($rt['join_type']) !== 'LEFT JOIN' && strtoupper($rt['join_type']) !== 'INNER JOIN'
          && strtoupper($rt['join_type']) !== 'OUTER JOIN' && strtoupper($rt['join_type']) !== 'JOIN'
        ) {
          continue;
        }
        $join = ' ' . $rt['join_type'] . ' ' . $rt['table_name'];
        if(array_key_exists('table_alias', $rt)) {
          $join .= ' ' . $rt['table_alias'];
        }
        $join .= ' ON ' . $rt['base_join_table'] . '.' . $rt['base_join_field'] . ' = ' . $rt['table_name'] . '.' . $rt['table_join_field'];

        $joins[] = $join;
      }
      if(count($joins) > 0) {
        $join_sql = implode(' ', $joins);
      }

    }

    // Limit
    if(array_key_exists('limit', $query_parameters) && is_array($query_parameters['limit'])) {
      $limit = $query_parameters['limit'];
      //@todo other checks? Like > 0 ?
      if(array_key_exists('limit_start', $limit) && is_numeric($limit['limit_start'])) {
        $limit_start = $limit['limit_start'];
        if(array_key_exists('limit_start', $limit) && is_numeric($limit['limit_start'])) {
          $limit_stop = $limit['limit_stop'];
        }
        if(NULL !== $limit_stop) {
          $limit_sql = " LIMIT {$limit_start}, {$limit_stop} ";
        }
        else {
          $limit_sql = " LIMIT {$limit_start} ";
        }
      }
    }

    // Sort
    if(array_key_exists('sort_fields', $query_parameters) && is_array($query_parameters['sort_fields'])) {
      $sort_params = array();
      foreach($query_parameters['sort_fields'] as $fld) {
        $s = $fld['field_name'];
        if(array_key_exists('sort_order', $fld) && $fld['sort_order'] == 'DESC') {
          $s .= ' ' . $fld['sort_order'];
        }
        $sort_params[] = $s;
      }
      $sort_sql = implode(', ', $sort_params);
      if(!is_object($sort_sql) && strlen(trim($sort_sql)) > 0) {
        $sort_sql = " ORDER BY " . $sort_sql;
      }
    }

    // Search values
    $query_parameters['search_params'][] = array('field_names' => array($base_table . '.active'), 'search_values' => array(1), 'comparison' => '=');

    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if((!is_array($search_values) && strlen(trim($search_values)) > 0)) {
          $search_values = array($search_values);
        }
        if(!is_array($field_names) || count($field_names) == 0 || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            if(array_key_exists('comparison', $p) && $p['comparison'] !== 'LIKE') {
              $this_search_param[] = $fn . ' ' . $p['comparison'] . ' ?';
              $k = array_keys($search_values)[0];
              $search_params[] = $search_values[$k];
            }
            else {
              $this_search_param[] = $fn . ' LIKE ?';
              $search_params[] = '%' . $search_values[array_keys($search_values[0])] . '%';
            }
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values[] = '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        if(array_key_exists('search_type', $query_parameters) && $query_parameters['search_type'] != 'AND') {
          $search_sql = implode(' ' . $query_parameters['search_type'] . ' ', $search_sql_values);
        }
        else {
          $search_sql = implode(' AND ', $search_sql_values);
        }
      }
    }

    $sql = "SELECT " . $select_sql .
      " FROM " . $base_table;

    if(!is_object($join_sql) && strlen($join_sql) > 0) {
      $sql .= $join_sql;
    }

    if(!is_object($search_sql) && strlen(trim($search_sql)) > 0) {
      $sql .= " WHERE {$search_sql} ";
    }
    if(!is_object($sort_sql) && strlen(trim($sort_sql)) > 0) {
      $sql .= " {$sort_sql} ";
    }
    if(!is_object($limit_sql) && strlen(trim($limit_sql)) > 0) {
      $sql .= $limit_sql;
    }

    $statement = $this->connection->prepare($sql);
    if(count($search_params) > 0) {
      $statement->execute($search_params);
    }
    else {
      $statement->execute();
    }
    $records_values = $statement->fetchAll(PDO::FETCH_ASSOC);
    return $records_values;

  }

  /***
   * @param $query_parameters parameters used to query records for return.
   * Sets records into $records_values.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function getRecordsDatatable(array $query_parameters) {

    /*
     * $query_parameters should contain:
     * ------------
     * base_table - string indicating base table for query
     * fields - array of field_name, field_alias, field_table
     *    if fields array is empty, query will return all fields for base_table
     * related_tables - optional array of table,
     *    where each table is an array of
     *    table_name, table_alias, join_type, table_join_field, base_join_table, base_join_field
     * sort_fields - array containing fields, where each field value is an array of field_name and optionally sort_order
     * limit - array containing limit_start, limit_stop
     * search_params - array containing search values.
     *          Each array value is an array containing field_names, search_values.
     *          field_names and search_values are also arrays.
     *          This makes it possible to structure OR queries or test fields for multiple values
     *          as well as doing a simple 1-to-1 search.
     */

    $base_table = NULL;
    $search_params = array();
    $select_sql = $join_sql = $search_sql = $sort_sql = $limit_sql = $group_sql = '';
    $data = array();

    // We need certain values: fields, base table. Fail if those aren't provided.
    if(!array_key_exists('fields', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No fields parameter specified.'));
    }
    if(!array_key_exists('base_table', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No base_table parameter specified.'));
    }
    if(!isset($query_parameters['fields']) || !is_array($query_parameters['fields'])) {
      return array('return' => 'fail', 'messages' => array('No fields parameter specified.'));
    }

    // Table
    $base_table = $query_parameters['base_table'];

    // Fields
    $select_fields_array = array();
    foreach($query_parameters['fields'] as $k => $field) {
      if(array_key_exists('field_name', $field)) {
        $this_field = '';
        if(array_key_exists('table_name', $field)) {
          $this_field = $field['table_name'] . '.';
        }
        $this_field .= $field['field_name'];
        if(array_key_exists('field_alias', $field)) {
          $this_field .= ' as ' . $field['field_alias'];
        }
        $select_fields_array[] = $this_field;
      }
    }
    if(count($select_fields_array) < 1) {
      $select_fields_array[] = $base_table . '.*';
    }
    $select_sql = implode(', ', $select_fields_array);
    if(array_key_exists('distinct', $query_parameters)) {
      $select_sql = ' DISTINCT ' . $select_sql;
    }

    if(array_key_exists('related_tables', $query_parameters) && is_array($query_parameters['related_tables'])
    && count($query_parameters['related_tables']) > 0) {
      $joins = array();
      foreach($query_parameters['related_tables'] as $rt) {
        if(!array_key_exists('table_name', $rt) || !array_key_exists('join_type', $rt)
          || !array_key_exists('base_join_table', $rt) || !array_key_exists('base_join_field', $rt)
          || !array_key_exists('table_join_field', $rt)
        ) {
          continue;
        }

        if(strtoupper($rt['join_type']) !== 'LEFT JOIN' && strtoupper($rt['join_type']) !== 'INNER JOIN'
          && strtoupper($rt['join_type']) !== 'OUTER JOIN' && strtoupper($rt['join_type']) !== 'JOIN'
        ) {
          continue;
        }
        $join = ' ' . $rt['join_type'] . ' ' . $rt['table_name'];
        if(array_key_exists('table_alias', $rt)) {
          $join .= ' ' . $rt['table_alias'];
        }
        $join .= ' ON ' . $rt['base_join_table'] . '.' . $rt['base_join_field'] . ' = ' . $rt['table_name'] . '.' . $rt['table_join_field'];

        $joins[] = $join;
      }
      if(count($joins) > 0) {
        $join_sql = implode(' ', $joins);
      }

    }

    // Limit
    if(array_key_exists('limit', $query_parameters) && is_array($query_parameters['limit'])) {
      $limit = $query_parameters['limit'];
      //@todo other checks? Like > 0 ?
      if(array_key_exists('limit_start', $limit) && is_numeric($limit['limit_start'])) {
        $limit_start = $limit['limit_start'];
        $limit_stop = '';
        if(array_key_exists('limit_start', $limit) && is_numeric($limit['limit_start'])) {
          $limit_stop = $limit['limit_stop'];
        }
        if(NULL !== $limit_stop) {
          $limit_sql = " LIMIT {$limit_start}, {$limit_stop} ";
        }
        else {
          $limit_sql = " LIMIT {$limit_start} ";
        }
      }
    }

    // Sort
    if(array_key_exists('sort_fields', $query_parameters) && is_array($query_parameters['sort_fields'])) {
      $sort_params = array();
      foreach($query_parameters['sort_fields'] as $fld) {
        $s = $fld['field_name'];
        $s .= ' ' . $fld['sort_order'];
        $sort_params[] = $s;
      }
      $sort_sql = implode(', ', $sort_params);
      if(!is_object($sort_sql) && strlen(trim($sort_sql)) > 0) {
        $sort_sql = " ORDER BY " . $sort_sql;
      }
    }

    if(array_key_exists('group_by', $query_parameters) && is_array($query_parameters['group_by'])) {
      $group_sql = implode(', ', $query_parameters['group_by']);
      if(!is_object($group_sql) && strlen(trim($group_sql)) > 0) {
        $group_sql = " GROUP BY " . $group_sql;
      }
    }

    // Search values
    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if((!is_array($search_values) && !is_object($search_values) && strlen(trim($search_values)) > 0)) {
          $search_values = array($search_values);
        }
        if(!is_array($field_names) || count($field_names) == 0
          || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            $k = array_keys($search_values)[0];
            if(array_key_exists('comparison', $p) && $p['comparison'] !== 'LIKE') {
              $this_search_param[] = $fn . ' ' . $p['comparison'] . ' ?';
              $search_params[] = $search_values[$k];
            }
            else {
              $this_search_param[] = $fn . ' LIKE ?';
              $search_params[] = '%' . $search_values[$k] . '%';
            }
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values[] = '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        if(array_key_exists('search_type', $query_parameters) && $query_parameters['search_type'] != 'OR') {
          $search_sql = implode(' ' . $query_parameters['search_type'] . ' ', $search_sql_values);
        }
        else {
          $search_sql = implode(' OR ', $search_sql_values);
        }
      }
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS "
      . $select_sql .
      " FROM " . $base_table;

    if(!is_object($join_sql) && strlen($join_sql) > 0) {
      $sql .= $join_sql;
    }

    if(!is_object($search_sql) && strlen(trim($search_sql)) > 0) {
      $sql .= " WHERE {$search_sql} ";
    }
    if(!is_object($group_sql) && strlen(trim($group_sql)) > 0) {
      $sql .= " {$group_sql} ";
    }
    if(!is_object($sort_sql) && strlen(trim($sort_sql)) > 0) {
      $sql .= " {$sort_sql} ";
    }
    if(!is_object($limit_sql) && strlen(trim($limit_sql)) > 0) {
      $sql .= $limit_sql;
    }

    $statement = $this->connection->prepare($sql);
    if(count($search_params) > 0) {
      $statement->execute($search_params);
    }
    else {
      $statement->execute();
    }
    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->connection->prepare("SELECT FOUND_ROWS()");
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["FOUND_ROWS()"];
    $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

    return $data;

  }

  /***
   * @param null $records_values data and metadata to save.
   * Attempts to save data, and updates $records_values accordingly.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function setRecords(array $query_parameters){

    /*
     * $query_parameters['records_values'] should contain an array of record values.
     *          Each record_value should have field_name, field_value.
     *
     *          $records_values may have an entry with key 'id'.
     *          If the array item key is 'id' and field_value is set, this indicates that the record
     *          already exists, and triggers an UPDATE statement rather than an INSERT.
     *          If the item key is 'id' and field_value is not set, an INSERT will be performed,
     *          and field_value will be updated from the id returned after inserting.
     *
     * $query_parameters should contain the info necessary to determine which records to update:
     * base_table - string indicating base table for update
     * search_params
     */

    $base_table = NULL;
    $data = array();
    $last_inserted_ids = array();

    // We need certain values: record_values, base table. Fail if those aren't provided.
    if(!array_key_exists('base_table', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No base_table parameter specified.'));
    }
    if(!isset($query_parameters['records_values']) || !is_array($query_parameters['records_values'])) {
      return array('return' => 'fail', 'messages' => array('No record values specified.'));
    }

    // Table
    $base_table = $query_parameters['base_table'];

    // Search values
    /*
    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if(!is_array($field_names) || count($field_names) == 0
          || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            $this_search_param[] = $fn . ' LIKE ?';
            $search_params[] = '%' . $search_values[array_keys($search_values[0])] . '%';
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values .= '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        $search_sql = implode(' AND ', $search_sql_values);
      }
    }
    */

    foreach($query_parameters['records_values'] as $record_values) {
      $update = false;
      $fields_sql_array = $fields_values_sql_array = $fields_params = array();

      if(array_key_exists('id', $record_values)
        && array_key_exists('field_value', $record_values['id']) && isset($record_values['id']['field_value'])
        && array_key_exists('field_name', $record_values['id']) && isset($record_values['id']['field_name'])
      )
      {
        $update = true;
        $last_inserted_id = $record_values['id']['field_value'];
      }

      foreach($record_values['fields'] as $rv) {
        if(array_key_exists('field_value', $rv) && isset($rv['field_value'])
          && array_key_exists('field_name', $rv) && isset($rv['field_name'])
        ) {
          if($update) {
            $fields_sql_array[] = $rv['field_name'] . " = :" . $rv['field_name'];
          }
          else {
            $fields_sql_array[$rv['field_name']] = ":" . $rv['field_name'];
          }
          $fields_params[":" . $rv['field_name']] = $rv['field_value'];
        }
      }

      if(count($fields_sql_array) > 0 && count($fields_sql_array) == count($fields_params)) {
        if($update) {
          $sql ="UPDATE " . $base_table;
          $sql .= " SET " . implode(',', $fields_sql_array);
          $sql .= " WHERE " . $record_values['id']['field_name'] . " = :id ";

          $statement = $this->connection->prepare($sql);
          foreach($fields_params as $fn1 => $fv1) {
            $statement->bindValue($fn1, $fv1);
          }
          $statement->bindValue(":id", $record_values['id']['field_value'], PDO::PARAM_INT);
          $statement->execute();

        }
        else {
          $sql ="INSERT INTO " . $base_table;
          $sql .= " (" . implode(',', array_keys($fields_sql_array)) . ', date_created)';
          $sql .= " VALUES (" . implode(',', array_values($fields_sql_array)) . ', NOW())';

          $statement = $this->connection->prepare($sql);
          foreach($fields_params as $fn1 => $fv1) {
            $statement->bindValue($fn1, $fv1);
          }
          $statement->execute();
          $last_inserted_id = $this->connection->lastInsertId();

          if(!$last_inserted_id) {
            return array('return' => 'fail', 'messages' => 'INSERT INTO `' . $this->table_name . '` failed.');
          }
        }

      } // if we have enough info to perform an insert or update

      $last_inserted_ids[] = $last_inserted_id;
    } // each set of record values

    return array('return' => 'success', 'ids' => $last_inserted_ids);
  }

  /***
   * @param $query_parameters parameters used to query records for deletion.
   * Attempts to delete specified records.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function deleteRecords(array $query_parameters){

    /*
     * $query_parameters should contain:
     * ------------
     * base_table - string indicating base table for query
     * search_params - array containing fields and values to use to locate the record for deletion.
     *          Each search_params value is an array containing field_names, search_values.
     */

    $base_table = NULL;
    $search_sql = '';
    $data = array();

    // We need base table. Fail if that isn't provided.
    if(!array_key_exists('base_table', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No base_table parameter specified.'));
    }

    if(isset($query_parameters['search_params']) && !is_array($query_parameters['search_params'])) {
      return array('return' => 'fail', 'messages' => array('Fields parameter is invalid.'));
    }

    // Table
    $base_table = $query_parameters['base_table'];

    // Search values
    if (array_key_exists('search_params', $query_parameters) && is_array($query_parameters['search_params'])) {
      $search_sql_values = array();
      foreach($query_parameters['search_params'] as $p) {
        $field_names = $p['field_names'];
        $search_values = $p['search_values'];

        if(!is_array($field_names) || count($field_names) == 0
          || !is_array($search_values) || count($search_values) == 0) {
          continue;
        }

        $this_search_param = array();
        foreach($field_names as $fn) {
          if(count($search_values) == 1) {
            $this_search_param[] = $fn . ' LIKE ?';
            $search_params[] = '%' . $search_values[array_keys($search_values[0])] . '%';
          }
          else {
            $this_search_param[] = $fn['field_name'] . ' IN (?)';
            $search_params[] = '%' . implode('%, %', $search_values) . '%';
          }
        }

        $search_sql_values .= '(' . implode(' OR ', $this_search_param) . ') ';
      }

      if(count($search_sql_values) > 0) {
        $search_sql = implode(' AND ', $search_sql_values);
      }
    }

    if(true == $query_parameters['delete_children']) {
      //@todo delete children first

      switch($base_table) {
        case 'project':
          break;
        case 'subject':
          break;
        case 'item':
          break;

      }
    }

    $sql = "DELETE FROM " . $base_table;

    if(!is_object($search_sql) && strlen(trim($search_sql)) > 0) {
      $sql .= " WHERE {$search_sql} ";
    }

    $statement = $this->connection->prepare($sql);
    if(count($search_params) > 0) {
      $statement->execute($search_params);
    }
    else {
      $statement->execute();
    }
    $return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success', 'data' => $return);
  }

  /***
   * @param $query_parameters parameters used to query records to be marked inactive.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function markRecordInactive(array $query_parameters){

    /*
     * $query_parameters should contain:
     * ------------
     * record_type - string indicating base table for query
     * record_id
     * user_id
     */

    $record_type = NULL;

    // We need base table. Fail if that isn't provided.
    if(!array_key_exists('record_type', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No record_type parameter specified.'));
    }
    if(!array_key_exists('user_id', $query_parameters)) {
      return array('return' => 'fail', 'messages' => array('No user_id parameter specified.'));
    }
    if(!isset($query_parameters['record_id']) || !is_numeric($query_parameters['record_id'])) {
      return array('return' => 'fail', 'messages' => array('To mark a record inactive a record_id must be specified.'));
    }

    // Table
    $record_type = $query_parameters['record_type'];

    $user_id = $query_parameters['user_id'];
    $record_id = array_key_exists('record_id', $query_parameters) ? $query_parameters['record_id'] : NULL;

    $sql = "UPDATE " . $record_type . " SET active = 0, last_modified_user_account_id=:user_id WHERE " . $record_type . "_repository_id=:id ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":user_id", $user_id, PDO::PARAM_INT);
    $statement->bindValue(":id", $record_id, PDO::PARAM_INT);
    $statement->execute();
    //$return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success'); //, 'data' => $return);
  }


  /**
   * @param $base_table
   * @return array
   */
  private function getTableColumns($base_table) {

    $sql ="SHOW COLUMNS FROM " . $base_table;
    $statement = $this->connection->prepare($sql);

    $statement->execute();
    $column_data = $statement->fetchAll(PDO::FETCH_ASSOC);

    $fields = array();
    foreach($column_data as $col) {
      if($col['Key'] == 'PRI') {
        $fields['id'] = $col['Field'];
      }
      else {
        $fields['fields'][] = $col['Field'];
      }
    }

    return $fields;
  }

}