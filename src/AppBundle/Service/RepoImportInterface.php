<?php

namespace AppBundle\Service;

/**
 * Interface for imports.
 */

interface RepoImportInterface {

  /**
   * @param array $params Parameters: job_id, project_id, parent_record_id, parent_record_type
   * @return array
   */
  public function import_csv($params = array());

  /**
   * @param string $job_type The job type (One of: subjects, items, capture datasets, models)
   * @param string $job_upload_directory The upload directory
   * @return array Import result and/or any messages
   */
  public function prepare_data($job_type = null, $job_upload_directory = null);

  /**
   * @param string $data  Data object
   * @param int $job_id  Job ID
   * @param int $parent_record_id  Parent record ID
   * @return array  An array of job log IDs
   */
  public function ingest_csv_data($data = null, $ids = array(), $parent_record_type = null, $i = 1);

}
