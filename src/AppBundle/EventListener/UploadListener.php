<?php

namespace AppBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Service\RepoValidateData;
use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Controller\ImportController;
use AppBundle\Service\RepoEdan;

class UploadListener
{
  /**
   * @var RepoStorageHybridController
   */
  private $repo_storage_controller;
  private $tokenStorage;
  private $import_controller;
  private $edan;

  public function __construct(
    Connection $conn,
    TokenStorageInterface $tokenStorage,
    ImportController $import_controller,
    RepoEdan $edan)
  {
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->tokenStorage = $tokenStorage;
    $this->import_controller = $import_controller;
    $this->edan = $edan;
    $this->connection = $conn;
  }
  
  /**
   * @param object $event  UploaderBundle's event object
   *
   * See (even though the documentation is a bit outdated):
   * https://github.com/1up-lab/OneupUploaderBundle/blob/master/Resources/doc/custom_logic.md
   */
  public function onUpload(PostPersistEvent $event)
  {
    $validation_results = (object)[];

    // Event response, request, and file.
    $response = $event->getResponse();
    $request = $event->getRequest();
    $file = $event->getFile();
    $record_id = '';

    // Posted data.
    $data = (object)[];
    $post = $request->request->all();
    $data->full_path = !empty($post['fullPath']) ? $post['fullPath'] : false;
    $data->job_id = !empty($post['jobId']) ? $post['jobId'] : false;
    $data->record_id = !empty($post['parentRecordId']) ? $post['parentRecordId'] : false;
    $data->record_type = !empty($post['parentRecordType']) ? $post['parentRecordType'] : false;
    $data->prevalidate = (!empty($post['prevalidate']) && ($post['prevalidate'] === 'true')) ? true : false;
    $data->simple_upload = (!empty($post['simpleUpload']) && ($post['simpleUpload'] === 'true')) ? true : false;
    $data->upload_path = !empty($post['uploadPath']) ? $post['uploadPath'] : false;

    // User data.
    $user = $this->tokenStorage->getToken()->getUser();
    $data->user_id = $user->getId();

    if (!$data->prevalidate) {
      $job_data = $this->repo_storage_controller->execute('getJobData', array($data->job_id));

      // TODO: Error handling...
      if (!empty($job_data)) {
        $data->job_id = $job_data['job_id'];
        $data->uuid = $job_data['uuid'];
      }
      
    }

    // $this->dumper($data);

    // Move uploaded files into the original directory structures, under a parent directory the jobId.
    if ($data->job_id) {

      // Move the files.
      $file_data = $this->moveFiles($file, $data);

      // If this is a simple upload (not Bulk Ingest or Simple Ingest), use the supplied upload_path - minus the document root.
      if ($data->simple_upload) {
        $full_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $data->upload_path . DIRECTORY_SEPARATOR . $file->getBasename());
        $file_size = filesize($data->upload_path . DIRECTORY_SEPARATOR .  $file->getBasename());
      } else {
        // Windows fix for the file path.
        $full_path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $file_data->full_path) : $file_data->full_path;
        $file_size = filesize($file_data->job_id_directory . DIRECTORY_SEPARATOR . $full_path);
      }

      // The final full path
      // Should look like this:
      // /uploads/repository/E13AB3F8-97FE-BB6B-CEE6-73BBEDE0DF91/FSGA-Incense-burner-v1-t/data/f1978_40-master/processed/f1978_40-master.obj
      $final_full_path = DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'repository' . DIRECTORY_SEPARATOR . $file_data->target_directory . DIRECTORY_SEPARATOR . $full_path;

      // Log the file to the 'file_uploads' table.
      if(!$file_data->prevalidate) {

        // If this is a simple upload, files are most likely being replaced.
        // Query the metadata storage for the file to update the record, and avoid duplicates.
        if ($data->simple_upload) {
          $existing_file = $this->repo_storage_controller->execute('getRecords', array(
              'base_table' => 'file_upload',
              'fields' => array(),
              'limit' => 1,
              'search_params' => array(
                0 => array('field_names' => array('file_upload.file_path'), 'search_values' => array($final_full_path), 'comparison' => '='),
              ),
              'search_type' => 'AND',
              'omit_active_field' => true,
            )
          );

          if (!empty($existing_file)) {
            $record_id = $existing_file[0]['file_upload_id'];
          }
        }

        $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'file_upload',
          'user_id' => $file_data->user_id,
          'values' => array(
            'job_id' => $file_data->job_id,
            'record_id' => $file_data->record_id,
            'record_type' => $file_data->record_type,
            'file_name' => $file->getBasename(),
            'file_path' => $final_full_path,
            'file_size' => filesize($_SERVER['DOCUMENT_ROOT'] . $final_full_path),
            'file_type' => strtolower($file->getExtension()), // $file->getMimeType()
            'file_hash' => md5_file($_SERVER['DOCUMENT_ROOT'] . $final_full_path),
          )
        ));
      }

    }

    // Pre-validate
    if ($data->prevalidate && $data->job_id && $data->record_type) {

      switch ($file->getExtension()) {
        case 'csv':
          // Run the CSV validation.
          $validation_results = $this->validateMetadata($data->job_id, $data->job_id_directory, $data->record_type, $file_data->record_id, $file->getBasename());
          // Remove the CSV file.
          // TODO: Remove the temporary directory.
          $finder = new Finder();
          $finder->files()->in($data->job_id_directory . '/');
          $finder->files()->name($file->getBasename());
          foreach ($finder as $file) {
            unlink($file->getPathname());
          }
          break;
      }
      
      // If errors are generated, return via $response.
      if(count((array)$validation_results) && isset($validation_results->results->messages)) {
        // TODO: Remember to remove the already uploaded file?
        $response['error'] = json_encode($validation_results->results->messages);
      }

      // Return the CSV so it can be displayed as a spreadsheet in the UI.
      $response['csv'] = json_encode($validation_results->csv);
      $response['csv_row_count'] = count($validation_results->csv);
    }

    return $response;
  }

  /**
   * @param object $data  Data object.
   * @param object $file  File object.
   * @return array
   */
  public function moveFiles($file = null, $data = null)
  {

    if (!empty($file) && !empty($data) && $data->job_id) {

      // If pre-validating, the target directory is a temporary directory. Otherwise, it's the job's UUID.
      $data->target_directory = $data->prevalidate ? $data->job_id : $data->uuid;
      $data->job_id_directory = str_replace($file->getBasename(), '', $file->getPathname()) . $data->target_directory;

      // If this is a simple upload, use the supplied upload_path.
      if ($data->simple_upload) {
        $data->job_id_directory = $data->upload_path;
      }

      // Create a directory with the job ID as the name if not present.
      if (!file_exists($data->job_id_directory)) {
        mkdir($data->job_id_directory, 0755, true);
      }

      // If there's a full path, then build-out the directory structure.
      if ($data->full_path) {

        $data->new_directory_path = str_replace('/' . $file->getBasename(), '', $data->full_path);

        // Create a directory with the $data->new_directory_path as the name if not present.
        if (!file_exists($data->job_id_directory . '/' . $data->new_directory_path)) {
          mkdir($data->job_id_directory . '/' . $data->new_directory_path, 0755, true);
        }
        // Move the file into the directory
        if (!file_exists($data->job_id_directory . '/' . $data->new_directory_path . '/' . $file->getBasename())) {
          rename($file->getPathname(), $data->job_id_directory . '/' . $data->new_directory_path . '/' . $file->getBasename());
        } else {
          // Remove the uploaded file???
          if (is_file($file->getPathname())) {
            unlink($file->getPathname());
          }
        }
      }

      // If there isn't a full path, then move the files into the root of the jobId directory.
      if (!$data->full_path) {
        // Move the file into the directory
        if (!file_exists($data->job_id_directory . '/' . $file->getBasename())) {
          rename($file->getPathname(), $data->job_id_directory . '/' . $file->getBasename());
        } else {
          // Remove the uploaded file???
          if (is_file($file->getPathname())) {
            unlink($file->getPathname());
          }
        }
      }

    }

    return $data;
  }

  /**
   * @param int $job_id  The job ID
   * @param int $job_id_directory  The job directory
   * @return json
   */
  public function validateMetadata($job_id = null, $job_id_directory = null, $record_type = null, $record_id, $filename = null)
  {
    $schema = false;
    $blacklisted_fields = array();
    $data = (object)[];

    // TODO: feed this into this method.
    if(empty($job_id)) {
      $blacklisted_fields = array(
        'project_id',
      );
    }

    // Construct the data.
    $data->csv = $this->import_controller->constructImportData($job_id_directory, $filename); // $itemsController

    // Remove the column headers from the array.
    $column_headers = $data->csv['0'];
    unset($data->csv['0']);
    $data->csv = array_values($data->csv);

    // if(empty($data->csv)) {
    //   unset($data->row_ids_results['is_valid']);
    //   unset($data->results['is_valid']);
    //   $data->results['messages'][0] = array('row' => 'CSV', 'error' => 'CSV is empty');
    // }

    if(!empty($data->csv)) {
      // Set the schema to validate against.
      switch (true) {
        case stristr($filename, 'subjects'):
          $schema = 'subject';
          break;
        case stristr($filename, 'items'):
          $schema = 'item';
          break;
        case stristr($filename, 'capture_datasets'):
          $schema = 'capture_dataset';
          break;
        case stristr($filename, 'models'):
          $schema = 'model';
          break;
        default:
          $schema = false;
      }

      // Could simply use the substr function and stripm off the last 5 characters of the CSV's file name 
      // as long as all CSV filenames followed a strict naming convention.
      // Example: projects.csv, subjects.csv, items.csv, capture_datasets.csv, capture_dataset_elements.csv
      // $schema = substr($filename, 0, -5);

      // TODO: Error handling if there is no $schema
      // if(!$schema) // Do something

      if($schema) {
        // Instantiate the RepoValidateData class.
        $repoValidate = new RepoValidateData($this->connection);

        // Check to see if a CSV's 'import_row_id' has gaps or is not sequential.
        $data->row_ids_results = $repoValidate->validateRowIds($data->csv, $schema);

        // Validate that the values within the capture_dataset_field_id fields are not already in the database.
        if($schema === 'capture_dataset') {

          switch($record_type) {
            case 'subject':
              // Validate that the 'capture_dataset_field_id' value is unique within the CSV when the parent record is a subject.
              $data->capture_dataset_field_id_results = $repoValidate->validateCaptureDatasetFieldIdInCsv($data->csv);
              break;
            case 'item':
              // Get the parent records.
              $parent_records = $this->repo_storage_controller->execute('getParentRecords', array(
                'base_record_id' => $record_id,
                'record_type' => $record_type,
              ));
              // Validate that the 'capture_dataset_field_id' value is unique for datasets that share the same Project and Item (within the database).
              if (!empty($parent_records)) {
                $data->capture_dataset_field_id_results = $repoValidate->validateCaptureDatasetFieldId($data->csv, $parent_records);
              }
              break;
          }

        }

        // Execute the validation against the JSON schema.
        $data->results = (object)$repoValidate->validateData($data->csv, $schema, $record_type, $blacklisted_fields);

        // Validate that the EDAN record exists (subject_guid), and that the holding entity relation in metadata storage exists.
        if (($schema === 'subject') && !empty($data->csv)) {
          // Validate that the EDAN record exists.
          $data->edan_results = $this->validateEdanRecord($data);
          // Validate that the holding entity exists.
          foreach($data->csv as $csv_key => $csv_value) {
            $data->holding_entity_results = $repoValidate->getHoldingEntity($csv_value->holding_entity_guid);
            // If not found, return the error message.
            if (empty($data->holding_entity_results)) {
              $data->holding_entity_results['messages'][] = array('row' => 'Row ' . ($csv_key+1), 'error' => $csv_value->holding_entity_guid . ' not found. Please contact the administrator for assistance.');
            }
          }
        }

        // Add the column headers back to the array.
        array_unshift($data->csv, $column_headers);

        // Merge row_ids_results messages.
        if(isset($data->row_ids_results['messages'])) {
          unset($data->row_ids_results['is_valid']);
          $data->results = (object)array_merge_recursive($data->row_ids_results, (array)$data->results);
        }

        // Merge capture_dataset_field_id_results messages.
        if(isset($data->capture_dataset_field_id_results['messages'])) {
          unset($data->capture_dataset_field_id_results['is_valid']);
          $data->results = (object)array_merge_recursive($data->capture_dataset_field_id_results, (array)$data->results);
        }

        // Merge edan_results messages.
        if(isset($data->edan_results['messages'])) {
          unset($data->edan_results['is_valid']);
          $data->results = (object)array_merge_recursive($data->edan_results, (array)$data->results);
        }

        // Merge holding entity messages.
        if(isset($data->holding_entity_results['messages'])) {
          unset($data->holding_entity_results['is_valid']);
          $data->results = (object)array_merge_recursive($data->holding_entity_results, (array)$data->results);
        }
      }
    }

    return $data;
  }

  /**
   * @param null $data The data to validate.
   * @return mixed array containing success/fail value, and any messages.
   */
  public function validateEdanRecord(&$data = NULL) {

    $return = array('is_valid' => false);

    // If no data is passed, set a message.
    if(empty($data)) $return['messages'][] = 'Nothing to validate. Please provide an object to validate.';

    // If data is passed, go ahead and process.
    if(!empty($data)) {
      // Loop through the data.
      foreach($data->csv as $csv_key => $csv_value) {

        // Check to see if the EDAN record exists.
        if (empty($subject_exists)) {
          // Query EDAN
          $result = $this->edan->getRecord($csv_value->subject_guid);
          // Catch if there is an error.
          if (isset($result['error'])) {
            $return['messages'][$csv_key] = array('row' => 'Row ' . ($csv_key+1), 'error' => 'EDAN record not found. subject_guid: ' . $csv_value->subject_guid);
          }
        }
        
      }
    }

    // If there are no messages, then return true for 'is_valid'.
    if(!isset($return['messages'])) {
      $return['is_valid'] = true;
    }

    return $return;
  }

  public function dumper($data = false, $die = true, $ip_address=false){
    if(!$ip_address || $ip_address == $_SERVER["REMOTE_ADDR"]){
      echo '<pre>';
      var_dump($data);
      echo '</pre>';
      if($die) die();
    }
  }

}