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
  public function import_csv(array $params);

  /**
   * Prepare Data
   *
   * @param string $job_type The job type (One of: subjects, items, capture datasets, models)
   * @param string $job_upload_directory The upload directory
   * @return array Import result and/or any messages
   */
  public function prepare_data(string $job_type, string $job_upload_directory);

  /**
   * Ingest CSV Data
   *
   * @param obj $data  Data object
   * @param array $job_data  Job data
   * @param string $parent_record_type  Parent record type
   * @param int $i  Iterator
   * @return array  An array of job log IDs
   */
  public function ingest_csv_data(obj $data, array $job_data, $parent_record_type, int $i);

  /**
   * Insert Capture Data Elements and Files
   *
   * @param array $capture_data_elements An array of capture data elements.
   * @param int $capture_dataset_repository_id The capture dataset repository ID
   * @param string $user_id The user ID
   * @return null
   */
  public function insert_capture_data_elements_and_files(array $capture_data_elements, int $capture_dataset_repository_id, string $user_id);

  /**
   * Extract Data From External
   *
   * @param string $function_name Name of the function to call.
   * @param array $data Job data
   * @return array
   */
  public function extract_data_from_external(string $function_name, array $data);

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

  /**
   * Get File Info
   *
   * @param string $uuid The job UUID
   * @param string $file_name The file name
   */
  public function get_file_info(string $uuid, string $file_name);

  /**
   * Get File Name Map
   *
   * @param array $job_data Job data
   * @param string $directory The target directory
   * @return array
   */
  public function get_filename_map(array $job_data, string $directory);

}
