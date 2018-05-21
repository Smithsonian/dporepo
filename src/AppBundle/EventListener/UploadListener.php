<?php

namespace AppBundle\EventListener;

use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Psr\Container\ContainerInterface;
use Oneup\UploaderBundle\Event\PostPersistEvent;
use AppBundle\Service\RepoValidateData;
use AppBundle\Controller\RepoStorageHybridController;

class UploadListener
{
  /**
   * @var RepoStorageHybridController
   */
  private $repo_storage_controller;
  private $tokenStorage;
  private $container;

  public function __construct(RepoStorageHybridController $repo_storage_controller, TokenStorageInterface $tokenStorage, ContainerInterface $container)
  {
    $this->repo_storage_controller = $repo_storage_controller;
    $this->tokenStorage = $tokenStorage;
    $this->container = $container;
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
    $data->parent_record_id = !empty($post['parentRecordId']) ? $post['parentRecordId'] : false;
    $data->prevalidate = (!empty($post['prevalidate']) && ($post['prevalidate'] === 'true')) ? true : false;
    // User data.
    $user = $this->tokenStorage->getToken()->getUser();
    $data->user_id = $user->getId();

    // Move uploaded files into the original directory structures, under a parent directory the jobId.
    if ($data->job_id && $data->parent_record_id) {

      // Move the files.
      $this->move_files($file, $data);

      // If not pre-validating, perform the CSV ingest.
      if(!$data->prevalidate && ($file->getExtension() === 'csv')) {

        // $this->dumper($data->job_id);

        // Construct the data.
        $data->csv = $this->construct_import_data($data->job_id_directory, $file->getBasename());

        if(!empty($data->csv)) {

          // Set the type of data being imported.
          $data_types = array('subject', 'item', 'capture_dataset', 'capture_dataset_element');
          foreach ($data_types as $data_type) {
              if (strstr($file->getBasename(), $data_type)) {
                $data->type = $data_type;
              }
          }

          $this->ingest_csv_data($data);
        }
      }

      
    }

    // Pre-validate
    if ($data->prevalidate && $data->job_id) {

      switch ($file->getExtension()) {
        case 'csv':
          // Run the CSV validation.
          $validation_results = $this->validate_metadata($data->job_id, $data->job_id_directory, $file->getBasename()); // , $this->container, $items
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
        $response['csv'] = json_encode($validation_results->csv);
      }
    }

    return $response;
  }

  /**
   * @param object $data  Data object.
   * @param object $file  File object.
   */
  public function move_files($file = null, $data = null)
  {
    if (!empty($file) && !empty($data) && $data->job_id && $data->parent_record_id) {

      $data->job_id_directory = str_replace($file->getBasename(), '', $file->getPathname()) . $data->job_id;

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
  }

  /**
   * @param int $job_id  The job ID
   * @param int $job_id_directory  The job directory
   * @return json
   */
  public function validate_metadata($job_id = null, $job_id_directory = null, $filename = null) // , $thisContainer, $itemsController
  {

    $blacklisted_fields = array();
    $data = (object)[];

    // TODO: feed this into this method.
    if(empty($job_id)) {
      $blacklisted_fields = array(
        'project_repository_id',
      );
    }

    // Construct the data.
    $data->csv = $this->construct_import_data($job_id_directory, $filename); // , $thisContainer, $itemsController

    if(!empty($data->csv)) {
      // Set the schema to validate against.
      if(stristr($filename, 'projects')) {
        $schema = 'project';
      }
      if(stristr($filename, 'subjects')) {
        $schema = 'subject';
      }
      if(stristr($filename, 'items')) {
        $schema = 'item';
      }
      // Instantiate the RepoValidateData class.
      $repoValidate = new RepoValidateData();
      // Execute the validation.
      $data->results = (object)$repoValidate->validateData($data->csv, $schema, $blacklisted_fields);
    }

    return $data;
  }

  /**
   * @param string $job_id_directory  The upload directory
   * @param string $filename  The file name
   * @return array  Import result and/or any messages
   */
  public function construct_import_data($job_id_directory = null, $filename = null) // , $thisContainer, $itemsController
  {

    $json_object = array();

    if(!empty($job_id_directory)) {

      $finder = new Finder();
      $finder->files()->in($job_id_directory . '/');
      $finder->files()->name($filename);

      foreach ($finder as $file) {
        // Get the contents of the CSV.
        $csv = $file->getContents();
      }

      // Convert the CSV to JSON.
      $array = array_map('str_getcsv', explode("\n", $csv));
      $json = json_encode($array);

      // Convert the JSON to a PHP array.
      $json_array = json_decode($json, false);

      // Read the first key from the array, which is the column headers.
      $target_fields = $json_array[0];

      // TODO: move into a vz-specific method?
      // [VZ IMPORT ONLY] Convert field names to satisfy the validator.
      foreach ($target_fields as $tfk => $tfv) {
        // [VZ IMPORT ONLY] Convert the 'import_subject_id' field name to 'subject_repository_id'.
        if($tfv === 'import_subject_id') {
          $target_fields[$tfk] = 'subject_repository_id';
        }
      }

      // Remove the column headers from the array.
      array_shift($json_array);

      foreach ($json_array as $key => $value) {
        // Replace numeric keys with field names.
        foreach ($value as $k => $v) {
          $field_name = $target_fields[$k];
          unset($json_array[$key][$k]);
          // // If present, bring the project_repository_id into the array.
          // $json_array[$key][$field_name] = ($field_name === 'project_repository_id') ? (int)$id : null;
          // TODO: move into a vz-specific method?
          // [VZ IMPORT ONLY] Strip 'USNM ' from the 'subject_repository_id' field.
          $json_array[$key][$field_name] = ($field_name === 'subject_repository_id') ? (int)str_replace('USNM ', '', $v) : $v;

          // TODO: figure out a way to tap into the ItemsController.
          // Look-up the ID for the 'item_type' (not when validating data, only when importing data).
          // if ((debug_backtrace()[1]['function'] !== 'validate_metadata') && ($field_name === 'item_type')) {
          //   $item_type_lookup_options = $itemsController->get_item_types($thisContainer);
          //   $json_array[$key][$field_name] = (int)$item_type_lookup_options[$v];
          // }

        }
        // Convert the array to an object.
        $json_object[] = (object)$json_array[$key];
      }

    }

    // $this->dumper($json_object);

    return $json_object;
  }

  /**
   * @param string $data  Data object
   * @return null
   */
  public function ingest_csv_data($data = null) {

    $session = new Session();

    // if(!empty($data->parent_record_id)) {
    //   // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
    //   $this->repo_storage_controller->setContainer($this->container);
    //   $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $data->parent_record_id));
    //   if(!$project) throw new UploadException('The Project record does not exist');
    // }

    $this->repo_storage_controller->setContainer($this->container);

    switch ($data->type) {
      // Ingest subjects
      case 'subject':
        // Insert into the job_log table
        // TODO: Feed the 'job_log_label' to the log leveraging fields from a form submission in the UI.
        $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job_log',
          'user_id' => $data->user_id,
          'values' => array(
            'job_id' => $data->job_id,
            'job_log_status' => 'start',
            'job_log_label' => 'Import subjects',
            'job_log_description' => 'Import started',
          )
        ));

        $subject_repository_ids = array();

        foreach ($data->csv as $subject_key => $subject) {
          // Set the project_repository_id
          $subject->project_repository_id = (int)$data->parent_record_id;
          // Insert into the subject table
          $subject_repository_id = $this->repo_storage_controller->execute('saveRecord', array(
            'base_table' => 'subject',
            'user_id' => $data->user_id,
            'values' => (array)$subject
          ));
          $subject_repository_ids[$subject->subject_repository_id] = $subject_repository_id;

          // Insert into the job_import_record table
          $job_import_record_id = $this->repo_storage_controller->execute('saveRecord', array(
            'base_table' => 'job_import_record',
            'user_id' => $data->user_id,
            'values' => array(
              'job_id' => $data->job_id,
              'record_id' => $subject_repository_id,
              'project_id' => (int)$data->parent_record_id,
              'record_table' => 'subject',
              'description' => $subject->local_subject_id . ' - ' . $subject->subject_display_name,
            )
          ));

        }

        // Set the session variable 'subject_repository_ids'.
        $session->set('subject_repository_ids', $subject_repository_ids);

        // Insert into the job_log table
        // TODO: Feed the 'job_log_label' to the log leveraging fields from a form submission in the UI.
        $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job_log',
          'user_id' => $data->user_id,
          'values' => array(
            'job_id' => $data->job_id,
            'job_log_status' => 'finish',
            'job_log_label' => 'Import subjects',
            'job_log_description' => 'Import finished',
          )
        ));
        break;

      // Ingest items
      case 'item':
        // Insert into the job_log table
        // TODO: Feed the 'job_label' and 'job_type' to the log leveraging fields from a form submission in the UI.
        $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job_log',
          'user_id' => $data->user_id,
          'values' => array(
            'job_id' => $data->job_id,
            'job_log_status' => 'start',
            'job_log_label' => 'Import items',
            'job_log_description' => 'Import started',
          )
        ));

        $subject_repository_ids = $session->get('subject_repository_ids');

        foreach ($data->csv as $item_key => $item) {
          // Set the subject_repository_id
          $item->subject_repository_id = $subject_repository_ids[$item->subject_repository_id];
          // Insert into the item table
          $item_repository_id = $this->repo_storage_controller->execute('saveRecord', array(
            'base_table' => 'item',
            'user_id' => $data->user_id,
            'values' => (array)$item
          ));
          
          // Insert into the job_import_record table
          $job_import_record_id = $this->repo_storage_controller->execute('saveRecord', array(
            'base_table' => 'job_import_record',
            'user_id' => $data->user_id,
            'values' => array(
              'job_id' => $data->job_id,
              'record_id' => $item_repository_id,
              'project_id' => (int)$data->parent_record_id,
              'record_table' => 'item',
              'description' => $item->item_display_name,
            )
          ));
        }

        // Set the session variable 'subject_repository_ids'.
        $session->remove('subject_repository_ids');

        // Insert into the job_log table
        $job_log_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job_log',
          'user_id' => $data->user_id,
          'values' => array(
            'job_id' => $data->job_id,
            'job_log_status' => 'finish',
            'job_log_label' => 'Import items',
            'job_log_description' => 'Import finished',
          )
        ));
        break;

    }

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