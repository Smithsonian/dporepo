<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Utils\AppUtilities;

use AppBundle\Controller\ItemsController;
use AppBundle\Controller\DatasetsController;
use AppBundle\Controller\ModelController;

class RepoImport implements RepoImportInterface {

  /**
   * @var object $u
   */
  public $u;

  /**
   * @var object $tokenStorage
   */
  public $tokenStorage;

  /**
   * @var object $itemsController
   */
  public $itemsController;

  /**
   * @var object $datasetsController
   */
  public $datasetsController;

  /**
   * @var object $modelsController
   */
  public $modelsController;

  /**
   * @var object $kernel
   */
  public $kernel;

  /**
   * @var string $project_directory
   */
  private $project_directory;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * @var string $external_file_storage_path
   */
  private $external_file_storage_path;

  /**
   * @var object $conn
   */
  private $conn;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var object $repoValidate
   */
  private $repoValidate;

  /**
   * Constructor
   * @param object  $kernel  Symfony's kernel object
   * @param string  $uploads_directory  Uploads directory path
   * @param string  $external_file_storage_path  External file storage path
   * @param string  $conn  The database connection
   */
  public function __construct(AppUtilities $u, TokenStorageInterface $tokenStorage, ItemsController $itemsController, DatasetsController $datasetsController, ModelController $modelsController, KernelInterface $kernel, string $uploads_directory, string $external_file_storage_path, \Doctrine\DBAL\Connection $conn)
  {
    $this->u = new AppUtilities();
    $this->tokenStorage = $tokenStorage;
    $this->itemsController = $itemsController;
    $this->datasetsController = $datasetsController;
    $this->modelsController = $modelsController;
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    // $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    $this->uploads_directory = __DIR__ . '/../../../web/uploads/repository/';
    $this->external_file_storage_path = $external_file_storage_path;
    $this->conn = $conn;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->repoValidate = new RepoValidateData($conn);
  }

  /**
   * Import CSV
   *
   * @param array $params Parameters: job_id, project_id, parent_record_id, parent_record_type
   * @return array
   */
  public function import_csv($params = array())
  {

    // $this->u->dumper($this->uploads_directory);

    $return = $csv_types = array();

    // If $params are empty, return an error.
    if(empty($params)) {
      $return['errors'][] = 'Parameters are empty';
    } else {
      // If $params values are empty, return errors.
      foreach ($params as $pkey => $pvalue) {
        if (empty($pvalue)) {
          $return['errors'][] = $pkey . ' is empty';
        }
      }
    }

    // If there are errors, return now.
    // TODO: insert errors into the database (job_log table).
    if (!empty($return['errors'])) return $return;
    
    $job_data = $this->repo_storage_controller->execute('getJobData', array($params['uuid']));

    // Throw a 404 if the job record doesn't exist.
    if (!$job_data) {
      $return['errors'][] = 'The Job record doesn\'t exist';
      return $return;
    }

    // Don't perform the metadata ingest if the job_status has been set to 'failed'.
    if ($job_data['job_status'] === 'failed') {
      $return['errors'][] = 'The job has failed. Exiting metadata ingest process.';
      return $return;
    }

    // Get user data.
    if( method_exists($this->tokenStorage, 'getUser') ) {
      $user = $this->tokenStorage->getToken()->getUser();
      $user_id = $user->getId();
    } else {
      $user_id = 0;
    }

    // Clear session data.
    $session = new Session();
    $session->remove('new_repository_ids_1');
    $session->remove('new_repository_ids_2');
    $session->remove('new_repository_ids_3');
    $session->remove('new_repository_ids_4');

    // Set the job type (e.g. subjects metadata import, items metadata import, capture datasets metadata import, models metadata import).
    // $job_data = $this->repo_storage_controller->execute('getJobData', array($params['uuid']));

    if (!empty($params['uuid']) && !empty($params['parent_project_id']) && !empty($params['parent_record_id']) && !empty($params['parent_record_type'])) {

      $ids = (object)array(
        'job_id' => $job_data['job_id'],
        'uuid' => $params['uuid'],
        'parent_project_id' => $params['parent_project_id'],
        'parent_record_id' => $params['parent_record_id'],
      );

      // Remove 'metadata import' from the $job_data['job_type'].
      $job_type = str_replace(' metadata import', '', $job_data['job_type']);

      if (!empty($job_type)) {
        // Prepare the data.
        $data = $this->prepare_data($job_type, $this->uploads_directory . $ids->uuid);

        // Ingest data.
        if (!empty($data)) {
          // Associate a Model to an Item
          // In order to associate a Model to an Item (normally a Model is associated to a Capture Dataset), need to:
          // 1) Get 'type' field values in the $data array.
          // 2) Then determine if there's an 'item' type and a 'model' type, but no 'capture_dataset' type.
          foreach ($data as $csv_key => $csv_value) {
            $csv_types[] = $csv_value['type'];
          }

          // Set the job_status to 'model' if that's the only CSV type being imported.
          if((count($csv_types) === 1) && in_array('model', $csv_types)) {
            $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'job',
              'record_id' => $ids->job_id,
              'user_id' => 0,
              'values' => array(
                'job_type' => 'models metadata import',
              )
            ));
          }

          // Execute the ingest.
          $i = 1;
          foreach ($data as $csv_key => $csv_value) {

            // Don't perform an ingest without CSV data (an empty CSV).
            if(isset($csv_value['csv'])) {
              $return['job_log_ids'] = $this->ingest_csv_data($csv_value, $ids, $params['parent_record_type'], $i);
            }

            $i++;
          }
        }
      }
    }

    // Update the job table to indicate that the CSV import failed.
    if (!empty($params['uuid']) && empty($return['job_log_ids'])) {
      $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => 'job',
        'record_id' => $job_data['job_id'],
        'user_id' => $user_id,
        'values' => array(
          'job_status' => 'failed',
          'date_completed' => date('Y-m-d H:i:s'),
          'qa_required' => 0,
          'qa_approved_time' => null,
        )
      ));
      // Populate the errors array to return to front end.
      $return['errors'][] = 'Metadata ingest failed. Job ID: ' . $job_data['job_id'];
    } else {
      // Update the job table to set the status from 'metadata ingest in progress' to 'file transfer in progress'.
      $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => 'job',
        'record_id' => $job_data['job_id'],
        'user_id' => $user_id,
        'values' => array(
          'job_status' => 'file transfer in progress',
          'date_completed' => date('Y-m-d H:i:s'),
          'qa_required' => 0,
          'qa_approved_time' => null,
        )
      ));
    }

    // $this->addFlash('message', '<strong>Upload Succeeded!</strong> Files will be validated shortly. The validation scheduled task runs every 30 seconds, but it may take time to grind through the validation process. Please check back!');

    return $return;
  }

  /**
   * @param string $job_type The job type (One of: subjects, items, capture datasets, models)
   * @param string $job_upload_directory The upload directory
   * @return array Import result and/or any messages
   */
  public function prepare_data($job_type = null, $job_upload_directory = null)
  {

    $data = array();

    if (!empty($job_upload_directory)) {

      $finder = new Finder();
      $finder->files()->in($job_upload_directory);

      // Prevent additional CSVs from being imported according to the $job_type.
      // Assign keys to each CSV, with projects first, subjects second, and items third.
      foreach ($finder as $file) {
        if (($job_type === 'subjects') && stristr($file->getFilename(), 'subjects')) {
          $csv[0]['type'] = 'subject';
          $csv[0]['data'] = $file->getContents();
        }
        if ((($job_type === 'subjects') || ($job_type === 'items')) && stristr($file->getFilename(), 'items')) {
          $csv[1]['type'] = 'item';
          $csv[1]['data'] = $file->getContents();
        }
        if ((($job_type === 'subjects') || ($job_type === 'items') || ($job_type === 'capture datasets') || ($job_type === 'models')) && stristr($file->getFilename(), 'capture_datasets')) {
          $csv[2]['type'] = 'capture_dataset';
          $csv[2]['data'] = $file->getContents();
        }
        if ((($job_type === 'subjects') || ($job_type === 'items') || ($job_type === 'capture datasets') || ($job_type === 'models')) && stristr($file->getFilename(), 'models')) {
          $csv[3]['type'] = 'model';
          $csv[3]['data'] = $file->getContents();
        }
      }

      // Sort the CSV array by key.

      ksort($csv);
      // Re-index the CSV array.
      $csv = array_values($csv);

      foreach ($csv as $csv_key => $csv_value) {

        // Convert the CSV to JSON.
        $array = array_map('str_getcsv', explode("\n", $csv_value['data']));
        $json = json_encode($array);

        // Convert the JSON to a PHP array.
        $json_array = json_decode($json, false);
        // Add the type to the array.
        $json_array['type'] = $csv_value['type'];

        // Read the first key from the array, which is the column headers.
        $target_fields = $json_array[0];

        // Remove the column headers from the array.
        array_shift($json_array);

        foreach ($json_array as $key => $value) {
          // Replace numeric keys with field names.
          if (is_numeric($key)) {
            foreach ($value as $k => $v) {

              $field_name = $target_fields[$k];

              unset($json_array[$key][$k]);

              // If present, bring the project_repository_id into the array.
              // $json_array[$key][$field_name] = ($field_name === 'project_repository_id') ? (int)$id : null;

              // Set the value of the field name.
              $json_array[$key][$field_name] = $v;

              // ITEM LOOKUPS
              // Look-up the ID for the 'item_type'.
              if ($field_name === 'item_type') {
                $item_type_lookup_options = $this->itemsController->get_item_types();
                $json_array[$key][$field_name] = (int)$item_type_lookup_options[$v];
              }

              // CAPTURE DATASET LOOKUPS
              // Look-up the ID for the 'capture_method'.
              if ($field_name === 'capture_method') {
                $capture_method_lookup_options = $this->datasetsController->get_capture_methods();
                $json_array[$key][$field_name] = (int)$capture_method_lookup_options[$v];
              }

              // Look-up the ID for the 'capture_dataset_type'.
              if ($field_name === 'capture_dataset_type') {
                $capture_dataset_type_lookup_options = $this->datasetsController->get_dataset_types();
                $json_array[$key][$field_name] = (int)$capture_dataset_type_lookup_options[$v];
              }

              // Look-up the ID for the 'item_position_type'.
              if ($field_name === 'item_position_type') {
                $item_position_type_lookup_options = $this->datasetsController->get_item_position_types();
                $json_array[$key][$field_name] = (int)$item_position_type_lookup_options[$v];
              }

              // Look-up the ID for the 'focus_type'.
              if ($field_name === 'focus_type') {
                $focus_type_lookup_options = $this->datasetsController->get_focus_types();
                $json_array[$key][$field_name] = (int)$focus_type_lookup_options[$v];
              }

              // Look-up the ID for the 'light_source_type'.
              if ($field_name === 'light_source_type') {
                $light_source_type_lookup_options = $this->datasetsController->get_light_source_types();
                $json_array[$key][$field_name] = (int)$light_source_type_lookup_options[$v];
              }

              // Look-up the ID for the 'background_removal_method'.
              if ($field_name === 'background_removal_method') {
                $background_removal_method_lookup_options = $this->datasetsController->get_background_removal_methods();
                $json_array[$key][$field_name] = (int)$background_removal_method_lookup_options[$v];
              }

              // Look-up the ID for the 'cluster_type'.
              if ($field_name === 'cluster_type') {
                $camera_cluster_types_lookup_options = $this->datasetsController->get_camera_cluster_types();
                $json_array[$key][$field_name] = (int)$camera_cluster_types_lookup_options[$v];
              }

              // MODEL LOOKUPS
              // TODO:
              // Model lookup options not in database! Need to either
              // 1) place into database and create a way to manage
              // 2) convert all lookups to draw from the JSON schema (preferred!)

              // Look-up the ID for the 'creation_method'.
              if ($field_name === 'creation_method') {
                $creation_method_lookup_options = array('scan-to-mesh' => 1, 'CAD' => 2);
                $json_array[$key][$field_name] = (int)$creation_method_lookup_options[$v];
              }

              // Look-up the ID for the 'model_modality'.
              if ($field_name === 'model_modality') {
                $model_modality_lookup_options = array('point_cloud' => 1, 'mesh' => 2);
                $json_array[$key][$field_name] = (int)$model_modality_lookup_options[$v];
              }

              // Look-up the ID for the 'units'.
              if ($field_name === 'units') {
                $units_lookup_options = $this->modelsController->get_unit();
                $json_array[$key][$field_name] = (int)$units_lookup_options[$v];
              }

              // Look-up the ID for the 'model_purpose'.
              if ($field_name === 'model_purpose') {
                $model_purpose_lookup_options = array('master' => 1, 'delivery_web' => 2, 'delivery_print' => 3, 'intermediate_processing_step' => 4);
                $json_array[$key][$field_name] = (int)$model_purpose_lookup_options[$v];
              }

            }

            // If an array of data contains 1 or fewer keys, then it means the row is empty.
            // Unset the empty row, so it doesn't get inserted into the database.
            if (count(array_keys((array)$json_array[$key])) > 1) {
              // Convert the array to an object.
              $data[$csv_key]['csv'][] = (object)$json_array[$key];
            }
            
          }

          if (!is_numeric($key)) {
            $data[$csv_key]['type'] = $value;
          }
        }

      }

    }

    return $data;
  }

  /**
   * @param string $data  Data object
   * @param int $job_id  Job ID
   * @param int $parent_record_id  Parent record ID
   * @return array  An array of job log IDs
   */
  public function ingest_csv_data($data = null, $ids = array(), $parent_record_type = null, $i = 1) {

    $session = new Session();
    $data = (object)$data;
    $job_log_ids = array();
    $job_status = 'finished';

    // Get user data.
    if( method_exists($this->tokenStorage, 'getUser') ) {
      $user = $this->tokenStorage->getToken()->getUser();
      $data->user_id = $user->getId();
    } else {
      $data->user_id = 0;
    }

    // Job ID and parent record ID
    $data->job_id = isset($ids->job_id) ? $ids->job_id : false;
    $data->uuid = isset($ids->uuid) ? $ids->uuid : false;
    $data->parent_project_id = isset($ids->parent_project_id) ? $ids->parent_project_id : false;
    $data->parent_record_id = isset($ids->parent_record_id) ? $ids->parent_record_id : false;
    $data->parent_record_type = isset($parent_record_type) ? $parent_record_type : false;

    // Just in case: throw a 404 if either job ID or parent record ID aren't passed.
    if (!$data->job_id) throw $this->createNotFoundException('Job ID not provided.');
    if (!$data->uuid) throw $this->createNotFoundException('UUID not provided.');
    if (!$data->parent_project_id) throw $this->createNotFoundException('Parent Project record ID not provided.');
    if (!$data->parent_record_id) throw $this->createNotFoundException('Parent record ID not provided.');

    // Check to see if the parent project record exists/active, and if it doesn't, throw a createNotFoundException (404).
    if (!empty($data->parent_project_id)) {
      $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $data->parent_project_id));
      // If no project is returned, throw a createNotFoundException (404).
      if (!$project) throw $this->createNotFoundException('The Project record doesn\'t exist');
    }

    // $data->type is referred to extensively throughout the logic.
    // $data->type can be one of: subject, item, capture_dataset, model

    // Insert into the job_log table
    $job_log_ids[] = $this->repo_storage_controller->execute('saveRecord', array(
      'base_table' => 'job_log',
      'user_id' => $data->user_id,
      'values' => array(
        'job_id' => $data->job_id,
        'job_log_status' => 'start',
        'job_log_label' => 'Import ' . $data->type,
        'job_log_description' => 'Import started',
      )
    ));

    // If data type is not a 'subject', set the array of $new_repository_ids.
    if ($data->type !== 'subject') {
      $new_repository_ids[$i] = $session->get('new_repository_ids_' . ($i-1));
    }

    foreach ($data->csv as $csv_key => $csv_val) {

      // If import_row_id or import_parent_id is missing, set the job to failed and exit.
      if (!isset($csv_val->import_row_id) || !isset($csv_val->import_parent_id)) {
        // Set the job status.
        $job_status = 'failed';
        // Log the error to the database.
        $this->repoValidate->logErrors(
          array(
            'job_id' => $data->job_id,
            'user_id' => 0,
            'job_log_label' => 'Metadata Ingest',
            'errors' => array($data->type . ' CSV is missing the import_row_id column'),
          )
        );
        // Update the 'job_status' in the 'job' table accordingly.
        $this->repo_storage_controller->execute('setJobStatus', 
          array(
            'job_id' => $data->uuid, 
            'status' => $job_status,
            'date_completed' => date('Y-m-d h:i:s')
          )
        );
        break;
      }

      if (isset($csv_val->import_row_id)) {
        // Set the parent record's repository ID.
        switch ($data->type) {
          case 'subject':
            // Set the project_repository_id
            $csv_val->project_repository_id = (int)$data->parent_project_id;
            break;
          case 'item':
            // Set the subject_repository_id.
            if (!empty($new_repository_ids[$i]) && !empty($csv_val->import_parent_id)) {
              $csv_val->subject_repository_id = $new_repository_ids[$i][$csv_val->import_parent_id];
            } else {
              $csv_val->subject_repository_id = $data->parent_record_id;
            }
            break;
          case 'capture_dataset':
            // Set the parent_item_repository_id.
            if (!empty($new_repository_ids[$i]) && !empty($csv_val->import_parent_id)) {
              $csv_val->parent_item_repository_id = $new_repository_ids[$i][$csv_val->import_parent_id];
            } else {
              $csv_val->parent_item_repository_id = $data->parent_record_id;
            }

            // Get the parent project ID.
            $parent_records = $this->repo_storage_controller->execute('getParentRecords', array(
              'base_record_id' => $csv_val->parent_item_repository_id,
              'record_type' => 'item',
            ));
            if (!empty($parent_records)) {
              $csv_val->parent_project_repository_id = $parent_records['project_repository_id'];
            }

          case 'model':
            // 1) Append the job ID to the file path
            // 2) Add the file's checksum to the $csv_val object.
            if(!empty($csv_val->file_path)) {
              // Append the job ID to the file path.
              $csv_val->file_path = '/' . $data->uuid . $csv_val->file_path;
              // Get the file's checksum from the BagIt manifest.
              $finder = new Finder();
              $finder->files()->in($this->uploads_directory . $data->uuid . '/');
              $finder->files()->name('manifest*.txt');
              // Find the manifest file.
              foreach ($finder as $file) {
                $manifest_contents = $file->getContents();
                $manifest_lines = preg_split('/\r\n|\n|\r/', trim($manifest_contents));
                foreach ($manifest_lines as $mkey => $mvalue) {
                  $manifest_line_array = preg_split('/\s+/', $mvalue);
                  // If there's a match against file paths, add the checksum to the $csv_val object.
                  if (strstr($csv_val->file_path, $manifest_line_array[1])) {
                    $csv_val->file_checksum = $manifest_line_array[0];
                    break;
                  }
                }
              }
            }
            // Set the parent_capture_dataset_repository_id or parent_item_repository_id (when a model is associated to an item).
            // TODO: add previous_parent_record_type to the mix, 
            // so the system will automatically detect what to associate a model to (to make it a bit more bullet-proof).
            if (!empty($new_repository_ids[$i]) && !empty($csv_val->import_parent_id)) {
              // If a model maps to an item, set the value for the 'parent_item_repository_id' field.
              if ($data->parent_record_type === 'item') {
                $csv_val->parent_item_repository_id = $new_repository_ids[$i][$csv_val->import_parent_id];
              }
              // Otherwise, set the value for the 'parent_capture_dataset_repository_id' field.
              else {
                $csv_val->parent_capture_dataset_repository_id = $new_repository_ids[$i][$csv_val->import_parent_id];
              }
            } else {
              // If a model maps to an item, set the value for the 'parent_item_repository_id' field.
              if ($data->parent_record_type === 'item') {
                $csv_val->parent_item_repository_id = $data->parent_record_id;
              }
              // Otherwise, set the value for the 'parent_capture_dataset_repository_id' field.
              else {
                $csv_val->parent_capture_dataset_repository_id = $data->parent_record_id;
              }
            }
            break;
        }

        // Insert data from the CSV into the appropriate database table, using the $data->type as the table name.
        $this_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => $data->type,
          'user_id' => $data->user_id,
          'values' => (array)$csv_val
        ));

        // Create an array of all of the newly created repository IDs.
        $new_new_repository_ids[$csv_val->import_row_id] = $this_id;

        // Set the description for the job log.
        switch ($data->type) {
          case 'subject':
            $data->description = $csv_val->local_subject_id . ' - ' . $csv_val->subject_name;
            break;
          case 'item':
            $data->description = $csv_val->item_description;
            break;
          case 'capture_dataset':
            $data->description = $data->for_model_description = $csv_val->capture_dataset_name;
            break;
          case 'model':
            $data->description = $project['project_name'] . ' - ' . $csv_val->model_file_type;
            break;
        }

        // Insert into the job_import_record table
        $job_import_record_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job_import_record',
          'user_id' => $data->user_id,
          'values' => array(
            'job_id' => $data->job_id,
            'record_id' => $this_id,
            'project_id' => (int)$data->parent_project_id,
            'record_table' => $data->type,
            'description' => $data->description,
          )
        ));
      }
    }
    if (isset($new_new_repository_ids) && !empty($new_new_repository_ids)) {
      // Set the session variable 'new_repository_ids'.
      $session->set('new_repository_ids_' . $i, $new_new_repository_ids);
    }

    // Job data.
    $job_data = $this->repo_storage_controller->execute('getJobData', array($data->uuid));

    if ($job_data['job_status'] !== 'failed') {
      // Insert into the job_log table
      // TODO: Feed the 'job_log_label' to the log leveraging fields from a form submission in the UI.
      $job_log_ids[] = $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => 'job_log',
        'user_id' => $data->user_id,
        'values' => array(
          'job_id' => $data->job_id,
          'job_log_status' => $job_status,
          'job_log_label' => 'Import ' . $data->type,
          'job_log_description' => 'Import ' . $job_status,
        )
      ));
    }

    // If the $job_data['job_status'] is failed, remove $job_log_ids.
    if ($job_data['job_status'] === 'failed') $job_log_ids = array();

    // TODO: return something more than job log IDs?
    return $job_log_ids;
  }

}