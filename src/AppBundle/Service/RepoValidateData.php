<?php

namespace AppBundle\Service;

use Doctrine\DBAL\Driver\Connection;

use PDO;

use JsonSchema\{
    SchemaStorage,
    Validator,
    Constraints\Factory,
    Constraints\Constraint
};

use AppBundle\Controller\RepoStorageHybridController;

class RepoValidateData implements RepoValidate {

  /**
   * @var string $schema_dir
   */
  public $schema_dir;
  private $repo_storage_controller;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(Connection $conn)
  {
    // TODO: move this to parameters.yml and bind in services.yml?
    $this->schema_dir = __DIR__ . '/../../../web/json/schemas/repository/';
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
  }

  /**
   * @param null $data The data to validate.
   * @param string $schema The schema to validate against (optional).
   * Check to see if a CSV's 'import_row_id' has gaps or is not sequential.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateRowIds(&$data = NULL, $schema = 'project') {

    $import_row_ids = $duplicate_keys = array();
    $return = array('is_valid' => false);

    // If no data is passed, set a message.
    if(empty($data)) $return['messages'][] = 'Nothing to validate. Please provide an object to validate.';

    // If data is passed, go ahead and process.
    if(!empty($data)) {
      // Loop through the data.
      foreach ($data as $data_key => $data_value) {
        // If an array of data contains 1 or fewer keys, then it means the row is empty.
        // Unset the empty row, so it doesn't get passed onto the JSON validator.
        if (count(array_keys((array)$data_value)) <= 1) {
          unset($data[$data_key]);
        } else {
          // Check for empty import_row_id values and duplicate import_row_id values.
          foreach ($data_value as $dv_key => $dv_value) {
            // If the value is empty, add a message.
            if(($dv_key === 'import_row_id') && empty($dv_value)) {
              $return['messages'][($data_key+1)] = array('row' => 'Row ' . ($data_key+1) . ' - Field: import_row_id', 'error' => 'Must be at least 1 characters long');
            }
            // Collect all of the 'import_row_id' values to check for duplicate values.
            if(($dv_key === 'import_row_id') && !empty($dv_value)) {
              $import_row_ids[] = $dv_value;
            }
          }
        }

      }
    }

    // Check 'import_row_id' values for duplicate values.
    // Unique values
    $unique = array_unique($import_row_ids);
    // Duplicates
    $duplicates = array_diff_assoc($import_row_ids, $unique);
    // Get duplicate keys
    $duplicate_keys = array_keys(array_intersect($import_row_ids, $duplicates));

    // Loop through 'import_row_id' keys containing duplicate values and add messages.
    if (!empty($duplicate_keys)) {
      foreach ($duplicate_keys as $dup_key => $dup_value) {
        $return['messages'][$dup_value] = array('row' => 'Row ' . ($dup_value+1) . ' - Field: import_row_id', 'error' => 'Contains a duplicate key');
      }
    }

    // If there are no messages, then return true for 'is_valid'.
    if(!isset($return['messages'])) {
      $return['is_valid'] = true;
    }

    // Sort messages by key (which is the row in the CSV) and reindex the array.
    if(isset($return['messages'])) {
      ksort($return['messages']);
      $return['messages'] = array_values($return['messages']);
    }

    // echo '<pre>';
    // var_dump($return);
    // echo '</pre>';
    // die();

    return $return;
  }

  /**
   * @param null $data The data to validate.
   * @param string $schema The schema to validate against (optional).
   * @param string $parent_record_type The parent record type - can be one of: project, subject, item, capture dataset.
   * @param array $blacklisted_fields An array of fields to ignore (optional).
   * Validates incoming data against JSON Schema Draft 7. See:
   * http://json-schema.org/specification.html
   * JSON Schema for PHP Documentation: https://github.com/justinrainbow/json-schema
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateData($data = NULL, $schema = 'project', $parent_record_type = NULL, $blacklisted_fields = array()) {

    $return = array('is_valid' => false);

    // If no data is passed, set a message.
    if(empty($data)) $return['messages'][] = 'Nothing to validate. Please provide an object to validate.';

    // If data is passed, go ahead and process.
    if(!empty($data)) {

      $integer_field_types = $boolean_field_types = array();

      // If need be, use the alternate JSON schema for uploads.
      switch ($schema) {
        case 'model':
          $upload_json = '_upload';
          break;
        default:
          $upload_json = '';
      }

      $jsonSchemaObject = json_decode(file_get_contents($this->schema_dir . $schema . $upload_json . '.json'));

      // Convert the CSV's integer-based fields to integers... 
      // Convert the CSV's boolean-based fields to booleans... 
      // because out of the box, all of the CSV's array values are strings.

      // First, reference the JSON schema to gather:
      // 1) all of the field names with the type of integer.
      // 2) all of the field names with the type of boolean.
      $schema_properties = $jsonSchemaObject->items->properties;
      foreach ($schema_properties as $key => $value) {
        // Integer field types
        if(isset($value->type) && ($value->type === 'integer')) {
          $integer_field_types[] = $key;
        }
        // Boolean field types
        if(isset($value->type) && ($value->type === 'boolean')) {
          $boolean_field_types[] = $key;
        }
      }

      // Convert field values from string to integer or boolean.
      foreach ($data as $data_key => $data_value) {
        foreach ($data_value as $dv_key => $dv_value) {
          // Cast to integer
          if(!empty($integer_field_types) && in_array($dv_key, $integer_field_types)) {
            $data[$data_key]->$dv_key = (int)$dv_value;
          }
          // Cast to boolean
          if(!empty($boolean_field_types) && in_array($dv_key, $boolean_field_types)) {
            $data[$data_key]->$dv_key = (bool)$dv_value;
          }
        }
      }

      $schemaStorage = new SchemaStorage();
      $schemaStorage->addSchema('file://' . $this->schema_dir  . $schema, $jsonSchemaObject);

      $jsonValidator = new Validator( new Factory($schemaStorage) );
      $jsonValidator->validate($data, $jsonSchemaObject, Constraint::CHECK_MODE_APPLY_DEFAULTS);

      if ($jsonValidator->isValid()) {
        $return['is_valid'] = true;
      } else {

        $return['is_valid'] = false;

        foreach ($jsonValidator->getErrors() as $error_key => $error) {
          // Ignore blacklisted field.
          // if(!strstr($error['property'], $blacklisted_fields)) {
            $row = preg_replace("/[^0-9]+/", '', $error['property']);
            $property_parts = explode('.', $error['property']);
            $return['messages'][] = array('row' => 'Row ' . ($row+1) . ' - ' . $property_parts[1], 'error' => $error['message']);
          // }
        }

        // If there are no messages, then return true for 'is_valid'.
        if(!isset($return['messages'])) {
          $return['is_valid'] = true;
        }

      }

    }

    return $return;
  }

  /**
   * Validate capture_dataset_field_id in CSV
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateCaptureDatasetFieldIdInCsv($data = array()) {

    $return = $field_ids = array();

    // If no data is passed, set a message.
    if(empty($data)) $return['messages'][] = 'Nothing to validate. Please provide an object to validate.';

    foreach ($data as $key => $value) {
      if (!empty($value->capture_dataset_field_id)) {
        if (in_array($value->capture_dataset_field_id, $field_ids)) {
          $return['messages'][] = array('row' => 'Row ' . ($key+1) . ' - Field: capture_dataset_field_id', 'error' => 'Illegal duplicate "capture_dataset_field_id" value found (' . $value->capture_dataset_field_id . ').');
        }
        $field_ids[] = $value->capture_dataset_field_id;
      }
    }

    return $return;
  }

  /**
   * Validate capture_dataset_field_id
   * @param array $data The data to validate.
   * @param array $parent_record_id The parent record ID.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateCaptureDatasetFieldId($data = array(), $parent_records = array()) {

    $return = array();

    // If no data is passed, set a message.
    if(empty($data)) $return['messages'][] = 'Nothing to validate. Please provide an object to validate.';

    // If data is passed, go ahead and perform the validation.
    if(!empty($data) && !empty($parent_records)) {


      foreach($data as $key => $value) {
        if(!empty($value->capture_dataset_field_id)) {
          // Check the database to see if there is a capture_dataset_field_id with the same value as what's in the CSV.
          $result = $this->repo_storage_controller->execute('getRecords', array(
              'base_table' => 'capture_dataset',
              'fields' => array(
                0 => array(
                  'table_name' => 'capture_dataset',
                  'field_name' => 'capture_dataset_name',
                ),
              ),
              'search_params' => array(
                array(
                  'field_names' => array(
                    'capture_dataset_field_id'
                  ),
                  'search_values' => array(
                    $value->capture_dataset_field_id
                  ),
                  'comparison' => '='
                ),
                array(
                  'field_names' => array(
                    'parent_project_repository_id'
                  ),
                  'search_values' => array(
                    $parent_records['project_repository_id']
                  ),
                  'comparison' => '='
                ),
                array(
                  'field_names' => array(
                    'parent_item_repository_id'
                  ),
                  'search_values' => array(
                    $parent_records['item_repository_id']
                  ),
                  'comparison' => '='
                )
              ),
              'search_type' => 'AND',
              'limit' => array('limit_start' => 1),
            )
          );
          // If a matching capture_dataset_field_id is found, add to the messages array.
          if(!empty($result)) {
            $return['messages'][] = array('row' => 'Row ' . ($key+1) . ' - Field: capture_dataset_field_id', 'error' => 'The "' . $result[0]['capture_dataset_name'] . '" Capture Dataset with the capture_dataset_field_id value (' . $value->capture_dataset_field_id . ') already exists.');
          }
        }
      }

    }

    return $return;
  }

  /**
   * @param string $status The status text to check for.
   * @param string $uploads_directory The uploads directory.
   * @return array The next directory to validate.
   */
  public function needsValidationChecker($status = null, $uploads_directory = null) {

    $directory = null;

    // Check the database to find the next job which hasn't had a BagIt validation performed against it.
    $data = $this->repo_storage_controller->execute('getRecords', array(
        'base_table' => 'job',
        'fields' => array(),
        'search_params' => array(
          0 => array(
            'field_names' => array(
              'job_status'
            ),
            'search_values' => array(
              $status
            ),
            'comparison' => '='
          )
        ),
        'search_type' => 'AND',
        'sort_fields' => array(
          0 => array('field_name' => 'date_created')
        ),
        'limit' => array('limit_start' => 1),
        'omit_active_field' => true,
      )
    );

    if(!empty($data)) {
      
      $directory = $uploads_directory . $data[0]['uuid'];
      
      // Update the 'job_status' in the 'job' table from 'bagit validation starting' to 'bagit validation in progress'.
      $this->repo_storage_controller->execute('setJobStatus', 
        array(
          'job_id' => $data[0]['uuid'], 
          'status' => 'bagit validation in progress',
          'date_completed' => date('Y-m-d h:i:s')
        )
      );
    }

    return $directory;
  }

  /**
   * @param array $params Parameters for inserting into the database.
   * @return null
   */
  public function logErrors($params = array()) {

    if(!empty($params)) {
      foreach ($params['errors'] as $ekey => $error) {
        $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job_log',
          'user_id' => $params['user_id'],
          'values' => array(
            'job_id' => $params['job_id'],
            'job_log_status' => 'error',
            'job_log_label' => $params['job_log_label'],
            'job_log_description' => $error,
          )
        ));
      }
    }

  }

  public function dumper($data = false, $die = true, $ip_address=false){
    if(!$ip_address || $ip_address == $_SERVER["REMOTE_ADDR"]){
      echo '<pre>';
      var_dump($data);
      echo '</pre>';
      if($die) die();
    }
  }

}