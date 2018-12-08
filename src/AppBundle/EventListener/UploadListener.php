<?php

namespace AppBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use AppBundle\Service\RepoValidateData;
use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Controller\ImportController;
use Doctrine\DBAL\Driver\Connection;

class UploadListener
{
  /**
   * @var RepoStorageHybridController
   */
  private $repo_storage_controller;
  private $tokenStorage;
  private $import_controller;

  public function __construct(
    Connection $conn,
    TokenStorageInterface $tokenStorage,
    ImportController $import_controller)
  {
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->tokenStorage = $tokenStorage;
    $this->import_controller = $import_controller;
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

    // Posted data.
    $data = (object)[];
    $post = $request->request->all();
    $data->full_path = !empty($post['fullPath']) ? $post['fullPath'] : false;
    $data->job_id = !empty($post['jobId']) ? $post['jobId'] : false;
    $data->record_id = !empty($post['parentRecordId']) ? $post['parentRecordId'] : false;
    $data->record_type = !empty($post['parentRecordType']) ? $post['parentRecordType'] : false;
    $data->prevalidate = (!empty($post['prevalidate']) && ($post['prevalidate'] === 'true')) ? true : false;
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

    // Move uploaded files into the original directory structures, under a parent directory the jobId.
    if ($data->job_id && $data->record_id) {

      // Move the files.
      $file_data = $this->move_files($file, $data);

      // Windows fix for the file path.
      $full_path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $file_data->full_path) : $file_data->full_path;

      // Log the file to the 'file_uploads' table.
      if(!$file_data->prevalidate) {
        $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'file_upload',
          'user_id' => $file_data->user_id,
          'values' => array(
            'job_id' => $file_data->job_id,
            'record_id' => $file_data->record_id,
            'record_type' => $file_data->record_type,
            'file_name' => $file->getBasename(),
            'file_path' => DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR . 'repository' . DIRECTORY_SEPARATOR . $file_data->target_directory . DIRECTORY_SEPARATOR . $full_path,
            'file_size' => filesize($file_data->job_id_directory . '/' . $full_path),
            'file_type' => $file->getExtension(), // $file->getMimeType()
            'file_hash' => '',
          )
        ));
      }

    }

    // Pre-validate
    if ($data->prevalidate && $data->job_id && $data->record_type) {

      switch ($file->getExtension()) {
        case 'csv':
          // Run the CSV validation.
          $validation_results = $this->validate_metadata($data->job_id, $data->job_id_directory, $data->record_type, $file_data->record_id, $file->getBasename());
          // Remove the CSV file.
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
  public function move_files($file = null, $data = null)
  {
    if (!empty($file) && !empty($data) && $data->job_id && $data->record_id) {

      // If pre-validating, the target directory is a temporary directory. Otherwise, it's the job's UUID.
      $data->target_directory = $data->prevalidate ? $data->job_id : $data->uuid;
      $data->job_id_directory = str_replace($file->getBasename(), '', $file->getPathname()) . $data->target_directory;

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
  public function validate_metadata($job_id = null, $job_id_directory = null, $record_type = null, $record_id, $filename = null)
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
    $data->csv = $this->import_controller->construct_import_data($job_id_directory, $filename); // $itemsController

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
      }
    }

    return $data;
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