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
    $this->schema_dir = __DIR__ . '/../../../web/json/schemas/repository/';
  }

  /**
   * @param null $data The data to validate.
   * @param string $schema The schema to validate against (optional).
   * @param array $blacklisted_fields An array of fields to ignore (optional).
   * Validates incoming data against JSON Schema Draft 7. See:
   * http://json-schema.org/specification.html
   * JSON Schema for PHP Documentation: https://github.com/justinrainbow/json-schema
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateData($data = NULL, $schema = 'project', $blacklisted_fields = array()) {

    $schema_definitions_dir = ($schema !== 'project') ? 'definitions/' : '';

    $return = array('is_valid' => false);

    // If no data is passed, set a message.
    if(empty($data)) $return['messages'][] = 'Nothing to validate. Please provide an object to validate.';

    // If data is passed, go ahead and process.
    if(!empty($data)) {

      $jsonSchemaObject = json_decode(file_get_contents($this->schema_dir . $schema_definitions_dir . $schema . '.json'));

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
          // TODO: Loop through blacklisted fields (right now, only using the first one, $blacklisted_fields[0]).
          // if(!strstr($error['property'], $blacklisted_fields[0])) {
            $row = str_replace('[', 'Row ', $error['property']);
            $row = str_replace(']', '', $row);
            $row = str_replace('.', ' - Field: ', $row);
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