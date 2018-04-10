<?php

namespace AppBundle\Service;

/**
 * Interface for repository data validation classes.
 */

interface RepoValidate {

  /***
   * @param $data The data to validate.
   * @param $schema The schema to validate against (optional).
   * Validates incoming data against JSON Schema Draft 7
   * See: http://json-schema.org/specification.html
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateData($data, $schema);

}
