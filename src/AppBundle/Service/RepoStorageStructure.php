<?php

namespace AppBundle\Service;


interface RepoStorageStructure {

  /***
   * @param null $schema_part name of schema definition/atom.
   * Return JSON schema for the repository.
   * If $schema_part is specified, return the schema for that atom.
   * @return mixed schema in JSON schema format.
   */
  public function getSchema($schema_atom = NULL);

  /***
   * @param $schema schema in JSON schema format.
   * @param $diff_only will return a diff between the existing schema and the newly specified schema.
   * Given $schema, generate the necessary structures in the data storage area.
   * @return mixed array containing success/fail value, and any messages;
   * If $diff_only, returns success + messages indicating the differences.
   */
  public function setSchema($schema_json, $diff_only);

  public function install();



}