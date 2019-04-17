<?php

namespace AppBundle\Service;

/**
 * Interface for imports.
 */

interface RepoImportInterface {

  /**
   * Import CSV
   *
   * @param array $params Parameters: job_id, project_id, record_id, record_type
   * @return array
   */
  public function importCsv(array $params);

  /**
   * Prepare Data
   *
   * @param string $job_type The job type (One of: subjects, items, capture datasets, models)
   * @param string $job_upload_directory The upload directory
   * @return array Import result and/or any messages
   */
  public function prepareData(string $job_type, string $job_upload_directory);

  /**
   * Ingest CSV Data
   *
   * @param obj $data  Data object
   * @param array $job_data  Job data
   * @param string $record_type  Parent record type
   * @param int $i  Iterator
   * @return array  An array of job log IDs
   */
  public function ingestCsvData(obj $data, array $job_data, $record_type, int $i);

  /**
   * Insert Capture Data Elements
   *
   * @param array $capture_data_elements An array of capture data elements.
   * @param int $capture_dataset_id The capture dataset repository ID
   * @param array $data Job data
   * @return null
   */
  public function insertCaptureDataElementsAndFiles(array $capture_data_elements, int $capture_dataset_id, array $data);

  /**
   * Extract Data From External
   *
   * @param string $function_name Name of the function to call.
   * @param array $data Job data
   * @return array
   */
  public function extractDataFromExternal(string $function_name, array $data);

  /**
   * Get Model Data From Processing Service Results
   *
   * @param array $data Job data
   * @return array
   */
  public function getModelDataFromProcessingServiceResults(array $data);

  /**
   * Get Data From File Names
   *
   * @param array $data Job data
   * @return array
   */
  public function getDataFromFileNames(array $data);

  /**
   * Get Capture Dataset Data From Filenames
   *
   * @param array $image_file_names Image file names
   * @param array $data Job data
   * @return array
   */
  public function getDatasetDataFromFilenames(array $image_file_names, array $data);
  
  /**
   * Get File Info
   *
   * @param string $uuid The job UUID
   * @param string $file_name The file name
   */
  public function getFileInfo(string $uuid, string $file_name);

  /**
   * Get File Name Map
   *
   * @param array $job_data Job data
   * @return array
   */
  public function getFilenameMap(array $job_data);

  /**
   * Insert Model Files
   *
   * @param string $file_path The file path
   * @param string $model_repository_id The model's ID
   * @param string $data The data array
   * @return null
   */
  public function insertModelFiles($file_path = null, $model_id = null, $data = array());

  /**
   * Insert UV Maps
   *
   * @param string $file_path The file path
   * @param string $model_repository_id The model's ID
   * @param string $data The data array
   * @return null
   */
  public function insertUvMaps($file_path = null, $model_id = null, $data = array());

  /**
   * @param null $data The data to validate.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateEdanRecord(&$data = null);

  /**
   * Add EDAN Data to JSON
   *
   * @param string $item_json_path Path to item.json.
   * @param int $item_id The item iD.
   * @return json
   */
  public function addEdanDataToJson($item_json_path = null, $item_id = null);

}
