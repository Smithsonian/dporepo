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
   * Getters for single records.
   * ----------------------------------------------------------------
   */

  /***
   * @param $params
   * @return mixed
   */
  public function getProject($params) {
    //$params will be something like array('project_id' => '123');
    $return_data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'project',
      'search_params' => array(
        0 => array('field_names' => array('project.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('project.project_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_id',
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
      'field_name' => 'api_published',
    );
    $query_params['fields'][] = array(
      'field_name' => 'api_discoverable',
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
      'field_name' => 'unit_stakeholder_id',
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

    $sql = "SELECT * FROM subject WHERE subject.active=1 and subject.subject_id= :subject_id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":subject_id", $params['record_id'], PDO::PARAM_STR);
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT model_purpose_description, sm.model_purpose_id FROM subject_model_purpose sm
      LEFT JOIN model_purpose on sm.model_purpose_id = model_purpose.model_purpose_id
      WHERE sm.subject_id= :subject_id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":subject_id", $params['record_id'], PDO::PARAM_STR);
    $statement->execute();
    $purpose_data = $statement->fetchAll(PDO::FETCH_ASSOC);

    $subject_model_purpose_data = array();
    foreach($purpose_data as $k => $p) {
      $desc = $p['model_purpose_description'];
      $id = $p['model_purpose_id'];
      $subject_model_purpose_data[$desc] = $id;
    }

    $data['access_model_purpose'] = is_array($subject_model_purpose_data) ? $subject_model_purpose_data : array();

    return $data;
  }

  public function saveSubject($params) {

    $user_id = array_key_exists('user_id', $params) ? $params['user_id'] : NULL;
    $subject_id = $this->saveRecord($params);

    $model_purpose_values = $params['values']['model_purpose_picker'];

    if(NULL !== $subject_id && $subject_id > 0) {
      // Delete existing values.
      $sql = "DELETE FROM subject_model_purpose
      WHERE subject_id= :subject_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":subject_id", $subject_id, PDO::PARAM_STR);
      $statement->execute();

      // Re-save new values.
      foreach($model_purpose_values as $k => $mp_id) {
        $sql = "INSERT INTO subject_model_purpose (model_purpose_id, subject_id, api_access, created_by_user_account_id, last_modified_user_account_id) 
        VALUES(:model_purpose_id, :subject_id, 1, :user_id, :user_id)";
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(":model_purpose_id", $mp_id, PDO::PARAM_STR);
        $statement->bindValue(":subject_id", $subject_id, PDO::PARAM_STR);
        $statement->bindValue(":user_id", $user_id, PDO::PARAM_STR);
        $statement->execute();
      }

    }
    return $subject_id;

  }

  public function saveItem($params) {

    $user_id = array_key_exists('user_id', $params) ? $params['user_id'] : NULL;
    $item_id = $this->saveRecord($params);

    $model_purpose_values = $params['values']['model_purpose_picker'];

    if(NULL !== $item_id && $item_id > 0) {
      // Delete existing values.
      $sql = "DELETE FROM item_model_purpose
      WHERE item_id= :item_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":item_id", $item_id, PDO::PARAM_STR);
      $statement->execute();

      // Re-save new values.
      foreach($model_purpose_values as $k => $mp_id) {
        $sql = "INSERT INTO item_model_purpose (model_purpose_id, item_id, api_access, created_by_user_account_id, last_modified_user_account_id) 
        VALUES(:model_purpose_id, :item_id, 1, :user_id, :user_id)";
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(":model_purpose_id", $mp_id, PDO::PARAM_STR);
        $statement->bindValue(":item_id", $item_id, PDO::PARAM_STR);
        $statement->bindValue(":user_id", $user_id, PDO::PARAM_STR);
        $statement->execute();
      }

    }
    return $item_id;

  }

  public function saveCaptureDataset($params) {

    $user_id = array_key_exists('user_id', $params) ? $params['user_id'] : NULL;
    $capture_dataset_id = $this->saveRecord($params);

    $model_purpose_values = $params['values']['model_purpose_picker'];

    if(NULL !== $capture_dataset_id && $capture_dataset_id > 0) {
      // Delete existing values.
      $sql = "DELETE FROM capture_dataset_model_purpose
      WHERE capture_dataset_id= :capture_dataset_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":capture_dataset_id", $capture_dataset_id, PDO::PARAM_STR);
      $statement->execute();

      // Re-save new values.
      foreach($model_purpose_values as $k => $mp_id) {
        $sql = "INSERT INTO capture_dataset_model_purpose (model_purpose_id, capture_dataset_id, api_access, created_by_user_account_id, last_modified_user_account_id) 
        VALUES(:model_purpose_id, :capture_dataset_id, 1, :user_id, :user_id)";
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(":model_purpose_id", $mp_id, PDO::PARAM_STR);
        $statement->bindValue(":capture_dataset_id", $capture_dataset_id, PDO::PARAM_STR);
        $statement->bindValue(":user_id", $user_id, PDO::PARAM_STR);
        $statement->execute();

      }
    }
    return $capture_dataset_id;

  }

  public function getDataForLookup($params) {

    $table = $params['table_name'];
    $value_field = $params['value_field'];
    $id_field = $params['id_field'];

    $sql = "SELECT " . $id_field. " as id, " . $value_field . " as val FROM " . $table . " WHERE active=1 ";
    $statement = $this->connection->prepare($sql);
    $statement->execute();
    $tmp = $statement->fetchAll(PDO::FETCH_ASSOC);

    $data = array();
    foreach($tmp as $k => $p) {
      $val = $p['val'];
      $id = $p['id'];
      $data[$val] = $id;
    }

    return $data;
  }

  public function getItem($params) {
      //$params will be something like array('item_id' => '123');
    $return_data = array();

      $query_params = array(
        'fields' => array(),
        'base_table' => 'item',
        'search_params' => array(
          0 => array('field_names' => array('item.active'), 'search_values' => array(1), 'comparison' => '='),
          1 => array('field_names' => array('item.item_id'), 'search_values' => $params, 'comparison' => '=')
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
        'field_name' => 'last_modified',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'item_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'project_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item',
        'field_name' => 'subject_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'item_type',
        'field_name' => 'label',
        'field_alias' => 'item_type_label',
      );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'api_published',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'api_discoverable',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'api_access_model_face_count_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'api_access_uv_map_size_id',
    );

      // Joins.
      $query_params['related_tables'][] = array(
        'table_name' => 'item_type',
        'table_join_field' => 'item_type_id',
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

    //@todo
    $return_data['inherit_api_published'] = NULL;
    $return_data['inherit_api_discoverable'] = NULL;
    if(isset($return_data['project_id'])) {
      $sql = "SELECT api_published, api_discoverable FROM project 
      WHERE project.project_id= :project_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":project_id", $return_data['project_id'], PDO::PARAM_STR);
      $statement->execute();
      $tmp = $statement->fetchAll(PDO::FETCH_ASSOC);

      if(count($tmp) > 0) {
        $return_data['inherit_api_published'] = $tmp[0]['api_published'];
        $return_data['inherit_api_discoverable'] = $tmp[0]['api_discoverable'];
      }
    }

    $sql = "SELECT model_purpose_description, im.model_purpose_id FROM item_model_purpose im
      LEFT JOIN model_purpose on im.model_purpose_id = model_purpose.model_purpose_id
      WHERE im.item_id= :item_id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":item_id", $params['item_id'], PDO::PARAM_STR);
    $statement->execute();
    $purpose_data = $statement->fetchAll(PDO::FETCH_ASSOC);

    $model_purpose_data = array();
    foreach($purpose_data as $k => $p) {
      $desc = $p['model_purpose_description'];
      $id = $p['model_purpose_id'];
      $model_purpose_data[$desc] = $id;
    }

    $return_data['api_access_model_purpose'] = is_array($model_purpose_data) ? $model_purpose_data : array();

      return $return_data;
  }

  public function getModel($params) {
    //$params will be something like array('model_id' => '123');
    $id = isset($params['model_id']) ? (int)$params['model_id'] : NULL;
    if (!isset($id)) return array();

    $data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'model',
      'search_params' => array(
        0 => array('field_names' => array('model.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('model.model_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'model_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'model_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'item_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'model',
      'field_name' => 'capture_dataset_id',
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

    $query_params['records_values'] = array();
    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $data = $ret[0];
    }
    if (empty($data)) return $data;


    // Get model files.
    $query_params = array(
      'fields' => array(),
      'base_table' => 'model_file',
      'search_params' => array(
        0 => array('field_names' => array('model_file.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('model_file.model_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'model_file',
      'field_name' => 'model_file_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'file_upload',
      'field_name' => 'file_name',
    );
    $query_params['fields'][] = array(
      'table_name' => 'file_upload',
      'field_name' => 'file_path',
    );
    $query_params['fields'][] = array(
      'table_name' => 'file_upload',
      'field_name' => 'file_hash',
    );

    // Joins.
    $query_params['related_tables'][] = array(
      'table_name' => 'file_upload',
      'table_join_field' => 'file_upload_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'model_file',
      'base_join_field' => 'file_upload_id',
    );

    $query_params['records_values'] = array();
    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    $file_data = array();
    if(array_key_exists(0, $ret)) {
      $file_data = $ret;
    }
    $data['files'] = $file_data;
    $data['viewable_model'] = false;
    foreach($file_data as $file) {
      $fn = $file['file_name'];
      $fn_exploded = explode('.', $fn);
      if(count($fn_exploded) == 2 && strtolower($fn_exploded[1]) == 'obj') {
        $data['viewable_model'] = $file;
    }
    }

    // End get model files.

    $data['capture_dataset'] = array();

    // Get all of the parent records.
    $record_type = !empty($data['capture_dataset_id']) ? 'model_with_capture_dataset_id' : 'model_with_item_id';

    // Execute.
    $data['parent_records'] = $this->getParentRecords(array(
      'base_record_id' => $id,
      'record_type' => $record_type,
    ));

    // Set the item ID.
    $item_id = $data['parent_records']['item_id'];

    // Example output of getParentRecords:
    // array(4) {
    //   ["project_id"]=>
    //   string(1) "2"
    //   ["subject_id"]=>
    //   string(3) "795"
    //   ["item_id"]=>
    //   string(4) "2570"
    //   ["model_id"]=>
    //   string(1) "9"
    // }

    if (!empty($data['capture_dataset_id'])) {
      // Get the capture dataset record.
      $capture_dataset = $this->getRecord(array(
          'base_table' => 'capture_dataset',
          'id_field' => 'capture_dataset_id',
          'id_value' => $data['capture_dataset_id'],
        )
      );
      // Modify the item ID and set the capture dataset.
      if (!empty($capture_dataset)) {
        $item_id = $capture_dataset['item_id'];
        $data['capture_dataset'] = $capture_dataset;
      }
    }

    // Get the item record.
    $item = $this->getRecord(array(
        'base_table' => 'item',
        'id_field' => 'item_id',
        'id_value' => $item_id,
      )
    );

    if (!empty($item)) {
      // Get the subject record.
      $subject = $this->getRecord(array(
          'base_table' => 'subject',
          'id_field' => 'subject_id',
          'id_value' => $item['subject_id'],
        )
      );

      if (!empty($subject)) {
        // Get the project record.
        $project = $this->getRecord(array(
            'base_table' => 'project',
            'id_field' => 'project_id',
            'id_value' => $subject['project_id'],
          )
        );

        $data['subject_name'] = $subject['subject_name'];
        $data['item_description'] = $item['item_description'];
        $data['project_name'] = $project['project_name'];
      }
    }

    return $data;
  }

  public function getCaptureDataset($params) {
    //$params will be something like array('capture_dataset_id' => '123');
    $return_data = array();

    $capture_dataset_id = array_key_exists('capture_dataset_id', $params) ? $params['capture_dataset_id'] : NULL;
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
          ,capture_dataset.item_id
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
          ,capture_dataset.directory_path
          ,capture_dataset.date_created
          ,capture_dataset.created_by_user_account_id
          ,capture_dataset.last_modified
          ,capture_dataset.last_modified_user_account_id
          ,capture_dataset.api_published
          ,capture_dataset.api_discoverable
          ,capture_dataset.api_access_model_face_count_id
          ,capture_dataset.api_access_uv_map_size_id
          ,capture_method.label AS capture_method_label
          ,dataset_type.label AS capture_dataset_type_label
          ,item_position_type.label_alias AS item_position_type_label
          ,focus_type.label AS focus_type_label
          ,light_source_type.label AS light_source_type_label
          ,background_removal_method.label AS background_removal_method_label
          ,camera_cluster_type.label AS camera_cluster_type_label
        FROM capture_dataset
        LEFT JOIN capture_method ON capture_method.capture_method_id = capture_dataset.capture_method
        LEFT JOIN dataset_type ON dataset_type.dataset_type_id = capture_dataset.capture_dataset_type
        LEFT JOIN item_position_type ON item_position_type.item_position_type_id = capture_dataset.item_position_type
        LEFT JOIN focus_type ON focus_type.focus_type_id = capture_dataset.focus_type
        LEFT JOIN light_source_type ON light_source_type.light_source_type_id = capture_dataset.light_source_type
        LEFT JOIN background_removal_method ON background_removal_method.background_removal_method_id = capture_dataset.background_removal_method
        LEFT JOIN camera_cluster_type ON camera_cluster_type.camera_cluster_type_id = capture_dataset.cluster_type
        WHERE capture_dataset.active = 1
        AND capture_dataset.capture_dataset_id = :capture_dataset_id";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":capture_dataset_id", $capture_dataset_id, PDO::PARAM_INT);
    $statement->execute();
    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }

    //@todo
    $return_data['inherit_api_published'] = NULL;
    $return_data['inherit_api_discoverable'] = NULL;
    if(isset($return_data['item_id'])) {
      $sql = "SELECT item.api_published, item.api_discoverable FROM item 
      LEFT JOIN capture_dataset on item.item_id = capture_dataset.item_id 
      WHERE capture_dataset.capture_dataset_id= :item_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":item_id", $return_data['item_id'], PDO::PARAM_STR);
      $statement->execute();
      $tmp = $statement->fetchAll(PDO::FETCH_ASSOC);

      if(count($tmp) > 0) {
        $return_data['inherit_api_published'] = $tmp[0]['api_published'];
        $return_data['inherit_api_discoverable'] = $tmp[0]['api_discoverable'];
      }
      else {
        // Get from project
        $sql = "SELECT p.api_published, p.api_discoverable FROM project p
          LEFT JOIN item on p.project_id = item.project_id
          WHERE item.item_id= :item_id";
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(":item_id", $return_data['item_id'], PDO::PARAM_STR);
        $statement->execute();
        $tmp = $statement->fetchAll(PDO::FETCH_ASSOC);
        if(count($tmp) > 0) {
          $return_data['inherit_api_published'] = $tmp[0]['api_published'];
          $return_data['inherit_api_discoverable'] = $tmp[0]['api_discoverable'];
        }
      }
    }

    $sql = "SELECT model_purpose_description, cm.model_purpose_id FROM capture_dataset_model_purpose cm
      LEFT JOIN model_purpose on cm.model_purpose_id = model_purpose.model_purpose_id
      WHERE cm.capture_dataset_id= :capture_dataset_id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":capture_dataset_id", $params['capture_dataset_id'], PDO::PARAM_STR);
    $statement->execute();
    $purpose_data = $statement->fetchAll(PDO::FETCH_ASSOC);

    $model_purpose_data = array();
    foreach($purpose_data as $k => $p) {
      $desc = $p['model_purpose_description'];
      $id = $p['model_purpose_id'];
      $model_purpose_data[$desc] = $id;
    }

    $return_data['api_access_model_purpose'] = is_array($model_purpose_data) ? $model_purpose_data : array();

    return $return_data;
  }

  public function getCaptureDataElement($params) {
    //$params will be something like array('capture_data_element_id' => '123');
    $return_data = array();
//@TODO HERE
    $capture_data_element_id = array_key_exists('capture_data_element_id', $params) ? $params['capture_data_element_id'] : NULL;
    $sql = "SELECT
          capture_data_element.capture_data_element_id
          ,capture_data_element.capture_dataset_id
          ,capture_data_element.capture_device_configuration_id
          ,capture_data_element.capture_device_field_id
          ,capture_data_element.capture_sequence_number
          ,capture_data_element.cluster_position_field_id
          ,capture_data_element.position_in_cluster_field_id
          ,capture_data_element.date_created
          ,capture_data_element.created_by_user_account_id
          ,capture_data_element.last_modified
          ,capture_data_element.last_modified_user_account_id          
          , ( SELECT GROUP_CONCAT(file_upload.metadata) from file_upload 
                LEFT JOIN capture_data_file on file_upload.file_upload_id = capture_data_file.file_upload_id
                WHERE capture_data_file.capture_data_element_id = capture_data_element.capture_data_element_id                
                GROUP BY capture_data_file.capture_data_element_id
            )
              as metadata
        FROM capture_data_element
        WHERE capture_data_element.active = 1
        AND capture_data_element.capture_data_element_id = :capture_data_element_id";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":capture_data_element_id", $capture_data_element_id, PDO::PARAM_INT);
    $statement->execute();
    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }

    return $return_data;
  }

  public function getCaptureDevice($params) {
    //$params will be something like array('capture_device_id' => '123');
    $return_data = array();

    $capture_device_id = array_key_exists('capture_device_id', $params) ? $params['capture_device_id'] : NULL;
    $sql = "SELECT
              capture_device.capture_device_id,
              capture_device.capture_data_element_id,
              capture_device.calibration_file,
              capture_device.capture_device_component_ids,
              capture_data_element.capture_dataset_id,
              capture_dataset.item_id,
              item.subject_id,
              item.project_id
            FROM capture_device
            LEFT JOIN capture_data_element ON capture_data_element.capture_data_element_id = capture_device.capture_data_element_id
            LEFT JOIN capture_dataset ON capture_dataset.capture_dataset_id = capture_data_element.capture_dataset_id
            LEFT JOIN item ON item.item_id = capture_dataset.item_id
            LEFT JOIN subject ON item.subject_id =subject.subject_id
            WHERE capture_device.active = 1
            AND capture_device.capture_device_id = :capture_device_id";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":capture_device_id", $capture_device_id, PDO::PARAM_INT);
    $statement->execute();
    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getPhotogrammetryScaleBarTargetPair($params) {
    //$params will be something like array('photogrammetry_scale_bar_target_pair_id' => '123');
    $return_data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'photogrammetry_scale_bar_target_pair',
      'search_params' => array(
        0 => array('field_names' => array('photogrammetry_scale_bar_target_pair.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('photogrammetry_scale_bar_target_pair.photogrammetry_scale_bar_target_pair_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'photogrammetry_scale_bar_target_pair_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'photogrammetry_scale_bar_id',
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
      'field_name' => 'subject_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'project_id',
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
      'table_join_field' => 'photogrammetry_scale_bar_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'photogrammetry_scale_bar_target_pair',
      'base_join_field' => 'photogrammetry_scale_bar_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'capture_dataset',
      'table_join_field' => 'capture_dataset_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'photogrammetry_scale_bar',
      'base_join_field' => 'capture_dataset_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'item',
      'table_join_field' => 'item_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'item_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'subject',
      'table_join_field' => 'subject_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'subject_id',
    );

    $ret = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    if(array_key_exists(0, $ret)) {
      $return_data = $ret[0];
    }
    return $return_data;
  }

  public function getPhotogrammetryScaleBar($params) {
    //$params will be something like array('photogrammetry_scale_bar_id' => '123');
    $return_data = array();

    $query_params = array(
      'fields' => array(),
      'base_table' => 'photogrammetry_scale_bar',
      'search_params' => array(
        0 => array('field_names' => array('photogrammetry_scale_bar.active'), 'search_values' => array(1), 'comparison' => '='),
        1 => array('field_names' => array('photogrammetry_scale_bar.photogrammetry_scale_bar_id'), 'search_values' => $params, 'comparison' => '=')
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    // Fields.
    /*
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar_target_pair',
      'field_name' => 'photogrammetry_scale_bar_id',
    );
    */
    $query_params['fields'][] = array(
      'field_name' => 'photogrammetry_scale_bar_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'photogrammetry_scale_bar',
      'field_name' => 'capture_dataset_id',
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
      'field_name' => 'item_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'subject_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'project_id',
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
      'table_join_field' => 'capture_dataset_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'photogrammetry_scale_bar',
      'base_join_field' => 'capture_dataset_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'item',
      'table_join_field' => 'item_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'item_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'subject',
      'table_join_field' => 'subject_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'subject_id',
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
      'id_field' => $record_type . '_id',
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
      'field_name' => 'unit_stakeholder_id',
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
   * Getters for multiple records.
   * ----------------------------------------------------------------
   */
  public function getDatasets($params) {

    $item_id = array_key_exists('item_id', $params) ? $params['item_id'] : NULL;

    $query_params = array(
      'fields' => array(),
      'base_table' => 'capture_dataset',
      'search_params' => array(
        0 => array('field_names' => array('capture_dataset.active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND',
      'related_tables' => array(),
    );

    if($item_id && is_numeric($item_id)) {
      $query_params['search_params'][1] = array(
        'field_names' => array(
          'capture_dataset.item_id',
        ),
        'search_values' => array((int)$item_id),
        'comparison' => '=',
      );
    }

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => '*',
    );

    // Joins.
    $query_params['related_tables'][] = array(
      'table_name' => 'item',
      'table_join_field' => 'item_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'item_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'subject',
      'table_join_field' => 'subject_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'subject_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'project',
      'table_join_field' => 'project_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'project_id',
    );

    $query_params['records_values'] = array();
    $return_data = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    return $return_data;
  }

  public function getElementsForCaptureDataset($params) {

      $capture_dataset_id = array_key_exists('capture_dataset_id', $params) ? $params['capture_dataset_id'] : NULL;
      $sql = "SELECT
                project.project_id,
                subject.subject_id,
                item.item_id,
                capture_data_element.capture_data_element_id,
                capture_data_element.capture_dataset_id,
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
            LEFT JOIN capture_dataset ON capture_dataset.capture_dataset_id = capture_data_element.capture_dataset_id
            LEFT JOIN item ON item.item_id = capture_dataset.item_id
            LEFT JOIN subject ON subject.subject_id = item.subject_id
            LEFT JOIN project ON project.project_id = item.project_id
            WHERE capture_data_element.active = 1
            AND capture_data_element.capture_dataset_id = :capture_dataset_id";

      $statement = $this->connection->prepare($sql);

      $statement->bindValue(":capture_dataset_id", $capture_dataset_id, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  public function getItemsBySubjectId($params) {
    //$params will be something like array('subject_id' => '123');

    $subject_id = array_key_exists('subject_id', $params) ? $params['subject_id'] : NULL;
    $query_params = array(
      'base_table' => 'item',
      'related_tables' => array(
        0 =>
          array(
            'table_name' => 'subject',
            'table_join_field' => 'subject_id',
            'join_type' => 'LEFT JOIN',
            'base_join_table' => 'item',
            'base_join_field' => 'subject_id',
          ),
        1 => array(
          'table_name' => 'project',
          'table_join_field' => 'project_id',
          'join_type' => 'LEFT JOIN',
          'base_join_table' => 'item',
          'base_join_field' => 'project_id',
        )
      ),
      'fields' => array(
        0 => array(
          'table_name' => 'project',
          'field_name' => 'project_id',
        ),
        1 => array(
          'table_name' => 'subject',
          'field_name' => 'subject_id',
        ),
        2 => array(
          'table_name' => 'item',
          'field_name' => 'item_id',
        ),
        3 => array(
          'table_name' => 'item',
          'field_name' => 'item_guid',
        ),
        4 => array(
          'table_name' => 'item',
          'field_name' => 'subject_id',
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
          'field_name' => 'item_description',
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

    if($subject_id) {
      $query_params['search_params'][1] = array('field_names' => array('item.subject_id'), 'search_values' => array($subject_id), 'comparison' => '=');
    }

    $query_params['records_values'] = array();
    $return_data = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    return $return_data;
  }

  public function getItemGuidsBySubjectId($params) {
    //$params will be something like array('subject_id' => '123');

    $subject_id = array_key_exists('subject_id', $params) ? $params['subject_id'] : NULL;
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

    if($subject_id) {
      $query_params['search_params'][1] = array('field_names' => array('item.subject_id'), 'search_values' => array($subject_id), 'comparison' => '=');
    }

    $query_params['records_values'] = array();
    $return_data = $this->getRecords($query_params);
    //@todo do something if $ret has errors

    return $return_data;
  }

  /**
   * @param string $uuid The upload directory
   * @return array
   */
  public function getJobData($uuid = null, $function = '') {

    $data = array();

    if (!empty($uuid)) {
      // Query the database.
      $result = $this->getRecords(array(
        'base_table' => 'job',
        'fields' => array(
          array(
            'table_name' => 'job',
            'field_name' => 'job_id',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'uuid',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'project_id',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'job_label',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'job_type',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'job_status',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'created_by_user_account_id',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'date_created',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'date_completed',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'qa_required',
          ),
          array(
            'table_name' => 'job',
            'field_name' => 'qa_approved_time',
          ),
          array(
            'table_name' => 'fos_user',
            'field_name' => 'username',
          )
        ),
        // Joins
        'related_tables' => array(
          array(
            'table_name' => 'fos_user',
            'table_join_field' => 'id',
            'join_type' => 'LEFT JOIN',
            'base_join_table' => 'job',
            'base_join_field' => 'created_by_user_account_id',
          )
        ),
        'limit' => 1,
        'search_params' => array(
          0 => array('field_names' => array('uuid'), 'search_values' => array($uuid[0]), 'comparison' => '='),
        ),
        'search_type' => 'AND',
        'omit_active_field' => true
        )
      );
    }

    if (!empty($result)) {
      $data = $result[0];
    }

    return $data;
  }

  /**
   * @param string $params Possible params: job_id (uuid), status, date_completed.
   * @return bool
   */
  public function setJobStatus($params = array()) {

    $data = false;

    if (!empty($params['job_id']) && !empty($params['status']) && empty($params['date_completed'])) {
      $sql ="UPDATE job SET job_status = :status WHERE uuid = :job_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":job_id", $params['job_id'], PDO::PARAM_STR);
      $statement->bindValue(":status", $params['status'], PDO::PARAM_STR);
      $statement->execute();
      if($statement->rowCount() === 1) $data = true;
    }

    if (!empty($params['job_id']) && !empty($params['status']) && !empty($params['date_completed'])) {
      $sql ="UPDATE job SET job_status = :status, date_completed = :date_completed WHERE uuid = :job_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":job_id", $params['job_id'], PDO::PARAM_STR);
      $statement->bindValue(":status", $params['status'], PDO::PARAM_STR);
      $statement->bindValue(":date_completed", $params['date_completed'], PDO::PARAM_STR);
      $statement->execute();
      if($statement->rowCount() === 1) $data = true;
    }

    return $data;
  }

  /**
   * @param string $uploads_directory The upload directory
   * @return array Import result and/or any messages
   */
  public function getImportedItems($params) {
    $sql = "SELECT SUM(case when job_import_record.record_table = 'subject' then 1 else 0 end) AS subjects_total,
      SUM(case when job_import_record.record_table = 'item' then 1 else 0 end) AS items_total,
      SUM(case when job_import_record.record_table = 'capture_dataset' then 1 else 0 end) AS capture_datasets_total,
      SUM(case when job_import_record.record_table = 'model' then 1 else 0 end) AS models_total,
      job_import_record.record_table,
      job.job_label,
      job.date_created,
      job.date_completed,
      job.job_status,
      fos_user.username as fos_user_username
      FROM job_import_record
      LEFT JOIN job ON job.job_id = job_import_record.job_id
      LEFT JOIN fos_user ON fos_user.id = job.created_by_user_account_id
      WHERE job_import_record.job_id = :job_id
      GROUP BY job_import_record.job_id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":job_id", $params['job_id'], PDO::PARAM_INT);
    $statement->execute();
    return $statement->fetch();
  }

  /**
   * @param array $params Parameters - only the job UUID for now.
   * @return array Results from the database
   */
  public function purgeImportedData($params = array()) {

    $data = array();

    if (!empty($params) && !empty($params['uuid'])) {

      // Get the job's data via job.uuid.
      $job_data = $this->getJobData(array($params['uuid']));

      if (!empty($job_data)) {

        $table_names = array(
          'data_tables' => array(
            'subject',
            'item',
            'capture_dataset',
            'capture_data_element',
            'capture_data_file',
            'model'
          ),
          'job_and_file_tables' => array(
            'job',
            'job_import_record',
            'job_log',
            'file_upload'
          ),
          'processing_job_tables' => array(
            'processing_job'
          ),
        );

        // Remove data from tables containing repository data.
        foreach ($table_names['data_tables'] as $data_table_name) {
          // Remove records.
          $sql_data = "DELETE FROM {$data_table_name}
            WHERE {$data_table_name}.{$data_table_name}_id IN (SELECT record_id
            FROM job_import_record
            WHERE job_import_record.job_id = :job_id
            AND job_import_record.record_table = '{$data_table_name}')";
          $statement = $this->connection->prepare($sql_data);
          $statement->bindValue(":job_id", $job_data['job_id'], PDO::PARAM_INT);
          $statement->execute();
          $data[ $data_table_name ] = $statement->rowCount();
          // Reset the auto increment value.
          $sql_data_reset = "ALTER TABLE {$data_table_name} MODIFY {$data_table_name}.{$data_table_name}_id INT(11) UNSIGNED;
          ALTER TABLE {$data_table_name} MODIFY {$data_table_name}.{$data_table_name}_id INT(11) UNSIGNED AUTO_INCREMENT";
          $statement = $this->connection->prepare($sql_data_reset);
          $statement->execute();
        }

        // Remove data from tables containing job-based data.
        foreach ($table_names['job_and_file_tables'] as $job_table_name) {
          // Remove records.
          $sql_job = "DELETE FROM {$job_table_name} WHERE {$job_table_name}.job_id = :job_id";
          $statement = $this->connection->prepare($sql_job);
          $statement->bindValue(":job_id", $job_data['job_id'], PDO::PARAM_INT);
          $statement->execute();
          $data[ $job_table_name ] = $statement->rowCount();
          // Reset the auto increment value.
          $sql_job_reset = "ALTER TABLE {$job_table_name} MODIFY {$job_table_name}.{$job_table_name}_id INT(11) UNSIGNED;
          ALTER TABLE {$job_table_name} MODIFY {$job_table_name}.{$job_table_name}_id INT(11) UNSIGNED AUTO_INCREMENT";
          $statement = $this->connection->prepare($sql_job_reset);
          $statement->execute();
        }

        // Remove data from tables containing processing job-based data.
        // foreach ($table_names['processing_job_tables'] as $processing_job_table_name) {
        //   // Remove records.
        //   $sql_job = "DELETE pj, pjf FROM {$processing_job_table_name} pj
        //     LEFT JOIN `processing_job_file` pjf ON pjf.`job_id` = pj.`processing_service_job_id`
        //     WHERE pj.`job_id` = 3";
        //   $statement = $this->connection->prepare($sql_job);
        //   $statement->bindValue(":job_id", $job_data['job_id'], PDO::PARAM_INT);
        //   $statement->execute();
        //   $data[ $processing_job_table_name ] = $statement->rowCount();
        //   // Reset the auto increment value.
        //   $sql_job_reset = "ALTER TABLE {$processing_job_table_name} MODIFY {$processing_job_table_name}.{$processing_job_table_name}_id INT(11) UNSIGNED;
        //   ALTER TABLE {$processing_job_table_name} MODIFY {$processing_job_table_name}.{$processing_job_table_name}_id INT(11) UNSIGNED AUTO_INCREMENT";
        //   $statement = $this->connection->prepare($sql_job_reset);
        //   $statement->execute();
        // }

      }

    }

    return $data;
  }

  public function getStakeholderGuids() {
    $sql = "
      SELECT project.project_id
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
        'field_names' => array($record_type . '_id'),
        'search_values' => array($record_id)
        ),
      )
    );
    return $data;
  }

  /**
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
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
              'capture_data_element_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'capture_dataset_rights':
        // LEFT JOIN to get the Data Rights Restriction Type.
        $query_params['related_tables'][] = array(
          'table_name' => 'data_rights_restriction_type',
          'table_join_field' => 'data_rights_restriction_type_id',
          'join_type' => 'LEFT JOIN',
          'base_join_table' => $record_type,
          'base_join_field' => 'data_rights_restriction',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
          'field_alias' => 'manage',
        );
        // The Data Rights Restriction Type.
        $query_params['fields'][] = array(
          'table_name' => 'data_rights_restriction_type',
          'field_name' => 'label',
          'field_alias' => 'data_rights_restriction'
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
          'field_name' => $record_type . '_id',
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
              'capture_dataset_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'capture_device':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
              'capture_device_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'item_position_type':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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

        // If we're showing all models pertaining to an Item,
        // we need to show models that relate to the Item's Capture Datasets as well.
        if($parent_id_field == 'item_id') {
          return $this->getDatatableItemModels($params);
        }

        /*
        $query_params['related_tables'][] = array(
          'table_name' => 'model_file',
          'table_join_field' => 'model_id',
          'join_type' => 'LEFT JOIN',
          'base_join_table' => 'model',
          'base_join_field' => 'model_id',
        );
        $query_params['related_tables'][] = array(
          'table_name' => 'file_upload',
          'table_join_field' => 'file_upload_id',
          'join_type' => 'LEFT JOIN',
          'base_join_table' => 'model_file',
          'base_join_field' => 'file_upload_id',
        );
        */
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'capture_dataset_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'parent_model_id',
        );

        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'item_id',
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
        /*
        $query_params['fields'][] = array(
          'table_name' => 'file_upload',
          'field_name' => 'file_path',
        );
        $query_params['fields'][] = array(
          'table_name' => 'file_upload',
          'field_name' => 'file_hash',
        );
        */
      $query_params['fields'][] = array(
        'table_name' => $record_type,
        'field_name' => 'active',
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
        'field_name' => $record_type . '_id',
        'field_alias' => 'DT_RowId',
      );

      $query_params['search_params'][0] = array(
        'field_names' => array($record_type . '.active'),
        'search_values' => array(1),
        'comparison' => '='
      );
      if (NULL !== $search_value) {
        $query_params['search_params'][] = array(
          'field_names' => array(
            $record_type . '.model_guid',
            $record_type . '.parent_model_id',
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
            //$record_type . '.file_path',
          ),
          'search_values' => array($search_value),
          'comparison' => 'LIKE',
        );
      }
      if (isset($parent_id_field) && NULL !== $parent_id) {
        //$c = count($query_params['search_params']);
        $query_params['search_params'][] = array(
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

      case 'model_file':
      $parent_id_field = "model_id";
      $query_params['related_tables'][] = array(
        'table_name' => 'file_upload',
        'table_join_field' => 'file_upload_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'model_file',
        'base_join_field' => 'file_upload_id',
      );

        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
          'field_alias' => 'manage',
        );

        $query_params['fields'][] = array(
        'table_name' => 'file_upload',
        'field_name' => 'file_name',
        );
        $query_params['fields'][] = array(
        'table_name' => 'file_upload',
        'field_name' => 'file_path',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'active',
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
          'field_name' => $record_type . '_id',
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
            $record_type . '.file_name',
            $record_type . '.file_path',
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
          'field_name' => $record_type . '_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'model_id',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'preceding_processing_action_id',
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
          'field_name' => $record_type . '_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');
        if (NULL !== $search_value) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              $record_type . '.preceding_processing_action_id',
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
              'model_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'photogrammetry_scale_bar':
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
              'capture_dataset_id',
            ),
            'search_values' => array($parent_id),
            'comparison' => '=',
          );
        }
        break;

      case 'photogrammetry_scale_bar_target_pair':

        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
          'field_alias' => 'DT_RowId',
        );

        $query_params['search_params'][0] = array('field_names' => array($record_type . '.active'), 'search_values' => array(1), 'comparison' => '=');

        if (NULL !== $parent_id) {
          $c = count($query_params['search_params']);
          $query_params['search_params'][$c] = array(
            'field_names' => array(
              'photogrammetry_scale_bar_id',
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
          'field_name' => $record_type . '_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => $record_type,
          'field_name' => 'project_id',
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
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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
            'model_id',
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
          'field_name' => $record_type . '_id',
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
          'field_name' => $record_type . '_id',
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

    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : 'asc';
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : 0;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : 20;

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $project_ids = array_key_exists('project_ids', $params) ? $params['project_ids'] : NULL;
    $date_range_start = array_key_exists('date_range_start', $params) ? $params['date_range_start'] : NULL;
    $date_range_end = array_key_exists('date_range_end', $params) ? $params['date_range_end'] : NULL;

    $select_sql = " DISTINCT 
          project.project_id as manage, project.project_id, project.project_name, project.stakeholder_guid, 
          project.date_created, project.last_modified, project.active, project.project_id as DT_RowId, 
          isni_data.isni_label as stakeholder_label, COUNT(item.item_id) as items_count
          FROM project 
          LEFT JOIN isni_data ON project.stakeholder_guid = isni_data.isni_id 
          LEFT JOIN item ON project.project_id = item.project_id
          ";

    $where_sql = " WHERE (project.active = 1) AND (item.active = 1 OR item.active IS NULL) ";
    if(NULL !== $search_value) {
      $where_sql .= " AND (
        project.project_name LIKE :search_value
        OR isni_data.isni_label LIKE :search_value
        OR project.date_created LIKE :search_value
        OR project.last_modified LIKE :search_value
      )";
    }
    if (NULL !== $project_ids && is_array($project_ids)) {
      $project_ids_placeholder = array();
      foreach($project_ids as $k => $pid) {
        $project_ids_placeholder[] = ':project_id_' . $k;
      }
      if(count($project_ids_placeholder) > 0) {
        $where_sql .= " AND ( project.project_id IN (" . implode(',', $project_ids_placeholder) . " ) )";
      }
    }

    if(NULL !== $date_range_start) {
      $where_sql .= " AND (project.last_modified < :date_range_start) ";
    }
    if(NULL !== $date_range_end) {
      $where_sql .= " AND (project.last_modified > :date_range_end) ";
    }

    $where_sql .= " GROUP BY project.project_id ";
    $sql = "SELECT SQL_CALC_FOUND_ROWS "
      . $select_sql. $where_sql;

    if($sort_field) {
      $sql .= " ORDER BY " . $sort_field . " " . $sort_order;
    }
    else {
      $sql .= " ORDER BY project_name";
    }

    if(NULL !== $stop_record) {
      $sql .= " LIMIT {$start_record}, {$stop_record} ";
    }

    $statement = $this->connection->prepare($sql);
    if(strlen(trim($search_value)) > 0) {
      //$statement->bindValue(":search_value", "%", PDO::PARAM_STR);
      $statement->bindValue(":search_value", '%' . $search_value . '%', PDO::PARAM_STR);
    }
    if (NULL !== $project_ids && is_array($project_ids)) {
      foreach($project_ids as $k => $pid) {
        $statement->bindValue(":project_id_" . $k, $pid, PDO::PARAM_INT);
      }
    }
    if(NULL !== $date_range_start) {
      $statement->bindValue(":date_range_start", $date_range_start);
    }
    if(NULL !== $date_range_end) {
      $statement->bindValue(":date_range_end", $date_range_end);
    }
    $statement->execute();
    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->connection->prepare("SELECT FOUND_ROWS()");
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["FOUND_ROWS()"];
    $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

    return $data;

  }

  /**
   * @param $params
   * @return mixed
   */
  public function getDatatableSubject($params) {

    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : 'asc';
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : 0;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : 20;

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $project_ids = array_key_exists('project_ids', $params) ? $params['project_ids'] : NULL;
    $date_range_start = array_key_exists('date_range_start', $params) ? $params['date_range_start'] : NULL;
    $date_range_end = array_key_exists('date_range_end', $params) ? $params['date_range_end'] : NULL;

    $select_sql = " DISTINCT 
          subject.subject_id as manage, subject.subject_id, subject.subject_name, 
          subject.holding_entity_guid, subject.local_subject_id, subject.subject_name, subject.subject_display_name,
          subject.subject_guid, subject.last_modified, subject.active, subject.subject_id as DT_RowId, 
          COUNT(item.item_id) as items_count
          FROM subject 
          LEFT JOIN item ON subject.subject_id = item.subject_id
          ";

    $where_sql = " WHERE (subject.active = 1) AND (item.active = 1 OR item.active IS NULL) ";
    if(NULL !== $search_value) {
      $where_sql .= " AND (
        subject.subject_name LIKE :search_value
        OR subject.subject_name LIKE :search_value
        OR subject.subject_display_name LIKE :search_value
        OR subject.holding_entity_guid LIKE :search_value
        OR subject.subject_guid LIKE :search_value
      )";
    }

    if(NULL !== $date_range_start) {
      $where_sql .= " AND (subject.last_modified < :date_range_start) ";
    }
    if(NULL !== $date_range_end) {
      $where_sql .= " AND (subject.last_modified > :date_range_end) ";
    }

    $where_sql = $where_sql . " GROUP BY subject.subject_id ";


    $sql = "SELECT SQL_CALC_FOUND_ROWS "
      . $select_sql. $where_sql;


    if($sort_field) {
      $sql .= " ORDER BY " . $sort_field . " " . $sort_order;
    }
    else {
      $sql .= " ORDER BY subject.last_modified DESC";
    }

    if(NULL !== $stop_record) {
      $sql .= " LIMIT {$start_record}, {$stop_record} ";
    }

    $statement = $this->connection->prepare($sql);
    if(strlen(trim($search_value)) > 0) {
      //$statement->bindValue(":search_value", "%", PDO::PARAM_STR);
      $statement->bindValue(":search_value", '%' . $search_value . '%', PDO::PARAM_STR);
    }
    if (NULL !== $project_ids && is_array($project_ids)) {
      foreach($project_ids as $k => $pid) {
        $statement->bindValue(":project_id_" . $k, $pid, PDO::PARAM_INT);
      }
    }
    if(NULL !== $date_range_start) {
      $statement->bindValue(":date_range_start", $date_range_start);
    }
    if(NULL !== $date_range_end) {
      $statement->bindValue(":date_range_end", $date_range_end);
    }
    $statement->execute();
    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->connection->prepare("SELECT FOUND_ROWS()");
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["FOUND_ROWS()"];
    $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

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

    // GROUP BY subjects.holding_entity_guid, subjects.local_subject_id, subjects.subject_guid, subjects.subject_name, subjects.last_modified, subjects.active, subjects.subject_id
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
      'table_join_field' => 'subject_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'subject',
      'base_join_field' => 'subject_id',
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
      'field_name' => 'subject_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_id',
      'field_alias' => 'DT_RowId',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_id',
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
  public function getDatatableCaptureDataset($params) {

    $item_id = array_key_exists('item_id', $params) ? $params['item_id'] : NULL;
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
      'table_join_field' => 'capture_method_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'capture_method',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'dataset_type',
      'table_join_field' => 'dataset_type_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'capture_dataset_type',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'item_position_type',
      'table_join_field' => 'item_position_type_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'item_position_type',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'focus_type',
      'table_join_field' => 'focus_type_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'focus_type',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'light_source_type',
      'table_join_field' => 'light_source_type_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'light_source_type',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'background_removal_method',
      'table_join_field' => 'background_removal_method_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'capture_dataset',
      'base_join_field' => 'background_removal_method',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'camera_cluster_type',
      'table_join_field' => 'camera_cluster_type_id',
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
          'capture_dataset.directory_path',
          'capture_dataset.workflow_status',
          'capture_dataset.workflow_status_detail',
          'capture_dataset.workflow_processing_step',
          'capture_dataset.date_created',
          'capture_dataset.created_by_user_account_id',
          'capture_dataset.last_modified',
          'capture_dataset.last_modified_user_account_id',
        ),
        'search_values' => array($search_value),
        'comparison' => 'LIKE',
      );
    }

    if($item_id && is_numeric($item_id)) {
      $count_params = count($query_params['search_params']);
      $query_params['search_params'][$count_params] = array(
        'field_names' => array(
          'capture_dataset.item_id',
        ),
        'search_values' => array((int)$item_id),
        'comparison' => '=',
      );
      //          AND capture_dataset.item_id = " . (int)$item_id . "");
    }

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'capture_dataset',
      'field_name' => 'capture_dataset_id',
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
      'field_name' => 'directory_path',
    );

    $query_params['fields'][] = array(
      'field_name' => 'workflow_id',
    );
    $query_params['fields'][] = array(
      'field_name' => 'workflow_status',
    );
    $query_params['fields'][] = array(
      'field_name' => 'workflow_status_detail',
    );
    $query_params['fields'][] = array(
      'field_name' => 'workflow_processing_step',
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
      'field_name' => 'capture_dataset_id',
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

      $capture_dataset_id = array_key_exists('capture_dataset_id', $params) ? $params['capture_dataset_id'] : NULL;
      $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
      $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
      $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : 0;
      $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

      $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
      //@todo- allow match on ID- specify ID field and value $record_match = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;

      $sql = "
            capture_data_element.capture_data_element_id
            ,capture_data_element.capture_data_element_id as manage
            ,capture_data_element.capture_data_element_id as DT_RowId
            ,capture_data_element.capture_dataset_id
            ,capture_data_element.capture_device_configuration_id
            ,capture_data_element.capture_device_field_id
            ,capture_data_element.capture_sequence_number
            ,capture_data_element.cluster_position_field_id
            ,capture_data_element.position_in_cluster_field_id
            ,capture_data_element.date_created
            ,capture_data_element.created_by_user_account_id
            ,capture_data_element.last_modified
            ,capture_data_element.last_modified_user_account_id          
            , ( SELECT GROUP_CONCAT(file_upload.metadata) from file_upload 
                  LEFT JOIN capture_data_file on file_upload.file_upload_id = capture_data_file.file_upload_id    
                  WHERE capture_data_file.capture_data_element_id = capture_data_element.capture_data_element_id
                  AND file_upload.metadata IS NOT NULL AND file_upload.metadata NOT LIKE ''              
                  GROUP BY capture_data_file.capture_data_element_id
              )
                as metadata
          FROM capture_data_element
          WHERE capture_data_element.active = 1 ";

      if(strlen(trim($search_value)) > 0) {
        $sql .= " AND (
          capture_device_configuration_id LIKE :search_value OR
          capture_device_field_id LIKE :search_value OR
          capture_sequence_number LIKE :search_value OR
          cluster_position_field_id LIKE :search_value OR
          position_in_cluster_field_id LIKE :search_value
        ) ";

      }

      if(NULL !== $capture_dataset_id) {
        $sql .= " AND capture_data_element.capture_dataset_id = :capture_dataset_id";
      }

      if($sort_field) {
        $sql .= " ORDER BY " . $sort_field . " " . $sort_order;
      }
      else {
        $sql .= " ORDER BY capture_data_element_id ";
      }

      if(NULL !== $stop_record) {
        $sql .= " LIMIT {$start_record}, {$stop_record} ";
      }
      else {
        $sql .= " LIMIT {$start_record} ";
      }

      $sql = "SELECT SQL_CALC_FOUND_ROWS " . $sql;

      $statement = $this->connection->prepare($sql);
      if(strlen(trim($search_value)) > 0) {
        $statement->bindValue(":search_value", $search_value, PDO::PARAM_STR);
      }
      if(NULL !== $capture_dataset_id) {
        $statement->bindValue(":capture_dataset_id", $capture_dataset_id, PDO::PARAM_INT);
      }

      $statement->execute();
      $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

      $statement = $this->connection->prepare("SELECT FOUND_ROWS()");
      $statement->execute();
      $count = $statement->fetch(PDO::FETCH_ASSOC);
      $data["iTotalRecords"] = $count["FOUND_ROWS()"];
      $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

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

    $sql = "
          capture_data_file.capture_data_file_id
          ,capture_data_file.capture_data_file_id as manage
          ,capture_data_file.capture_data_file_id as DT_RowId
          ,capture_data_file.capture_data_file_name
          ,capture_data_file.capture_data_file_type
          ,capture_data_file.is_compressed_multiple_files
          ,capture_data_file.date_created
          ,capture_data_file.created_by_user_account_id
          ,capture_data_file.last_modified
          ,capture_data_file.last_modified_user_account_id          
          , file_upload.metadata
        FROM capture_data_file
        LEFT JOIN file_upload ON capture_data_file.file_upload_id = file_upload.file_upload_id
        WHERE capture_data_file.active = 1 
        ";

    if(NULL !== $parent_id) {
      $sql .= " AND capture_data_file.capture_data_element_id = :capture_data_element_id ";
    }

    if(strlen(trim($search_value)) > 0) {
      $sql .= " AND (
        capture_data_file_name LIKE :search_value OR
        capture_data_file_type LIKE :search_value OR
        is_compressed_multiple_files LIKE :search_value
      )";
    }

    if($sort_field) {
      $sql .= " ORDER BY " . $sort_field . " " . $sort_order;
    }
    else {
      $sql .= " ORDER BY capture_data_file_id ";
    }

    if(NULL !== $stop_record) {
      $sql .= " LIMIT {$start_record}, {$stop_record} ";
    }
    else {
      $sql .= " LIMIT {$start_record} ";
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . $sql;

    $statement = $this->connection->prepare($sql);

    $statement = $this->connection->prepare($sql);
    if(strlen(trim($search_value)) > 0) {
      $statement->bindValue(":search_value", $search_value, PDO::PARAM_STR);
    }
    if(NULL !== $parent_id) {
      $statement->bindValue(":capture_data_element_id", $parent_id, PDO::PARAM_INT);
    }

    $statement->execute();
    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->connection->prepare("SELECT FOUND_ROWS()");
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["FOUND_ROWS()"];
    $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

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
    $project_id = array_key_exists('project_id', $params) ? $params['project_id'] : NULL;
    $subject_id = array_key_exists('subject_id', $params) ? $params['subject_id'] : NULL;

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
        ),
        'search_values' => array($search_value),
        'comparison' => 'LIKE',
      );
    }
    if($project_id && is_numeric($project_id)) {
      $count_params = count($query_params['search_params']);
      $query_params['search_params'][$count_params] = array(
        'field_names' => array(
          'item.project_id',
        ),
        'search_values' => array((int)$project_id),
        'comparison' => '=',
      );
    }
    if($subject_id && is_numeric($subject_id)) {
      $count_params = count($query_params['search_params']);
      $query_params['search_params'][$count_params] = array(
        'field_names' => array(
          'item.subject_id',
        ),
        'search_values' => array((int)$subject_id),
        'comparison' => '=',
      );
    }

    $query_params['related_tables'][] = array(
      'table_name' => 'capture_dataset',
      'table_join_field' => 'item_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'item',
      'base_join_field' => 'item_id',
    );

    // Fields.
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'item_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'subject_id',
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
      'field_name' => 'date_created',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'last_modified',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'item_id',
      'field_alias' => 'DT_RowId',
    );
    $query_params['fields'][] = array(
      'field_name' => 'count(distinct capture_dataset.item_id)',
      'field_alias' => 'datasets_count',
    );

    // Need to group by since we're doing a count
    $query_params['group_by'] = array(
      'item.item_id'
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

  public function getDatatableItemModels($params) {

    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    $parent_id_field = array_key_exists('parent_id_field', $params) ? $params['parent_id_field'] : NULL;
    $parent_id = array_key_exists('parent_id', $params) ? $params['parent_id'] : NULL;

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;

    if($parent_id_field != 'item_id') {
      return array();
    }

    $sql = " DISTINCT tmp.*
            FROM 
    
            (SELECT model_id, model_id as manage, model.capture_dataset_id,
            parent_model_id, item_id, model_guid, date_of_creation, model_file_type,
            derived_from, creation_method, model_modality, units, is_watertight, model_purpose, point_count,
            has_normals, face_count, vertices_count, has_vertex_color, has_uv_space, model_maps, active,
            date_created, created_by_user_account_id, last_modified, last_modified_user_account_id,
            model_id as DT_RowId,           
            (
              SELECT file_name
              FROM file_upload
              LEFT JOIN model_file on file_upload.file_upload_id = model_file.file_upload_id
              WHERE file_upload.active = 1 AND model_file.active = 1
              AND model_file.model_id=model.model_id              
              AND (file_upload.file_name LIKE '%.obj' OR file_upload.file_name IS NULL)
              LIMIT 0, 1
            ) as file_name
            
            FROM model
            
            WHERE item_id=:item_id

            UNION 
            
            SELECT model_id, model_id as manage, model.capture_dataset_id,
            parent_model_id, model.item_id, model_guid, date_of_creation, model_file_type,
            derived_from, creation_method, model_modality, units, is_watertight, model_purpose, point_count,
            has_normals, face_count, vertices_count, has_vertex_color, has_uv_space, model_maps, model.active,
            model.date_created, model.created_by_user_account_id, model.last_modified, model.last_modified_user_account_id,
            model_id as DT_RowId,
            (
              SELECT file_name
              FROM file_upload
              LEFT JOIN model_file on file_upload.file_upload_id = model_file.file_upload_id
              WHERE file_upload.active = 1 AND model_file.active = 1
              AND model_file.model_id=model.model_id              
              AND (file_upload.file_name LIKE '%.obj' OR file_upload.file_name IS NULL)
              LIMIT 0, 1
            ) as file_name
            
            FROM model
            LEFT JOIN capture_dataset on model.capture_dataset_id = capture_dataset.capture_dataset_id
            
            WHERE 
            capture_dataset.item_id=:item_id
            and capture_dataset.active=1
             
            )
            AS tmp
            
            WHERE tmp.active = 1 
             
            ";
    //@todo instead of looking at the extension, check for model = web ready, and check resolution is viewable

    if(strlen(trim($search_value)) > 0) {
      $sql .= " AND (
        model_guid LIKE :search_value OR
        parent_model_id LIKE :search_value OR
        date_of_creation LIKE :search_value OR
        model_file_type LIKE :search_value OR
        derived_from LIKE :search_value OR
        creation_method LIKE :search_value OR
        model_modality LIKE :search_value OR
        units LIKE :search_value OR
        is_watertight LIKE :search_value OR
        model_purpose LIKE :search_value OR
        point_count LIKE :search_value OR
        has_normals LIKE :search_value OR
        face_count LIKE :search_value OR
        vertices_count LIKE :search_value OR
        has_vertex_color LIKE :search_value OR
        has_uv_space LIKE :search_value OR
        model_maps LIKE :search_value
      )";
    }

    if($sort_field) {
      $sql .= " ORDER BY " . $sort_field . " " . $sort_order;
    }
    else {
      $sql .= " ORDER BY model_id ";
    }

    if(NULL !== $stop_record) {
      $sql .= " LIMIT {$start_record}, {$stop_record} ";
    }
    else {
      $sql .= " LIMIT {$start_record} ";
    }

    $sql = "SELECT SQL_CALC_FOUND_ROWS " . $sql;

    $statement = $this->connection->prepare($sql);

    $statement->bindValue(":item_id", $parent_id, PDO::PARAM_INT);
    if(strlen(trim($search_value)) > 0) {
      $statement->bindValue(":search_value", $search_value, PDO::PARAM_STR);
    }

    $statement->execute();

    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->connection->prepare("SELECT FOUND_ROWS()");
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["FOUND_ROWS()"];
    $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

    return $data;

  }

  /**
   * Get datatable data for imports.
   * @param $params
   * @return mixed
   */
  public function getDatatableImports($params) {

    $record_type = 'job';
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
        'field_name' => $record_type . '.date_created',
        'sort_order' => 'DESC',
      );
    }

    $query_params['related_tables'][] = array(
      'table_name' => 'project',
      'table_join_field' => 'project_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'job',
      'base_join_field' => 'project_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'job_import_record',
      'table_join_field' => 'job_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'job',
      'base_join_field' => 'job_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'fos_user',
      'table_join_field' => 'id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'job',
      'base_join_field' => 'created_by_user_account_id',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => $record_type . '_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_id',
    );
    $query_params['fields'][] = array(
      'field_name' => "SUM(case when job_import_record.record_table = 'subject' then 1 else 0 end)",
      'field_alias' => 'subjects_total',
    );
    $query_params['fields'][] = array(
      'field_name' => "SUM(case when job_import_record.record_table = 'item' then 1 else 0 end)",
      'field_alias' => 'items_total',
    );
    $query_params['fields'][] = array(
      'field_name' => "SUM(case when job_import_record.record_table = 'capture_dataset' then 1 else 0 end)",
      'field_alias' => 'capture_datasets_total',
    );
    $query_params['fields'][] = array(
      'field_name' => "SUM(case when job_import_record.record_table = 'model' then 1 else 0 end)",
      'field_alias' => 'models_total',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_name',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => 'date_created',
    );
    $query_params['fields'][] = array(
      'table_name' => 'job',
      'field_name' => 'uuid',
    );
    $query_params['fields'][] = array(
      'table_name' => 'job',
      'field_name' => 'job_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'job',
      'field_name' => 'project_id',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => 'job_status',
    );
    $query_params['fields'][] = array(
      'table_name' => 'fos_user',
      'field_name' => 'username',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => $record_type . '_id',
      'field_alias' => 'DT_RowId',
    );

    // Need to group by due to the SUM
    $query_params['group_by'] = array(
      'job.project_id',
      'job.job_id',
    );

    if (NULL !== $search_value) {
      $query_params['search_params'][0] = array(
        'field_names' => array(
          'project.project_name',
          $record_type . '.date_created',
          $record_type . '.created_by_user_account_id',
        ),
        'search_values' => array($search_value),
        'comparison' => 'LIKE',
      );
    }

    if(NULL !== $date_range_start) {
      $c = count($query_params['search_params']);
      $query_params['search_params'][$c] = array(
        'field_names' => array('project.date_created'),
        'search_values' => array($date_range_start),
        'comparison' => '<',
      );
    }
    if(NULL !== $date_range_end) {
      $c = count($query_params['search_params']);
      $query_params['search_params'][$c] = array(
        'field_names' => array('project.date_created'),
        'search_values' => array($date_range_end),
        'comparison' => '>',
      );
    }

    $data = $this->getRecordsDatatable($query_params);
    return $data;
  }

  /**
   * Get datatable data for an import's details.
   * @param $params
   * @return mixed
   */
  public function getDatatableImportDetails($params) {

    $record_type = 'job_import_record';
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;
    $job_id = array_key_exists('id', $params) ? $params['id'] : NULL;

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $date_range_start = array_key_exists('date_range_start', $params) ? $params['date_range_start'] : NULL;
    $date_range_end = array_key_exists('date_range_end', $params) ? $params['date_range_end'] : NULL;

    // Determine what was ingested via $job_data['job_type'] (e.g. subjects, items, capture datasets).
    $job_data = $this->getRecord(array(
        'base_table' => 'job',
        'id_field' => 'job_id',
        'id_value' => $job_id,
        'omit_active_field' => true,
      )
    );
    // TODO: ^^^ error handling if job is not found? ^^^

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
        'field_name' => $record_type . '.date_created',
        'sort_order' => 'DESC',
      );
    }

    $query_params['related_tables'][] = array(
      'table_name' => 'job',
      'table_join_field' => 'job_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'job_import_record',
      'base_join_field' => 'job_id',
    );
    $query_params['related_tables'][] = array(
      'table_name' => 'project',
      'table_join_field' => 'project_id',
      'join_type' => 'LEFT JOIN',
      'base_join_table' => 'job',
      'base_join_field' => 'project_id',
    );

    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'project',
      'field_name' => 'project_name',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'subject',
      'field_name' => 'subject_name',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'item_id',
    );
    $query_params['fields'][] = array(
      'table_name' => 'item',
      'field_name' => 'item_description',
    );

    // If subjects were ingested (with a project as the parent record)...
    if ($job_data['job_type'] === 'subjects metadata import') {

      $record_table = 'subject';

      $query_params['related_tables'][] = array(
        'table_name' => 'subject',
        'table_join_field' => 'subject_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'job_import_record',
        'base_join_field' => 'record_id',
      );
      $query_params['related_tables'][] = array(
        'table_name' => 'item',
        'table_join_field' => 'subject_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'subject',
        'base_join_field' => 'subject_id',
      );

      if (NULL !== $search_value) {
        $query_params['search_params'][3] = array(
          'field_names' => array(
            'subject_name',
            'item_description',
          ),
          'search_values' => array($search_value),
          'comparison' => 'LIKE',
        );
      }

    }
    

    // If items were ingested (with a subject as the parent record)...
    if ($job_data['job_type'] === 'items metadata import') {

      $record_table = 'item';

      $query_params['related_tables'][] = array(
        'table_name' => 'item',
        'table_join_field' => 'item_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'job_import_record',
        'base_join_field' => 'record_id',
      );
      $query_params['related_tables'][] = array(
        'table_name' => 'subject',
        'table_join_field' => 'subject_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'item',
        'base_join_field' => 'subject_id',
      );

      if (NULL !== $search_value) {
        $query_params['search_params'][3] = array(
          'field_names' => array(
            'subject_name',
            'item_description',
          ),
          'search_values' => array($search_value),
          'comparison' => 'LIKE',
        );
      }

    }


    // If capture datasets were ingested (with an item as the parent record)...
    if ($job_data['job_type'] === 'capture datasets metadata import') {

      $record_table = 'capture_dataset';

      $query_params['related_tables'][] = array(
        'table_name' => 'capture_dataset',
        'table_join_field' => 'capture_dataset_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'job_import_record',
        'base_join_field' => 'record_id',
      );
      $query_params['related_tables'][] = array(
        'table_name' => 'item',
        'table_join_field' => 'item_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'capture_dataset',
        'base_join_field' => 'item_id',
      );
      $query_params['related_tables'][] = array(
        'table_name' => 'subject',
        'table_join_field' => 'subject_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'item',
        'base_join_field' => 'subject_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'capture_dataset',
        'field_name' => 'capture_dataset_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'capture_dataset',
        'field_name' => 'capture_dataset_name',
      );

      if (NULL !== $search_value) {
        $query_params['search_params'][3] = array(
          'field_names' => array(
            'item_description',
            'capture_dataset_name',
          ),
          'search_values' => array($search_value),
          'comparison' => 'LIKE',
        );
      }

    }


    // If models were ingested (with a capture dataset as the parent record)...
    if ($job_data['job_type'] === 'models metadata import') {

      $record_table = 'model';

      $query_params['related_tables'][] = array(
        'table_name' => 'model',
        'table_join_field' => 'model_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'job_import_record',
        'base_join_field' => 'record_id',
      );
      $query_params['related_tables'][] = array(
        'table_name' => 'capture_dataset',
        'table_join_field' => 'capture_dataset_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'model',
        'base_join_field' => 'capture_dataset_id',
      );
      $query_params['related_tables'][] = array(
        'table_name' => 'item',
        'table_join_field' => 'item_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'model',
        'base_join_field' => 'item_id',
      );
      $query_params['related_tables'][] = array(
        'table_name' => 'subject',
        'table_join_field' => 'subject_id',
        'join_type' => 'LEFT JOIN',
        'base_join_table' => 'item',
        'base_join_field' => 'subject_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'capture_dataset',
        'field_name' => 'capture_dataset_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'capture_dataset',
        'field_name' => 'capture_dataset_name',
      );
      $query_params['fields'][] = array(
        'table_name' => 'model',
        'field_name' => 'model_id',
      );
      $query_params['fields'][] = array(
        'table_name' => 'model',
        'field_name' => 'model_file_type',
      );
      $query_params['fields'][] = array(
        'table_name' => 'model',
        'field_name' => 'date_of_creation',
      );

      if (NULL !== $search_value) {
        $query_params['search_params'][3] = array(
          'field_names' => array(
            'item_description',
            'capture_dataset_name',
          ),
          'search_values' => array($search_value),
          'comparison' => 'LIKE',
        );
      }

    }


    $query_params['search_params'][0] = array('field_names' => array('job_import_record.record_table'), 'search_values' => array($record_table), 'comparison' => '=');
    $query_params['search_type'] = 'AND';

    $query_params['search_params'][1] = array('field_names' => array('job_import_record.job_id'), 'search_values' => array((int)$job_id),'comparison' => '=');
    $query_params['search_type'] = 'AND';

    // $query_params['search_params'][2] = array('field_names' => array('item.item_id'), 'search_values' => array(''), 'comparison' => 'IS NOT NULL');
    // $query_params['search_type'] = 'AND';

    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => $record_type . '_id',
      'field_alias' => 'manage',
    );
    $query_params['fields'][] = array(
      'table_name' => $record_type,
      'field_name' => $record_type . '_id',
      'field_alias' => 'DT_RowId',
    );

    if(NULL !== $date_range_start) {
      $c = count($query_params['search_params']);
      $query_params['search_params'][$c] = array(
        'field_names' => array('project.date_created'),
        'search_values' => array($date_range_start),
        'comparison' => '<',
      );
    }
    if(NULL !== $date_range_end) {
      $c = count($query_params['search_params']);
      $query_params['search_params'][$c] = array(
        'field_names' => array('project.date_created'),
        'search_values' => array($date_range_end),
        'comparison' => '>',
      );
    }

    $data = $this->getRecordsDatatable($query_params);
    return $data;
  }

  public function getDatatableUsers($params) {
    //$params will be something like array('username_canonical' => 'bartlettr');
    $data = array();

    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    $role_slug = array_key_exists('role_slug', $params) ? $params['role_slug'] : NULL;
    $username_canonical = array_key_exists('username_canonical', $params) ? $params['username_canonical'] : NULL;

    // First select was project.project_name, unit_stakeholder.unit_stakeholder_label, unit_stakeholder.unit_stakeholder_full_name
    // Users with project-specific access.
    $sql = "SELECT username_canonical, username_canonical as manage, username_canonical as DT_RowId, username, email, enabled, 
            GROUP_CONCAT(rolename) as roles,
            project_name, unit_stakeholder_label, unit_stakeholder_full_name
            
            FROM 
            
            (SELECT fos_user.username_canonical, username, email, enabled, rolename,
            project.project_name, '' as unit_stakeholder_label, '' as unit_stakeholder_full_name
            
            FROM fos_user
            LEFT JOIN user_role on fos_user.username_canonical = user_role.username_canonical
            LEFT JOIN role on user_role.role_id = role.role_id
            LEFT JOIN project on user_role.project_id = project.project_id
            LEFT JOIN unit_stakeholder on project.stakeholder_guid = unit_stakeholder.isni_id 
            WHERE user_role.project_id IS NOT NULL 
            AND fos_user.enabled = 1
            AND user_role.active = 1
            AND role.active = 1
            AND project.active = 1
            AND unit_stakeholder.active = 1
            ";
    if(NULL !== $role_slug) {
      $sql .= " AND role.rolename_canonical LIKE :rolename_canonical ";
    }

    // Users with stakeholder-specific access.
    $sql .= " UNION 
            
            SELECT fos_user.username_canonical, username, email, enabled, rolename,
            'ALL' as project_name, unit_stakeholder.unit_stakeholder_label, unit_stakeholder.unit_stakeholder_full_name
            FROM fos_user
            LEFT JOIN user_role on fos_user.username_canonical = user_role.username_canonical
            LEFT JOIN role on user_role.role_id = role.role_id
            JOIN unit_stakeholder on user_role.stakeholder_id = unit_stakeholder.unit_stakeholder_id
            WHERE user_role.stakeholder_id IS NOT NULL 
            AND fos_user.enabled = 1
            AND user_role.active = 1
            AND unit_stakeholder.active = 1
            ";
    if(NULL !== $role_slug) {
      $sql .= " AND role.rolename_canonical LIKE :rolename_canonical ";
    }

    // Users with system-wide access.
    $sql .= " UNION

            SELECT fos_user.username_canonical, username, email, enabled, rolename,
            'ALL' as project_name, '' as unit_stakeholder_label, 'ALL' as unit_stakeholder_full_name
            FROM fos_user
            LEFT JOIN user_role on fos_user.username_canonical = user_role.username_canonical
            LEFT JOIN role on user_role.role_id = role.role_id
            WHERE user_role.stakeholder_id IS NULL 
            AND user_role.project_id IS NULL 
            AND fos_user.enabled = 1
            AND user_role.active = 1
            AND role.active = 1
            ";
    if(NULL !== $role_slug) {
      $sql .= " AND role.rolename_canonical LIKE :rolename_canonical ";
    }

    // Users with no current role- it only makes sense to get these when no rolename was specified.
    if(NULL == $role_slug) {
      $sql .= " UNION
            SELECT fos_user.username_canonical, username, email, enabled, '' as rolename,
            '' as project_name, '' as unit_stakeholder_label, '' as unit_stakeholder_full_name
            FROM fos_user
            LEFT JOIN user_role on fos_user.username_canonical = user_role.username_canonical
            WHERE user_role.user_role_id IS NULL 
            AND fos_user.enabled = 1
            ";
    }

    $sql .= ") as tmp ";

    $where = "";
    $where_parts = array();
    if(NULL !== $username_canonical) {
      $where_parts[] = " username_canonical=:username_canonical ";
    }

    if ($search_value) {
      $where_parts[] = "(tmp.username_canonical LIKE ':search_value' OR tmp.email LIKE ':search_value' OR 
      tmp.project_name LIKE ':search_value' OR tmp.rolename LIKE ':search_value' OR
      tmp.unit_stakeholder_label LIKE ':search_value' OR tmp.unit_stakeholder_full_name LIKE ':search_value')";
    }

    if(count($where_parts) > 0) {
      $where = " WHERE " . implode(' AND ', $where_parts);
    }

    $sql .= $where . " GROUP BY username_canonical, unit_stakeholder_label, unit_stakeholder_full_name, project_name ";

    if($sort_field) {
      $sql .= " ORDER BY " . $sort_field . " " . $sort_order;
    }
    else {
      $sql .= " ORDER BY username, unit_stakeholder_label, unit_stakeholder_full_name, project_name";
    }

    $count_query = "SELECT COUNT(manage) as c from (" . $sql . ") as x ";

    if(NULL !== $stop_record) {
      $sql .= " LIMIT {$start_record}, {$stop_record} ";
    }
    else {
      $sql .= " LIMIT {$start_record} ";
    }

    $statement = $this->connection->prepare($sql);
    if(NULL !== $username_canonical) {
      $statement->bindValue(":username_canonical", $username_canonical, PDO::PARAM_STR);
    }
    if(NULL !== $role_slug) {
      $statement->bindValue(":rolename_canonical", $role_slug, PDO::PARAM_STR);
    }
    if(strlen(trim($search_value)) > 0) {
      $statement->bindValue(":search_value", '%' . $search_value . '%', PDO::PARAM_STR);
    }
    $ret = $statement->execute();
    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement2 = $this->connection->prepare($count_query);
    if(NULL !== $username_canonical) {
      $statement2->bindValue(":username_canonical", $username_canonical, PDO::PARAM_STR);
    }
    if(NULL !== $role_slug) {
      $statement2->bindValue(":rolename_canonical", $role_slug, PDO::PARAM_STR);
    }
    $statement2->bindValue(":search_value", "%", PDO::PARAM_STR);
    if(strlen(trim($search_value)) > 0) {
      //$statement2->bindValue(":search_value", '%' . $search_value . '%', PDO::PARAM_STR);
    }
    $statement2->execute();
    $count = $statement2->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["c"];
    $data["iTotalDisplayRecords"] = $count["c"];

    return $data;

  }

  public function getDatatableUserRoles($params) {
    //$params will be something like array('username_canonical' => 'rb');
    $data = array();
    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    $username_canonical = array_key_exists('username_canonical', $params) ? $params['username_canonical'] : NULL;
    // First select was project.project_name, unit_stakeholder.unit_stakeholder_label, unit_stakeholder.unit_stakeholder_full_name
    $sql = "SELECT user_role_id, user_role_id as manage, user_role_id as DT_RowId, rolename,
            project_name, unit_stakeholder_label, unit_stakeholder_full_name, username_canonical
            FROM 
            
            (SELECT user_role_id, user_role.username_canonical, rolename,
            project.project_name, '' as unit_stakeholder_label, '' as unit_stakeholder_full_name
            
            FROM user_role
            LEFT JOIN role on user_role.role_id = role.role_id
            LEFT JOIN project on user_role.project_id = project.project_id
            LEFT JOIN unit_stakeholder on project.stakeholder_guid = unit_stakeholder.isni_id 
            WHERE user_role.project_id IS NOT NULL 
            AND user_role.active = 1
            AND role.active = 1
            AND project.active = 1
            AND unit_stakeholder.active = 1

            UNION 
            
            SELECT user_role_id, user_role.username_canonical, rolename,
            'ALL' as project_name, unit_stakeholder.unit_stakeholder_label, unit_stakeholder.unit_stakeholder_full_name
            FROM user_role
            LEFT JOIN role on user_role.role_id = role.role_id
            JOIN unit_stakeholder on user_role.stakeholder_id = unit_stakeholder.unit_stakeholder_id
            WHERE user_role.stakeholder_id IS NOT NULL 
            AND user_role.active = 1
            AND role.active = 1
            AND unit_stakeholder.active = 1
            
            UNION

            SELECT user_role_id, user_role.username_canonical, rolename,
            'ALL' as project_name, 'ALL' as unit_stakeholder_label, '' as unit_stakeholder_full_name
            FROM user_role
            LEFT JOIN role on user_role.role_id = role.role_id
            WHERE user_role.stakeholder_id IS NULL AND user_role.project_id IS NULL 
            AND user_role.active = 1
            AND role.active = 1
            )
            as tmp ";

    $where = "";
    $where_parts = array();
    if(NULL !== $username_canonical) {
      $where_parts[] = " username_canonical=:username_canonical ";
    }

    if ($search_value) {
      $where_parts[] = "(username_canonical LIKE ':search_value' OR email LIKE ':search_value' OR 
      rolename LIKE ':search_value' OR project_name LIKE ':search_value' OR 
      unit_stakeholder_label LIKE ':search_value' OR unit_stakeholder_full_name LIKE ':search_value')";
    }

    if(count($where_parts) > 0) {
      $where = " WHERE " . implode(' AND ', $where_parts);
    }

    $sql .= $where . " GROUP BY user_role_id, username_canonical, unit_stakeholder_label, unit_stakeholder_full_name, project_name, rolename ";

    if($sort_field) {
      $sql .= " ORDER BY " . $sort_field . " " . $sort_order;
    }
    else {
      $sql .= " ORDER BY username_canonical, unit_stakeholder_label, unit_stakeholder_full_name, project_name, rolename, user_role_id";
    }

    $count_query = "SELECT COUNT(manage) as c from (" . $sql . ") as x ";

    if(NULL !== $stop_record) {
      $sql .= " LIMIT {$start_record}, {$stop_record} ";
    }
    else {
      $sql .= " LIMIT {$start_record} ";
    }

    $statement = $this->connection->prepare($sql);
    if(NULL !== $username_canonical) {
      $statement->bindValue(":username_canonical", $username_canonical, PDO::PARAM_STR);
    }
    if(strlen(trim($search_value)) > 0) {
      $statement->bindValue(":search_value", '%' . $search_value . '%', PDO::PARAM_STR);
    }

    $statement->execute();
    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->connection->prepare($count_query);
    if(NULL !== $username_canonical) {
      $statement->bindValue(":username_canonical", $username_canonical, PDO::PARAM_STR);
    }
    if(strlen(trim($search_value)) > 0) {
      $statement->bindValue(":search_value", '%' . $search_value . '%', PDO::PARAM_STR);
    }
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["c"];
    $data["iTotalDisplayRecords"] = $count["c"];

    return $data;
  }

  public function getDatatableRoles($params) {
    //$params will be something like array('rolename_canonical' => 'bartlettr');
    $data = array();
    $search_value = array_key_exists('search_value', $params) ? $params['search_value'] : NULL;
    $sort_field = array_key_exists('sort_field', $params) ? $params['sort_field'] : NULL;
    $sort_order = array_key_exists('sort_order', $params) ? $params['sort_order'] : NULL;
    $start_record = array_key_exists('start_record', $params) ? $params['start_record'] : NULL;
    $stop_record = array_key_exists('stop_record', $params) ? $params['stop_record'] : NULL;

    $rolename_canonical = array_key_exists('rolename_canonical', $params) ? $params['rolename_canonical'] : NULL;
    $sql = "SELECT rolename_canonical, role.role_id as manage, rolename_canonical as DT_RowId, 
              rolename, role_description, 
              GROUP_CONCAT(permission_name) as permissions,
              ( SELECT COUNT(id) from fos_user 
                LEFT JOIN user_role on fos_user.username_canonical = user_role.username_canonical 
                WHERE fos_user.enabled = 1
                AND user_role.active = 1
                AND user_role.role_id = role.role_id
              ) as count_users
            FROM role
            LEFT JOIN role_permission on role.role_id = role_permission.role_id
            LEFT JOIN permission on role_permission.permission_id = permission.permission_id
            WHERE role.active = 1
                AND (role_permission.active = 1 OR role_permission.active IS NULL)
                AND (permission.active = 1 OR permission.active IS NULL)
                ";


    if(strlen(trim($rolename_canonical)) > 0) {
      $sql .= " AND rolename_canonical=:rolename_canonical ";
    }
    if(strlen(trim($search_value)) > 0) {
      $sql .= " AND (rolename_canonical LIKE ':search_value' OR rolename LIKE ':search_value' OR 
      role_description LIKE ':search_value' OR permissions LIKE ':search_value' )";
    }
    $sql .= " GROUP BY rolename_canonical ";

    $count_query = "SELECT COUNT(manage) as c from (" . $sql . ") as x ";

    if($sort_field) {
      $sql .= " ORDER BY " . $sort_field . " " . $sort_order;
    }
    else {
      $sql .= " ORDER BY rolename";
    }

    if(NULL !== $stop_record) {
      $sql .= " LIMIT {$start_record}, {$stop_record} ";
    }
    else {
      $sql .= " LIMIT {$start_record} ";
    }

    $statement = $this->connection->prepare($sql);
    if(strlen(trim($rolename_canonical)) > 0) {
      $statement->bindValue(":rolename_canonical", $rolename_canonical, PDO::PARAM_STR);
    }
    if(strlen(trim($search_value)) > 0) {
      $statement->bindValue(":search_value", '%' . $search_value . '%', PDO::PARAM_STR);
    }
    $statement->execute();
    $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

    $statement = $this->connection->prepare($count_query);
    if(strlen(trim($rolename_canonical)) > 0) {
      $statement->bindValue(":rolename_canonical", $rolename_canonical, PDO::PARAM_STR);
    }
    if(strlen(trim($search_value)) > 0) {
      $statement->bindValue(":search_value", '%' . $search_value . '%', PDO::PARAM_STR);
    }
    $statement->execute();
    $count = $statement->fetch(PDO::FETCH_ASSOC);
    $data["iTotalRecords"] = $count["c"];
    $data["iTotalDisplayRecords"] = $count["c"];

    return $data;
  }

  /**
   * User access control functions.
   */
  public function getUserAccessByProject($params = array()) {

    $data = false;

    $username = isset($params['username_canonical']) ? $params['username_canonical'] : NULL;
    $permission_name = isset($params['permission_name']) ? $params['permission_name'] : NULL;
    $project_id = isset($params['project_id']) ? $params['project_id'] : NULL;

    if(NULL == $permission_name || NULL == $username || NULL == $project_id) {
      return $data;
    }

    // See if user specifically has access to this project, has access via a stakeholder, or has access to this permission globally.
    $sql = "SELECT username_canonical, permission_name, GROUP_CONCAT(project_id) as project_ids
              FROM
              (
              SELECT user_role.username_canonical, permission.permission_name, project.project_id
                  FROM user_role
        
                  JOIN role_permission ON user_role.role_id = role_permission.role_id
                  JOIN permission ON role_permission.permission_id = permission.permission_id
                  LEFT JOIN project on user_role.project_id = project.project_id
                  LEFT JOIN unit_stakeholder ON project.stakeholder_guid = unit_stakeholder.isni_id
        
                  WHERE user_role.username_canonical= :username 
                  AND permission.permission_name= :permission_name
                  AND ( (user_role.project_id IS NULL AND user_role.stakeholder_id IS NULL) 
                  OR user_role.project_id= :project_id ) 
                  
                  AND user_role.active = 1
                  AND role_permission.active = 1
                  AND permission.active = 1
                  AND (project.active = 1 OR project.active IS NULL)
                  AND (unit_stakeholder.active = 1 OR unit_stakeholder.active IS NULL)

                        
              UNION
              SELECT user_role.username_canonical, permission.permission_name, project.project_id
                  FROM user_role
        
                  JOIN role_permission ON user_role.role_id = role_permission.role_id
                  JOIN permission ON role_permission.permission_id = permission.permission_id
                  LEFT JOIN unit_stakeholder ON user_role.stakeholder_id = unit_stakeholder.unit_stakeholder_id
                  LEFT JOIN project on unit_stakeholder.isni_id = project.stakeholder_guid
                  WHERE user_role.username_canonical= :username 
                  AND permission.permission_name= :permission_name
                  AND user_role.project_id IS NULL 
                  AND user_role.stakeholder_id IS NOT NULL 
                  AND project.project_id= :project_id  
                  
                  AND user_role.active = 1
                  AND role_permission.active = 1
                  AND permission.active = 1
                  AND project.active = 1
                  AND unit_stakeholder.active = 1

              )
              as tmp
              GROUP BY username_canonical, permission_name ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":username", $username, PDO::PARAM_STR);
    $statement->bindValue(":permission_name", $permission_name, PDO::PARAM_STR);
    if(NULL !== $project_id) {
      $statement->bindValue(":project_id", $project_id, PDO::PARAM_INT);
    }
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);

    return $data;
  }

  public function getUserAccessByStakeholder($params = array()) {

    $data = false;

    $username = isset($params['username_canonical']) ? $params['username_canonical'] : NULL;
    $permission_name = isset($params['permission_name']) ? $params['permission_name'] : NULL;
    $stakeholder_id = isset($params['stakeholder_id']) ? $params['stakeholder_id'] : NULL;

    if(NULL == $permission_name || NULL == $username) {
      return $data;
    }

    // See if user specifically has access to this stakeholder's projects, or has access to this permission globally.
    $sql = "SELECT user_role.username_canonical, permission.permission_name, GROUP_CONCAT(project.project_id) as project_ids
          FROM user_role
          JOIN role_permission ON user_role.role_id = role_permission.role_id
          JOIN permission ON role_permission.permission_id = permission.permission_id
          LEFT JOIN unit_stakeholder ON user_role.stakeholder_id = unit_stakeholder.unit_stakeholder_id
          LEFT JOIN project ON unit_stakeholder.isni_id = project.stakeholder_guid
          WHERE user_role.username_canonical= :username 
          AND permission.permission_name= :permission_name
          AND ( (user_role.project_id IS NULL AND user_role.stakeholder_id IS NULL) 
          
          AND user_role.active = 1
          AND role_permission.active = 1
          AND permission.active = 1
          AND project.active = 1
          AND unit_stakeholder.active = 1";
    if(NULL !== $stakeholder_id) {
      $sql .= " OR user_role.stakeholder_id= :stakeholder_id ";
    }
    $sql .= ")";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":username", $username, PDO::PARAM_STR);
    $statement->bindValue(":permission_name", $permission_name, PDO::PARAM_STR);
    if(NULL !== $stakeholder_id) {
      $statement->bindValue(":stakeholder_id", $stakeholder_id, PDO::PARAM_INT);
    }
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);

    return $data;

  }

  public function getUserAccessAny($params = array()) {

    $data = false;

    $username = isset($params['username_canonical']) ? $params['username_canonical'] : NULL;
    $permission_name = isset($params['permission_name']) ? $params['permission_name'] : NULL;

    if(NULL == $permission_name || NULL == $username) {
      return $data;
    }

    // If the user has global access, return all project_ids.
    $sql = "SELECT DISTINCT user_role.username_canonical, permission.permission_name
            FROM user_role 
            JOIN role_permission ON user_role.role_id = role_permission.role_id 
            JOIN permission ON role_permission.permission_id = permission.permission_id 
            WHERE user_role.username_canonical= :username 
            AND permission.permission_name= :permission_name 
            AND stakeholder_id IS NULL AND project_id IS NULL

            AND user_role.active = 1
            AND role_permission.active = 1
            AND permission.active = 1
           ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":username", $username, PDO::PARAM_STR);
    $statement->bindValue(":permission_name", $permission_name, PDO::PARAM_STR);
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);
    if(is_array($data) && count($data) > 0) {
      $data['project_ids'] = '';
      return $data;
    }

    // Otherwise see if user specifically has access to a specific stakeholder's projects, or specific projects
    $sql = "SELECT username_canonical, permission_name, GROUP_CONCAT(DISTINCT project_id) as project_ids
          FROM 
          (
          SELECT DISTINCT user_role.username_canonical, permission.permission_name, project.project_id
          FROM user_role
          JOIN role_permission ON user_role.role_id = role_permission.role_id
          JOIN permission ON role_permission.permission_id = permission.permission_id
          JOIN unit_stakeholder ON user_role.stakeholder_id = unit_stakeholder.unit_stakeholder_id
          LEFT JOIN project ON unit_stakeholder.isni_id = project.stakeholder_guid
          WHERE user_role.username_canonical= :username 
          AND permission.permission_name= :permission_name 
          AND user_role.active = 1
          AND role_permission.active = 1
          AND permission.active = 1
          AND unit_stakeholder.active = 1

          UNION
          SELECT DISTINCT user_role.username_canonical, permission.permission_name, project.project_id
          FROM user_role
          JOIN role_permission ON user_role.role_id = role_permission.role_id
          JOIN permission ON role_permission.permission_id = permission.permission_id
          JOIN project ON user_role.project_id = project.project_id
          LEFT JOIN unit_stakeholder ON project.stakeholder_guid = unit_stakeholder.isni_id 
          WHERE user_role.username_canonical= :username 
          AND permission.permission_name= :permission_name
          AND user_role.active = 1
          AND role_permission.active = 1
          AND permission.active = 1
          AND project.active = 1
          )
          as tmp
          GROUP BY username_canonical, permission_name 
           ";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":username", $username, PDO::PARAM_STR);
    $statement->bindValue(":permission_name", $permission_name, PDO::PARAM_STR);
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);

    return $data;
  }

  public function getAllProjectIds($params = array()) {

    $data = array('project_ids' => '');
    $sql = "SELECT GROUP_CONCAT(project_id) as project_ids from project WHERE active = 1";

    $statement = $this->connection->prepare($sql);
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);

    return $data;

  }


  public function markProjectInactive($params) {
    $user_id = $params['user_id'];
    $project_id = $params['record_id'];

    //@todo trap for missing user_id or record_id.
    $sql = "UPDATE project
                LEFT JOIN item ON project.project_id = item.project_id
                LEFT JOIN capture_dataset ON capture_dataset.item_id = item.item_id
                LEFT JOIN capture_data_element ON capture_data_element.capture_dataset_id = capture_dataset.capture_dataset_id
                SET project.active = 0,
                    project.last_modified_user_account_id = :last_modified_user_account_id,
                    item.active = 0,
                    item.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE project.project_id = :id
            ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $project_id, PDO::PARAM_INT);
    $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);
    $statement->execute();

    // Can't return records- causes PDO error.
    //$return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success'); //, 'data' => $return);

  }

  /**
   * Get Parent Records
   *
   * @param array $params An array of parameters, namely 'base_record_id' and 'record_type'.
   * @return array An array of parent records.
   */
  public function getParentRecords($params = array()) {

    $data = array();

    if(isset($params['base_record_id']) && isset($params['record_type'])) {

      switch($params['record_type']) {

        case 'item':
          $params['id_field_name'] = 'item.item_id';
          $params['select'] = 'project.project_id, subject.subject_id, item.item_id';
          $params['left_joins'] = 'LEFT JOIN subject ON subject.subject_id = item.subject_id
              LEFT JOIN project ON project.project_id = item.project_id';
          break;

        case 'capture_dataset':
          $params['id_field_name'] = 'capture_dataset.capture_dataset_id';
          $params['select'] = 'project.project_id, subject.subject_id, item.item_id, capture_dataset.capture_dataset_id';
          $params['left_joins'] = 'LEFT JOIN item ON item.item_id = capture_dataset.item_id
              LEFT JOIN subject ON subject.subject_id = item.subject_id
              LEFT JOIN project ON project.project_id = item.project_id';
          break;

        case 'capture_dataset_element':
          $params['id_field_name'] = 'capture_data_element.capture_data_element_id';
          $params['select'] = 'project.project_id, subject.subject_id, item.item_id, capture_dataset.capture_dataset_id, capture_data_element.capture_data_element_id';
          $params['left_joins'] = 'LEFT JOIN capture_dataset ON capture_dataset.capture_dataset_id = capture_data_element.capture_dataset_id
              LEFT JOIN item ON item.item_id = capture_dataset.item_id
              LEFT JOIN subject ON subject.subject_id = item.subject_id
              LEFT JOIN project ON project.project_id = item.project_id';
          break;

        case 'model_with_item_id':
          $params['record_type'] = 'model';
          $params['id_field_name'] = 'model.model_id';
          $params['select'] = 'project.project_id, project.project_name, subject.subject_id, item.item_id, item.item_description, model.model_id';
          $params['left_joins'] = 'LEFT JOIN capture_data_element ON capture_data_element.capture_data_element_id = model.capture_dataset_id
              -- LEFT JOIN capture_dataset ON capture_dataset.capture_dataset_id = capture_data_element.capture_dataset_id
              LEFT JOIN item ON item.item_id = model.item_id
              LEFT JOIN subject ON subject.subject_id = item.subject_id
              LEFT JOIN project ON project.project_id = item.project_id';
          break;
        case 'model_with_capture_dataset_id':
          $params['record_type'] = 'model';
          $params['id_field_name'] = 'model.model_id';
          $params['select'] = 'project.project_id, project.project_name, subject.subject_id, item.item_id, item.item_description, model.model_id';
          $params['left_joins'] = '
              LEFT JOIN capture_dataset ON capture_dataset.capture_dataset_id = model.capture_dataset_id
              LEFT JOIN item ON item.item_id = capture_dataset.item_id
              LEFT JOIN subject ON subject.subject_id = item.subject_id
              LEFT JOIN project ON project.project_id = item.project_id';
          break;

        default: // subject
          //@todo- subject does not have a parent
          $params['id_field_name'] = 'subject.subject_id';
          $params['select'] = 'subject.subject_id';

      }

      $sql = "SELECT " . $params['select'] . "
              FROM " . $params['record_type']
               . ' ' . $params['left_joins'] .
              " WHERE " . $params['id_field_name'] . " = :base_record_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":base_record_id", $params['base_record_id'], PDO::PARAM_INT);
      $statement->execute();
      $data = $statement->fetch(PDO::FETCH_ASSOC);
    }

    return $data;
  }

  public function getValues($params) {

    $tablename = array_key_exists('tablename', $params) ? $params['tablename'] : NULL;
    $stakeholder_id = array_key_exists('stakeholder_id', $params) ? $params['stakeholder_id'] : NULL;
    if($tablename !== "stakeholder" && $tablename !== "project" && $tablename !== "role") {
      return array();
    }

    switch($tablename) {
      case 'role':
        $sql = "SELECT role_id as id, rolename as name FROM role ";
        break;
      case 'project':
        $sql = "SELECT project_id as id, project_name as name FROM project";
        break;
      case 'stakeholder':
        $sql = "SELECT unit_stakeholder_id as id, unit_stakeholder_full_name as name FROM unit_stakeholder";
        break;
    }
    $sql .= " WHERE active=:active ";

    if(NULL !== $stakeholder_id && $tablename == 'project') {
      $sql .= " AND stakeholder_guid=:stakeholder_guid ";
    }
    $sql .= " ORDER BY name ";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":active", 1, PDO::PARAM_INT);
    if(NULL !== $stakeholder_id && $tablename == 'project') {
      $statement->bindValue(":stakeholder_guid", $stakeholder_id, PDO::PARAM_STR);
    }
    $statement->execute();
    $data = $statement->fetchAll(PDO::FETCH_ASSOC);

    return $data;
  }

  public function getUserRole($params = array()) {

    $user_role_id = array_key_exists('user_role_id', $params) ? $params['user_role_id'] : NULL;
    if(NULL == $user_role_id) {
      return array();
    }

    $sql = "SELECT * FROM user_role WHERE user_role_id = :user_role_id AND active = 1";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":user_role_id", $user_role_id, PDO::PARAM_STR);
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);

    return $data;
  }

  public function getRole($params = array()) {

    $sql = "SELECT * FROM role WHERE rolename_canonical = :rolename_canonical AND active = 1";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":rolename_canonical", $params['rolename_canonical'], PDO::PARAM_STR);
    $statement->execute();
    $data = $statement->fetch(PDO::FETCH_ASSOC);

    $sql = "SELECT permission_id, permission_name FROM permission ";
    $statement = $this->connection->prepare($sql);
    $statement->execute();
    $all_permission_data = $statement->fetchAll(PDO::FETCH_ASSOC);

    $permission_data = array();
    if(is_array($data) && array_key_exists('role_id', $data)) {
      $sql = "SELECT role_permission_id, permission_id
            FROM role_permission 
            WHERE (role_id = :role_id)";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":role_id", $data['role_id'], PDO::PARAM_INT);
      $statement->execute();
      $permission_data = $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    foreach($all_permission_data as $k => $p) {
      // permission_id, permission_name
      $permission_id = $p['permission_id'];
      $p['selected'] = false;
      foreach($permission_data as $k2 => $p2) {
        if($p2['permission_id'] == $permission_id) {
          $p['selected'] = true;
          break;
        }
      }
      $all_permission_data[$k] = $p;
    }

    $data['role_permissions'] = is_array($all_permission_data) ? $all_permission_data : array();
    return $data;
  }

  public function markSubjectInactive($params) {

    $user_id = $params['user_id'];
    $subject_id = $params['record_id'];

    //@todo trap for missing user_id or record_id.
    $sql = "UPDATE subject
                LEFT JOIN item ON item.subject_id = subject.subject_id
                LEFT JOIN capture_dataset ON capture_dataset.item_id = item.item_id
                LEFT JOIN capture_data_element ON capture_data_element.capture_dataset_id = capture_dataset.capture_dataset_id
                SET subject.active = 0,
                    subject.last_modified_user_account_id = :last_modified_user_account_id,
                    item.active = 0,
                    item.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE subject.subject_id = :id
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
    $capture_dataset_id = $params['record_id'];

    //@todo trap for missing user_id or record_id.
    $sql = "UPDATE capture_dataset
                LEFT JOIN capture_data_element ON capture_data_element.capture_data_element_id = capture_dataset.capture_dataset_id
                SET capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE capture_dataset.capture_dataset_id = :id
            ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $capture_dataset_id, PDO::PARAM_INT);
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
                LEFT JOIN capture_dataset ON capture_dataset.item_id = item.item_id
                LEFT JOIN capture_data_element ON capture_data_element.capture_dataset_id = capture_dataset.capture_dataset_id
                SET item.active = 0,
                    item.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_dataset.active = 0,
                    capture_dataset.last_modified_user_account_id = :last_modified_user_account_id,
                    capture_data_element.active = 0,
                    capture_data_element.last_modified_user_account_id = :last_modified_user_account_id
                WHERE item.item_id = :id
            ";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $item_id, PDO::PARAM_INT);
    $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);
    $statement->execute();

    // Can't return records- causes PDO error.
    //$return = $statement->fetchAll(PDO::FETCH_ASSOC);

    return array('return' => 'success'); //, 'data' => $return);

  }

  /***
   * @param $params has record_type (capture_dataset, model, or uv_map)
   * and record_id (repository id of the record)
   * @return current status info, or false
   */
  public function getWorkflowProcessingStatus($params) {

    $record_type = array_key_exists('record_type', $params) ? $params['record_type'] : NULL;
    $record_id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;

    if($record_type !== 'capture_dataset' && $record_type !== 'model') {
      return NULL;
    }

    // See if record exists; return FALSE if not.
    $sql = "Select * FROM " . $record_type . " WHERE " . $record_type . "_id=:id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $record_id, PDO::PARAM_INT);
    $statement->execute();

    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);

    if(!is_array($ret) || empty($ret)) {
      return false;
    }

    $record = $ret[0];
    return array(
      'record_type' => $record_type,
      'record_id' => $record_id,
      'workflow_id' => $record['workflow_id'],
      'processing_step' => $record['workflow_processing_step'],
      'status' => $record['workflow_status'],
      'status_detail' => $record['workflow_status_detail'],
      'created_by_user_account_id' => $record['created_by_user_account_id'],
      'date_created' => $record['date_created'],
      'last_modified_user_account_id' => $record['last_modified_user_account_id'],
      'last_modified' => $record['last_modified'],
    );
  }

  /***
   * @param $params has record_type (capture_dataset, model, or uv_map)
   * and record_id (repository id of the record)
   * @return mixed true or false
   */
  public function setWorkflowProcessingStatus($params) {

    $user_id = array_key_exists('user_id', $params) ? $params['user_id'] : NULL;
    $record_type = array_key_exists('record_type', $params) ? $params['record_type'] : NULL;
    $record_id = array_key_exists('record_id', $params) ? $params['record_id'] : NULL;
    $project_id = array_key_exists('project_id', $params) ? $params['project_id'] : NULL;
    $workflow_id = array_key_exists('workflow_id', $params) ? $params['workflow_id'] : NULL;
    $processing_step = array_key_exists('processing_step', $params) ? $params['processing_step'] : NULL;
    $status = array_key_exists('status', $params) ? $params['status'] : NULL;
    $status_detail = array_key_exists('status_detail', $params) ? $params['status_detail'] : NULL;

    if($record_type !== 'capture_dataset' && $record_type !== 'model') {
      return NULL;
    }

    // See if record exists; return FALSE if not.
    $sql = "Select * FROM " . $record_type . " WHERE " . $record_type . "_id=:id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $record_id, PDO::PARAM_INT);
    $statement->execute();

    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);

    if(!is_array($ret) || empty($ret)) {
      return false;
    }

    // Update this record with new status info. Write to workflow_status_log also.
    $sql ="INSERT INTO workflow_status_log 
          (workflow_id, project_id, record_id, record_table, processing_step, status, status_detail, created_by_user_account_id, date_created) 
          VALUES (:workflow_id, :project_id, :record_id, :record_table, :processing_step, :status, :status_detail, :created_by_user_account_id, NOW())";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":workflow_id", $workflow_id, PDO::PARAM_INT);
    $statement->bindValue(":project_id", $project_id, PDO::PARAM_INT);
    $statement->bindValue(":record_id", $record_id, PDO::PARAM_INT);

    $statement->bindValue(":record_table", $user_id, PDO::PARAM_STR);
    $statement->bindValue(":processing_step", $processing_step, PDO::PARAM_STR);
    $statement->bindValue(":status", $status, PDO::PARAM_STR);
    $statement->bindValue(":status_detail", $status_detail, PDO::PARAM_STR);
    $statement->bindValue(":created_by_user_account_id", $user_id, PDO::PARAM_INT);

    $statement->execute();

    $sql ="UPDATE " . $record_type . " set workflow_id=:workflow_id, workflow_processing_step=:processing_step,
    workflow_status=:status, workflow_status_detail=:status_detail,
    last_modified=NOW(), last_modified_user_account_id=:user_id 
    WHERE " . $record_type . "_id=:id";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $record_id, PDO::PARAM_INT);
    $statement->bindValue(":workflow_id", $workflow_id, PDO::PARAM_INT);
    $statement->bindValue(":processing_step", $processing_step, PDO::PARAM_STR);
    $statement->bindValue(":status", $status, PDO::PARAM_STR);
    $statement->bindValue(":status_detail", $status_detail, PDO::PARAM_STR);
    $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);

    $statement->execute();

    return true;
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
    $sql = "SELECT isni_id FROM isni_data WHERE isni_id=:id";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":id", $id, PDO::PARAM_INT);
    $statement->execute();

    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);
    if(count($ret) > 0) {
      // update
      $sql ="UPDATE isni_data set isni_label=:label, last_modified=NOW(), last_modified_user_account_id=:user_id WHERE isni_id=:id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":id", $ret[0]['isni_id'], PDO::PARAM_STR);
      $statement->bindValue(":label", $label, PDO::PARAM_STR);
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
      $statement->bindValue(":id", $id, PDO::PARAM_STR);
      $statement->bindValue(":label", $label, PDO::PARAM_STR);
      $statement->bindValue(":user_id", $user_id, PDO::PARAM_INT);
      $statement->execute();
    }

    return $id;

  }

  public function saveRole($params) {

    $user_id = array_key_exists('user_id', $params) ? $params['user_id'] : NULL;
    $rolename_canonical = array_key_exists('rolename_canonical', $params) ? $params['rolename_canonical'] : NULL;
    $rolename = array_key_exists('rolename', $params) ? $params['rolename'] : NULL;
    $role_description = array_key_exists('role_description', $params) ? $params['role_description'] : NULL;
    $role_permissions = array_key_exists('role_permissions', $params) ? $params['role_permissions'] : NULL;

    $new_rolename_canonical = $this->make_slug($rolename);

    if(!isset($rolename) || strlen(trim($rolename)) < 1 ) {
      return; //@todo with error
    }

    // See if record exists; update if so, insert otherwise.
    $sql = "SELECT role_id FROM role WHERE rolename_canonical=:rolename_canonical";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":rolename_canonical", $rolename_canonical, PDO::PARAM_INT);
    $statement->execute();
    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);

    if(count($ret) > 0) {
      // update
      $sql ="UPDATE role SET rolename=:rolename, rolename_canonical=:new_rolename_canonical, role_description=:role_description, 
            last_modified=NOW(), last_modified_user_account_id=:user_id WHERE rolename_canonical=:rolename_canonical";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":rolename", $rolename, PDO::PARAM_STR);
      $statement->bindValue(":role_description", $role_description, PDO::PARAM_STR);
      $statement->bindValue(":new_rolename_canonical", $new_rolename_canonical, PDO::PARAM_STR);
      $statement->bindValue(":rolename_canonical", $rolename_canonical, PDO::PARAM_STR);
      $statement->bindValue(":user_id", $user_id, PDO::PARAM_INT);
      $statement->execute();
    }
    else {
      // insert
      // Update this record with new status info. Write to workflow_status_log also.
      $sql ="INSERT INTO role 
          (rolename, rolename_canonical, role_description, created_by_user_account_id, date_created, last_modified_user_account_id, last_modified) 
          VALUES (:rolename, :rolename_canonical, :role_description, :created_by_user_account_id, NOW(), :last_modified_user_account_id, NOW())";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":rolename_canonical", $new_rolename_canonical, PDO::PARAM_STR);
      $statement->bindValue(":rolename", $rolename, PDO::PARAM_STR);
      $statement->bindValue(":role_description", $role_description, PDO::PARAM_STR);
      $statement->bindValue(":created_by_user_account_id", $user_id, PDO::PARAM_INT);
      $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);
      $statement->execute();
    }

    // Get the role id.
    $sql = "SELECT role_id FROM role WHERE rolename_canonical=:rolename_canonical";
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":rolename_canonical", $new_rolename_canonical, PDO::PARAM_STR);
    $statement->execute();
    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);
    if(count($ret) > 0) {
      $role_id = $ret[0]['role_id'];

      // Set role permissions.
      $sql ="UPDATE role_permission SET active=0 where role_id=:role_id";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":role_id", $role_id, PDO::PARAM_INT);
      $statement->execute();

      foreach($role_permissions as $permission_id) {
        $sql ="INSERT INTO role_permission (role_id, permission_id, created_by_user_account_id, date_created, last_modified_user_account_id, last_modified) 
          VALUES (:role_id, :permission_id, :created_by_user_account_id, NOW(), :last_modified_user_account_id, NOW())";
        $statement = $this->connection->prepare($sql);
        $statement->bindValue(":role_id", $role_id, PDO::PARAM_INT);
        $statement->bindValue(":permission_id", $permission_id, PDO::PARAM_INT);
        $statement->bindValue(":created_by_user_account_id", $user_id, PDO::PARAM_INT);
        $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);
        $statement->execute();
      }
    }

    return $new_rolename_canonical;

  }

  public function deleteRole($params) {

    $role_id = array_key_exists('role_id', $params) ? $params['role_id'] : NULL;
    $username_canonical = array_key_exists('username_canonical', $params) ? $params['username_canonical'] : NULL;
    $user_id = $params['user_id'];

    if(!isset($user_id) || !isset($role_id)) {
      return; //@todo with error
    }

    // Set inactive any matching records, if exist.
    if(isset($role_id)) {
      $sql = "UPDATE role SET active=0, last_modified_user_account_id=:user_id WHERE role_id=:role_id ";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":role_id", $role_id, PDO::PARAM_INT);
      $statement->bindValue(":user_id", $user_id, PDO::PARAM_INT);
      $statement->execute();

      // Update related user_role records.
      $sql = "UPDATE user_role SET active=0, last_modified_user_account_id=:user_id WHERE role_id=:role_id ";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":role_id", $role_id, PDO::PARAM_INT);
      $statement->bindValue(":user_id", $user_id, PDO::PARAM_INT);
      $statement->execute();

    }

    return;

  }

  public function saveUserRole($params) {

    $user_id = array_key_exists('user_id', $params) ? $params['user_id'] : NULL;
    $user_role_id = array_key_exists('user_role_id', $params) ? $params['user_role_id'] : NULL;
    $username_canonical = array_key_exists('username_canonical', $params) ? $params['username_canonical'] : NULL;
    $role_id = array_key_exists('role_id', $params) ? $params['role_id'] : NULL;
    $project_id = array_key_exists('project_id', $params) ? $params['project_id'] : NULL;
    $stakeholder_id = array_key_exists('stakeholder_id', $params) ? $params['stakeholder_id'] : NULL;

    if(!isset($username_canonical) || !isset($role_id)) {
      return; //@todo with error
    }

    // Delete any matching records, if exist.
    if(isset($user_role_id)) {
      $sql = "DELETE FROM user_role WHERE user_role_id=:user_role_id ";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":user_role_id", $user_role_id, PDO::PARAM_INT);
      $statement->execute();
    }

    // Insert.
    $user_role_id = NULL;

    $sql ="INSERT INTO user_role 
        (username_canonical, role_id, created_by_user_account_id, date_created, last_modified_user_account_id, last_modified ";
    $sql_values = " VALUES (:username_canonical, :role_id, :created_by_user_account_id, NOW(), :last_modified_user_account_id, NOW() ";
    if(NULL !== $project_id) {
      $sql .= ", project_id";
      $sql_values .= ", :project_id";
    }
    if(NULL !== $stakeholder_id) {
      $sql .= ", stakeholder_id";
      $sql_values .= ", :stakeholder_id";
    }
    $sql .= ") " . $sql_values . ")";

    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":username_canonical", $username_canonical, PDO::PARAM_STR);
    $statement->bindValue(":role_id", $role_id, PDO::PARAM_INT);
    if(NULL !== $project_id) {
      $statement->bindValue(":project_id", $project_id, PDO::PARAM_INT);
    }
    if(NULL !== $stakeholder_id) {
      $statement->bindValue(":stakeholder_id", $stakeholder_id, PDO::PARAM_INT);
    }
    $statement->bindValue(":created_by_user_account_id", $user_id, PDO::PARAM_INT);
    $statement->bindValue(":last_modified_user_account_id", $user_id, PDO::PARAM_INT);

    $statement->execute();


    // Get the user role id.
    $sql = "SELECT user_role_id FROM user_role 
        WHERE username_canonical=:username_canonical AND role_id=:role_id AND created_by_user_account_id= :created_by_user_account_id ";
    if(NULL !== $project_id) {
      $sql .= "AND project_id=:project_id ";
    }
    else {
      $sql .= "AND project_id IS NULL ";
    }
    if(NULL !== $stakeholder_id) {
      $sql .= "AND stakeholder_id= :stakeholder_id ";
    }
    else {
      $sql .= "AND stakeholder_id IS NULL ";
    }
    $statement = $this->connection->prepare($sql);
    $statement->bindValue(":username_canonical", $username_canonical, PDO::PARAM_STR);
    $statement->bindValue(":role_id", $role_id, PDO::PARAM_INT);
    if(NULL !== $project_id) {
      $statement->bindValue(":project_id", $project_id, PDO::PARAM_INT);
    }
    if(NULL !== $stakeholder_id) {
      $statement->bindValue(":stakeholder_id", $stakeholder_id, PDO::PARAM_INT);
    }
    $statement->bindValue(":created_by_user_account_id", $user_id, PDO::PARAM_INT);
    $statement->execute();
    $ret = $statement->fetchAll(PDO::FETCH_ASSOC);
    if(count($ret) > 0) {
      $user_role_id = $ret[0]['user_role_id'];
    }

    return $user_role_id;

  }

  public function deleteUserRole($params) {

    $user_role_id = array_key_exists('user_role_id', $params) ? $params['user_role_id'] : NULL;
    $username_canonical = array_key_exists('username_canonical', $params) ? $params['username_canonical'] : NULL;
    $user_id = $params['user_id'];

    if(!isset($username_canonical) || !isset($user_role_id)) {
      return; //@todo with error
    }

    // Set inactive any matching records, if exist.
    if(isset($user_role_id)) {
      $sql = "UPDATE user_role SET active=0, last_modified_user_account_id=:user_id WHERE user_role_id=:user_role_id ";
      $statement = $this->connection->prepare($sql);
      $statement->bindValue(":user_role_id", $user_role_id, PDO::PARAM_INT);
      $statement->bindValue(":user_id", $user_id, PDO::PARAM_INT);
      $statement->execute();
    }

    return;

  }

  private function make_slug($str) {
    // Turns a string into a string of only lowercase characters and underscores.

    $newstr = strtolower(str_replace(' ', '_', trim($str)));
    $newstr = str_replace('!', '', $newstr);
    $newstr = str_replace('@', '', $newstr);
    $newstr = str_replace('#', '', $newstr);
    $newstr = str_replace('$', '', $newstr);
    $newstr = str_replace('%', '', $newstr);
    $newstr = str_replace('^', '', $newstr);
    $newstr = str_replace('&', '', $newstr);
    $newstr = str_replace('*', '', $newstr);
    $newstr = str_replace('(', '', $newstr);
    $newstr = str_replace(')', '', $newstr);
    $newstr = str_replace('{', '', $newstr);
    $newstr = str_replace('}', '', $newstr);
    $newstr = str_replace('[', '', $newstr);
    $newstr = str_replace(']', '', $newstr);
    $newstr = str_replace('-', '_', $newstr);
    $newstr = str_replace('+', '', $newstr);
    $newstr = str_replace('=', '', $newstr);
    $newstr = str_replace('|', '', $newstr);
    $newstr = str_replace("\\", '', $newstr);
    $newstr = str_replace("/", '', $newstr);
    $newstr = str_replace('"', '', $newstr);
    $newstr = str_replace(':', '', $newstr);
    $newstr = str_replace(';', '', $newstr);
    $newstr = str_replace('<', '', $newstr);
    $newstr = str_replace('>', '', $newstr);
    $newstr = str_replace('.', '', $newstr);
    $newstr = str_replace(',', '', $newstr);
    $newstr = str_replace('?', '', $newstr);
    $newstr = str_replace('~', '', $newstr);
    $newstr = str_replace("`", '', $newstr);

    return $newstr;
  }

  /**
   * Generic functions for getting, setting, deleting and marking inactive.
   * ----------------------------------------------------------------
   */

  /**
   * Save function.
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

    //@todo- in the short term we want to be able to write workflow actions with no user id
    // We may want to tighten this up to force a non-null and > 0 user_id.
    if(NULL == $base_table || NULL == $values || (0 !== $user_id && NULL == $user_id)) {
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


  /**
   * Get a record.
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
        0 => array('field_names' => array($id_field), 'search_values' => array($id_value), 'comparison' => '='),
        1 => array('field_names' => array('active'), 'search_values' => array(1), 'comparison' => '='),
      ),
      'search_type' => 'AND'
    );

    // If the 'omit_active_field' parameter is set, unset the $query_params['search_params'][1] variable 
    // and pass the 'omit_active_field' parameter onto the getRecords() method.
    if (isset($parameters['omit_active_field'])) {
      unset($query_params['search_params'][1]);
      $query_params['omit_active_field'] = true;
    }

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
          'field_name' => $base_table . '_id',
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
        if(array_key_exists('limit_stop', $limit) && is_numeric($limit['limit_stop'])) {
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
    if(!isset($query_parameters['omit_active_field'])) {
      // If not explicitly omitted, add a search against the 'active' field = 1
      $query_parameters['search_params'][] = array('field_names' => array($base_table . '.active'), 'search_values' => array(1), 'comparison' => '=');
    } else {
      $query_parameters['search_params'][] = array('field_names' => array(), 'search_values' => array(), 'comparison' => '=');
    }

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
              $search_params[] = is_array($search_values[0]) ? '%' . $search_values[array_keys($search_values[0])] . '%' : '%' . $search_values[0] . '%';
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
          $comparison_type = 'STR';
          if(array_key_exists('comparison_type', $p)) {
            $comparison_type = 'INT';
          }
          $comparison = 'LIKE';
          if(array_key_exists('comparison', $p)) {
            $comparison = $p['comparison'];
          }

          if(count($search_values) == 1) {
            $k = array_keys($search_values)[0];

            if($comparison == 'IN' && $comparison_type == 'INT') {
              $in_placeholders = array();
              $t = is_array($search_values[$k]) ? $search_values[$k] : explode(',',$search_values[$k]);
              foreach($t as $m) {
                $in_placeholders[] = '?';
                $search_params[] = $m;
              }
              $this_search_param[] = $fn . ' IN (' . implode(',', $in_placeholders) . ')';
            }
            elseif($comparison == 'IN' && $comparison_type == 'STR') {
              $in_placeholders = array();
              $t = is_array($search_values[$k]) ? $search_values[$k] : explode(',',$search_values[$k]);
              foreach($t as $m) {
                $in_placeholders[] = '?';
                $search_params[] = $m;
              }
              $this_search_param[] = $fn . ' IN (%' . implode('%,%', $in_placeholders) . '%)';
            }
            elseif($comparison !== 'IN' && $comparison !== 'LIKE' && $comparison !== 'IS NOT NULL') {
              $this_search_param[] = $fn . ' ' . $p['comparison'] . ' ?';
              $search_params[] = $search_values[$k];
            }
            else if($comparison !== 'LIKE' && $comparison === 'IS NOT NULL') {
              $this_search_param[] = $fn . ' IS NOT NULL';
            }
            else {
              $this_search_param[] = $fn . ' LIKE ?';
              $search_params[] = '%' . $search_values[$k] . '%';
            }
          }
          elseif(count($search_values) > 1) {
            if($comparison == 'IN' && $comparison_type == 'INT') {
              $in_placeholders = array();
              $t = is_array($search_values) ? $search_values : explode(',',$search_values);
              foreach($t as $m) {
                $in_placeholders[] = '?';
                $search_params[] = $m;
              }
              $this_search_param[] = $fn . ' IN (' . implode(',', $in_placeholders) . ')';
            }
            elseif($comparison == 'IN' && $comparison_type == 'STR') {
              $in_placeholders = array();
              $t = is_array($search_values) ? $search_values : explode(',',$search_values);
              foreach($t as $m) {
                $in_placeholders[] = '?';
                $search_params[] = $m;
              }
              $this_search_param[] = $fn . ' IN (%' . implode('%,%', $in_placeholders) . '%)';
            }
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

    // echo '<pre>';
    // var_dump($sql);
    // echo '</pre>';
    // die();

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
        if(array_key_exists('field_value', $rv)
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
            $statement->bindValue($fn1, $fv1, is_bool($fv1) ? PDO::PARAM_BOOL : PDO::PARAM_STR);
          }

          $statement->bindValue(":id", $record_values['id']['field_value'], PDO::PARAM_INT);
          $statement->execute();

          // TODO: beef it up with some exception handling.
          // Example:
          // try {
          //   //----
          // }
          // catch(PDOException $e) {
          //   echo $e->getMessage();
          // }
          //
          // If the number of rows affected by the last SQL statement is zero, return fail.
          // See: http://php.net/manual/en/pdostatement.rowcount.php
          // if($statement->rowCount() === 0) {
            // return array('return' => 'fail', 'messages' => 'UPDATE `' . $base_table . '` failed.');
          // } else {
            $last_inserted_id = $record_values['id']['field_value'];
          // }

        }
        else {
          $sql ="INSERT INTO " . $base_table;
          $sql .= " (" . implode(',', array_keys($fields_sql_array)) . ', date_created)';
          $sql .= " VALUES (" . implode(',', array_values($fields_sql_array)) . ', NOW())';

          $statement = $this->connection->prepare($sql);
          foreach($fields_params as $fn1 => $fv1) {
            $statement->bindValue($fn1, $fv1, is_bool($fv1) ? PDO::PARAM_BOOL : PDO::PARAM_STR);
          }
          $statement->execute();
          $last_inserted_id = $this->connection->lastInsertId();

          if(!$last_inserted_id) {
            return array('return' => 'fail', 'messages' => 'INSERT INTO `' . $base_table . '` failed.');
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

    $sql = "UPDATE " . $record_type . " SET active = 0, last_modified_user_account_id=:user_id WHERE " . $record_type . "_id=:id ";

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