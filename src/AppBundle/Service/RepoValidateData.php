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

class RepoValidateData implements RepoValidate {

  /**
   * @var string $schema_dir
   */
  public $schema_dir;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct()
  {
    // TODO: move this to parameters.yml and bind in services.yml?
    $this->schema_dir = __DIR__ . '/../../../web/json/schemas/repository/';
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

    $schema_definitions_dir = ($schema !== 'project') ? 'definitions/' : '';

    $return = array('is_valid' => false);

    // If no data is passed, set a message.
    if(empty($data)) $return['messages'][] = 'Nothing to validate. Please provide an object to validate.';

    // If data is passed, go ahead and process.
    if(!empty($data)) {

      $integer_field_types = $boolean_field_types = array();

      $jsonSchemaObject = json_decode(file_get_contents($this->schema_dir . $schema_definitions_dir . $schema . '.json'));

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
      $schemaStorage->addSchema('file://' . $this->schema_dir . $schema_definitions_dir, $jsonSchemaObject);

      $jsonValidator = new Validator( new Factory($schemaStorage) );
      $jsonValidator->validate($data, $jsonSchemaObject, Constraint::CHECK_MODE_APPLY_DEFAULTS);

      if ($jsonValidator->isValid()) {
        $return['is_valid'] = true;
      } else {

        $return['is_valid'] = false;

        foreach ($jsonValidator->getErrors() as $error) {
          // Ignore blacklisted field.
          // if(!strstr($error['property'], $blacklisted_fields)) {
            $row = str_replace('[', 'Row ', $error['property']);
            $row = str_replace(']', '', $row);
            $row = str_replace('.', ' - Field: ', $row);
            // Fix "Row 0..." (increment by 1).
            $pattern = '/([0-9])/';
            $replacement = ((int)'$3'+1);
            $row = preg_replace($pattern, $replacement, $row);
            $return['messages'][] = array('row' => $row, 'error' => $error['message']);
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

}