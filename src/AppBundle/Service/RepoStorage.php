<?php

namespace AppBundle\Service;

/**
 * Interface for repository storage classes.
 */

interface RepoStorage {

  public function __construct($connection);

  /***
   * @param $query_parameters parameters used to query records for return.
   * @return mixed Return records - projects, subjects, etc. or empty set.
   */
  public function getRecords(array $query_parameters, array &$records_values);

  /***
   * @param null $records_values data and metadata to save.
   * Attempts to save data, and updates $records_values accordingly.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function setRecords(array &$records_values, array $query_parameters);

  /***
   * @param $query_parameters parameters used to query records for deletion.
   * @param $delete_children whether to delete child record.
   * Attempts to delete specified records.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function deleteRecords(array $query_parameters, $delete_children = FALSE);

}
