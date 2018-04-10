<?php

namespace AppBundle\Service;

use Doctrine\DBAL\Driver\Connection;

use PDO;

use JsonSchema\{
    SchemaStorage,
    Validator,
    Constraints\Factory
};

class RepoValidateData implements RepoValidate {

  // private $connection;

  // public function __construct($connection) {
  //   $this->connection = $connection;
  // }

  /**
   * @param null $data The data to validate.
   * @param null $schema The schema to validate against (optional).
   * Validates incoming data against JSON Schema Draft 7. See:
   * http://json-schema.org/specification.html
   * JSON Schema for PHP Documentation: https://github.com/justinrainbow/json-schema
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateData($data = NULL, $schema = 'project') {

    $schema_definitions_dir = ($schema !== 'project') ? 'definitions/' : '';

    $return = array('is_valid' => false);

    // If no data is passed, set a message.
    if(empty($data)) $return['messages'][] = 'Nothing to validate. Please provide an object to validate.';

    // If data is passed, go ahead and process.
    if(!empty($data)) {

      $schema_dir = __DIR__ . '/../../../web/json/schemas/';
      $jsonSchemaObject = json_decode(file_get_contents($schema_dir . $schema_definitions_dir . $schema . '.json'));

      $schemaStorage = new SchemaStorage();
      $schemaStorage->addSchema('file://' . $schema_dir, $jsonSchemaObject);

      $jsonValidator = new Validator( new Factory($schemaStorage) );
      $jsonValidator->validate($data, $jsonSchemaObject);

      if ($jsonValidator->isValid()) {
        $return['is_valid'] = true;
      } else {
        $return['is_valid'] = false;
        foreach ($jsonValidator->getErrors() as $error) {
          $return['messages'][$error['property']] = sprintf("[%s] %s", $error['property'], $error['message']);
        }
      }

    }

    return $return;
  }

}