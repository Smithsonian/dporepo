<?php

namespace AppBundle\Service;

/**
 * Interface for repository storage classes.
 */

interface RepoStorage {

  public function __construct($connection, $project_dir);

  /***
   * @param $query_parameters parameters used to query records for return.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function getRecords(array $query_parameters);

  /***
   * @param array $query_parameters data and metadata to save.
   * Attempts to save data, and updates $query_parameters['records_values'] accordingly.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function setRecords(array $query_parameters);

  /***
   * @param $query_parameters parameters used to query records for deletion.
   * @param $$query_parameters['delete_children'] whether to delete child record.
   * Attempts to delete specified records.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function deleteRecords(array $query_parameters);

}
