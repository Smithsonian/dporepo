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
  public function importCsv($params = array());

  /**
   * Prepare Data
   *
   * @param string $job_type The job type (One of: subjects, items, capture datasets, models)
   * @param string $job_upload_directory The upload directory
   * @return array Import result and/or any messages
   */
  public function prepareData($job_type = '', $job_upload_directory = '');

  /**
   * Ingest CSV Data
   *
   * @param obj $data  Data object
   * @param array $job_data  Job data
   * @param string $record_type  Parent record type
   * @param int $i  Iterator
   * @return array  An array of job log IDs
   */
  public function ingestCsvData($data, $job_data = array(), $record_type = '', $i);

  /**
   * Insert Capture Data Elements
   *
   * @param array $capture_data_elements An array of capture data elements.
   * @param int $capture_dataset_id The capture dataset repository ID
   * @param array $data Job data
   * @return null
   */
  public function insertCaptureDataElementsAndFiles($capture_data_elements = array(), $capture_dataset_id = '', $data = array());

  /**
   * Extract Data From External
   *
   * @param string $function_name Name of the function to call.
   * @param array $data Job data
   * @return array
   */
  public function extractDataFromExternal($function_name = '', $data = array());

  /**
   * Get Model Data From Processing Service Results
   *
   * @param array $data Job data
   * @return array
   */
  public function getModelDataFromProcessingServiceResults($data = array());

  /**
   * Get Data From File Names
   *
   * @param array $data Job data
   * @return array
   */
  public function getDataFromFileNames($data = array());

  /**
   * Get Capture Dataset Data From Filenames
   *
   * @param array $image_file_names Image file names
   * @param array $data Job data
   * @return array
   */
  public function getDatasetDataFromFilenames($image_file_names = array(), $data = array());
  
  /**
   * Get File Info
   *
   * @param string $uuid The job UUID
   * @param string $file_name The file name
   */
  public function getFileInfo($uuid = '', $file_name = '');

  /**
   * Get File Name Map
   *
   * @param array $job_data Job data
   * @return array
   */
  public function getFilenameMap($job_data = array());

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
   * @param int $item_id The item iD.
   * @return json
   */
  public function addEdanDataToJson($item_id = null);

}
