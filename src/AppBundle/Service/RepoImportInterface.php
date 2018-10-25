<?php

namespace AppBundle\Service;

/**
 * Interface for imports.
 */

interface RepoImportInterface {

  /**
   * Import CSV
   *
   * @param array $params Parameters: job_id, project_id, parent_record_id, parent_record_type
   * @return array
   */
  public function import_csv($params = array());

  /**
   * Prepare Data
   *
   * @param string $job_type The job type (One of: subjects, items, capture datasets, models)
   * @param string $job_upload_directory The upload directory
   * @return array Import result and/or any messages
   */
  public function prepare_data($job_type = null, $job_upload_directory = null);

  /**
   * Ingest CSV Data
   *
   * @param string $data  Data object
   * @param int $job_id  Job ID
   * @param int $parent_record_id  Parent record ID
   * @return array  An array of job log IDs
   */
  public function ingest_csv_data($data = null, $ids = array(), $parent_record_type = null, $i = 1);

  /**
   * Extract Data From External
   *
   * @param string $function_name Name of the function to call.
   * @param array $data Job data
   * @return array
   */
  public function extract_data_from_external(array $function_name, array $data);

  /**
   * Get Model Data From Processing Service
   *
   * @param array $data Job data
   * @return array
   */
  public function get_model_data_from_processing_service(array $data);

  /**
   * Get Data From File Names
   *
   * @param array $data Job data
   * @return array
   */
  public function get_data_from_file_names(array $data);

  /**
   * Get Capture Dataset Data From Filenames
   *
   * @param array $image_file_names Image file names
   * @param array $data Job data
   * @return array
   */
  public function get_dataset_data_from_filenames(array $image_file_names, array $data);
  
  /**
   * Get Data From File Names
   *
   * @param array $model_file_names Model file names
   * @param array $data Job data
   * @return array
   */
  public function get_model_data_from_filenames(array $model_file_names, array $data);

}
