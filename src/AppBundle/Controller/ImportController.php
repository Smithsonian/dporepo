<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
// use Psr\Log\LoggerInterface;

use AppBundle\Form\UploadsParentPickerForm;
use AppBundle\Entity\UploadsParentPicker;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class ImportController extends Controller
{
    /**
     * @var object $u
     */
    public $u;

    /**
     * @var string $uploads_directory
     */
    private $uploads_directory;

    /**
     * @var string $uploads_path
     */
    private $uploads_path;

    private $repo_storage_controller;
    private $tokenStorage;


    /**
     * Constructor
     * @param object  $u  Utility functions object
     */
    public function __construct(AppUtilities $u, RepoStorageHybridController $repo_storage_controller, TokenStorageInterface $tokenStorage) // , LoggerInterface $logger
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = $repo_storage_controller;
        $this->tokenStorage = $tokenStorage;
        // $this->logger = $logger;
        // Usage:
        // $this->logger->info('Import started. Job ID: ' . $job_id);

        // TODO: move this to parameters.yml and bind in services.yml.
        $this->uploads_directory = __DIR__ . '/../../../web/uploads/repository/';
        $this->uploads_path = '/uploads/repository';
    }

    /**
     * @Route("/admin/import_csv/{job_id}/{parent_project_id}/{parent_record_id}/{parent_record_type}", name="import_csv", defaults={"job_id" = null, "parent_project_id" = null, "parent_record_id" = null, "parent_record_type" = null}, methods="GET")
     *
     * @param int $job_id Job ID
     * @param int $parent_project_id Project ID
     * @param int $parent_record_id Parent record ID
     * @param string $parent_record_type Parent record type
     * @param object $request Symfony's request object
     * @param object $validate ValidateMetadataController class
     * @param object $validate ItemsController class
     * @param object $validate DatasetsController class
     * @param object $validate ModelController class
     */
    public function import_csv($job_id, $parent_project_id, $parent_record_id, $parent_record_type, Request $request, ValidateMetadataController $validate, ItemsController $itemsController, DatasetsController $datasetsController, ModelController $modelsController)
    {
      // Clear session data.
      $session = new Session();
      $session->remove('new_repository_ids_1');
      $session->remove('new_repository_ids_2');
      $session->remove('new_repository_ids_3');
      $session->remove('new_repository_ids_4');

      $job_log_ids = $csv_types = array();

      $this->repo_storage_controller->setContainer($this->container);

      // Set the job type (e.g. subjects metadata import, items metadata import, capture datasets metadata import, models metadata import).
      $job_data = $this->repo_storage_controller->execute('getRecord', array(
          'base_table' => 'job',
          'id_field' => 'job_id',
          'id_value' => $job_id,
          'omit_active_field' => true,
        )
      );
      // Throw a 404 if the job record doesn't exist.
      if (!$job_data) throw $this->createNotFoundException('The Job record doesn\'t exist');

      if (!empty($job_id) && !empty($parent_project_id) && !empty($parent_record_id) && !empty($parent_record_type)) {

        $ids = (object)array(
          'job_id' => $job_id,
          'parent_project_id' => $parent_project_id,
          'parent_record_id' => $parent_record_id,
        );

        // Remove 'metadata import' from the $job_data['job_type'].
        $job_type = str_replace(' metadata import', '', $job_data['job_type']);

        if (!empty($job_type)) {
          // Prepare the data.
          $data = $this->prepare_data($job_type, $this->uploads_directory . $ids->job_id, $itemsController, $datasetsController, $modelsController);

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
                $job_log_ids = $this->ingest_csv_data($csv_value, $ids, $parent_record_type, $i);
              }

              $i++;
            }
          }
        }
      }

      // Update the job table to indicate that the CSV import failed.
      if (!empty($job_id) && empty($job_log_ids)) {
        $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job',
          'record_id' => $job_id,
          'user_id' => $this->getUser()->getId(),
          'values' => array(
            'job_status' => 'failed',
            'date_completed' => date('Y-m-d H:i:s'),
            'qa_required' => 0,
            'qa_approved_time' => null,
          )
        ));
      } else {
        // Update the job table to set the status from 'uploading' to 'in progress'.
        $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job',
          'record_id' => $job_id,
          'user_id' => $this->getUser()->getId(),
          'values' => array(
            'job_status' => 'in progress',
            'date_completed' => date('Y-m-d H:i:s'),
            'qa_required' => 0,
            'qa_approved_time' => null,
          )
        ));
      }

      $this->addFlash('message', '<strong>Upload Succeeded!</strong> Files will be validated shortly. The validation scheduled task runs every 30 seconds, but it may take time to grind through the validation process. Please check back!');

      return $this->json($job_log_ids);
    }

    /**
     * @param string $job_type The job type (One of: subjects, items, capture datasets, models)
     * @param string $job_upload_directory The upload directory
     * @return array Import result and/or any messages
     */
    public function prepare_data($job_type = null, $job_upload_directory = null, $itemsController, $datasetsController, $modelsController)
    {

      $data = array();

      if (!empty($job_upload_directory)) {

        $finder = new Finder();
        $finder->files()->in($job_upload_directory);

        // Prevent additional CSVs from being imported according to the $job_type.
        // Assign keys to each CSV, with projects first, subjects second, and items third.
        foreach ($finder as $file) {
          if (($job_type === 'subjects') && stristr($file->getRealPath(), 'subjects')) {
            $csv[0]['type'] = 'subject';
            $csv[0]['data'] = $file->getContents();
          }
          if ((($job_type === 'subjects') || ($job_type === 'items')) && stristr($file->getRealPath(), 'items')) {
            $csv[1]['type'] = 'item';
            $csv[1]['data'] = $file->getContents();
          }
          if ((($job_type === 'subjects') || ($job_type === 'items') || ($job_type === 'capture datasets') || ($job_type === 'models')) && stristr($file->getRealPath(), 'capture_datasets')) {
            $csv[2]['type'] = 'capture_dataset';
            $csv[2]['data'] = $file->getContents();
          }
          if ((($job_type === 'subjects') || ($job_type === 'items') || ($job_type === 'capture datasets') || ($job_type === 'models')) && stristr($file->getRealPath(), 'models')) {
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
                $json_array[$key][$field_name] = ($field_name === 'project_repository_id') ? (int)$id : null;

                // Set the value of the field name.
                $json_array[$key][$field_name] = $v;

                // ITEM LOOKUPS
                // Look-up the ID for the 'item_type'.
                if ($field_name === 'item_type') {
                  $item_type_lookup_options = $itemsController->get_item_types($this->container);
                  $json_array[$key][$field_name] = (int)$item_type_lookup_options[$v];
                }

                // CAPTURE DATASET LOOKUPS
                // Look-up the ID for the 'capture_method'.
                if ($field_name === 'capture_method') {
                  $capture_method_lookup_options = $datasetsController->get_capture_methods($this->container);
                  $json_array[$key][$field_name] = (int)$capture_method_lookup_options[$v];
                }

                // Look-up the ID for the 'capture_dataset_type'.
                if ($field_name === 'capture_dataset_type') {
                  $capture_dataset_type_lookup_options = $datasetsController->get_dataset_types($this->container);
                  $json_array[$key][$field_name] = (int)$capture_dataset_type_lookup_options[$v];
                }

                // Look-up the ID for the 'item_position_type'.
                if ($field_name === 'item_position_type') {
                  $item_position_type_lookup_options = $datasetsController->get_item_position_types($this->container);
                  $json_array[$key][$field_name] = (int)$item_position_type_lookup_options[$v];
                }

                // Look-up the ID for the 'focus_type'.
                if ($field_name === 'focus_type') {
                  $focus_type_lookup_options = $datasetsController->get_focus_types($this->container);
                  $json_array[$key][$field_name] = (int)$focus_type_lookup_options[$v];
                }

                // Look-up the ID for the 'light_source_type'.
                if ($field_name === 'light_source_type') {
                  $light_source_type_lookup_options = $datasetsController->get_light_source_types($this->container);
                  $json_array[$key][$field_name] = (int)$light_source_type_lookup_options[$v];
                }

                // Look-up the ID for the 'background_removal_method'.
                if ($field_name === 'background_removal_method') {
                  $background_removal_method_lookup_options = $datasetsController->get_background_removal_methods($this->container);
                  $json_array[$key][$field_name] = (int)$background_removal_method_lookup_options[$v];
                }

                // Look-up the ID for the 'cluster_type'.
                if ($field_name === 'cluster_type') {
                  $camera_cluster_types_lookup_options = $datasetsController->get_camera_cluster_types($this->container);
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
                  $model_modality_lookup_options = array('point cloud' => 1, 'mesh' => 2);
                  $json_array[$key][$field_name] = (int)$model_modality_lookup_options[$v];
                }

                // Look-up the ID for the 'units'.
                if ($field_name === 'units') {
                  $units_lookup_options = $modelsController->get_unit($this->container);
                  $json_array[$key][$field_name] = (int)$units_lookup_options[$v];
                }

                // Look-up the ID for the 'model_purpose'.
                if ($field_name === 'model_purpose') {
                  $model_purpose_lookup_options = array('master' => 1, 'delivery web' => 2, 'delivery print' => 3, 'intermediate processing step' => 4);
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
    $this->repo_storage_controller->setContainer($this->container);

    // User data.
    $user = $this->tokenStorage->getToken()->getUser();
    $data->user_id = $user->getId();
    // Job ID and parent record ID
    $data->job_id = isset($ids->job_id) ? $ids->job_id : false;
    $data->parent_project_id = isset($ids->parent_project_id) ? $ids->parent_project_id : false;
    $data->parent_record_id = isset($ids->parent_record_id) ? $ids->parent_record_id : false;
    $data->parent_record_type = isset($parent_record_type) ? $parent_record_type : false;

    // Just in case: throw a 404 if either job ID or parent record ID aren't passed.
    if (!$data->job_id) throw $this->createNotFoundException('Job ID not provided.');
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
        case 'model':
          // 1) Append the job ID to the file path
          // 2) Add the file's checksum to the $csv_val object.
          if(!empty($csv_val->file_path)) {
            // Append the job ID to the file path.
            $csv_val->file_path = '/' . $data->job_id . $csv_val->file_path;
            // Get the file's checksum from the BagIt manifest.
            $finder = new Finder();
            $finder->files()->in($this->uploads_directory . $data->job_id . '/');
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
          $data->description = $csv_val->local_subject_id . ' - ' . $csv_val->subject_display_name;
          break;
        case 'item':
          $data->description = $csv_val->item_display_name;
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

    // Set the session variable 'new_repository_ids'.
    $session->set('new_repository_ids_' . $i, $new_new_repository_ids);

    // Insert into the job_log table
    // TODO: Feed the 'job_log_label' to the log leveraging fields from a form submission in the UI.
    $job_log_ids[] = $this->repo_storage_controller->execute('saveRecord', array(
      'base_table' => 'job_log',
      'user_id' => $data->user_id,
      'values' => array(
        'job_id' => $data->job_id,
        'job_log_status' => 'finish',
        'job_log_label' => 'Import ' . $data->type,
        'job_log_description' => 'Import finished',
      )
    ));

    // TODO: return something more than job log IDs?
    return $job_log_ids;
  }

    /**
     * @Route("/admin/import", name="import_summary_dashboard", methods="GET")
     *
     * @param object $conn Database connection object
     * @param object $request Symfony's request object
     */
    public function import_summary_dashboard(Connection $conn, Request $request)
    {
        $obj = new UploadsParentPicker();

        // Create the parent record picker typeahead form.
        $form = $this->createForm(UploadsParentPickerForm::class, $obj);

        return $this->render('import/import_summary_dashboard.html.twig', array(
            'page_title' => 'Uploads',
            'form' => $form->createView(),
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));
    }

    /**
     * @Route("/admin/import/datatables_browse_imports", name="imports_browse_datatables", methods="POST")
     *
     * Browse Imports
     *
     * Run a query to retrieve all imports in the database.
     *
     * @param Request $request Symfony's request object
     * @return \Symfony\Component\HttpFoundation\JsonResponse The query result
     */
    public function datatables_browse_imports(Request $request)
    {
      $req = $request->request->all();
      $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
      $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
      $sort_order = $req['order'][0]['dir'];
      $start_record = !empty($req['start']) ? $req['start'] : 0;
      $stop_record = !empty($req['length']) ? $req['length'] : 20;

      $query_params = array(
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'start_record' => $start_record,
        'stop_record' => $stop_record,
      );
      if ($search) {
        $query_params['search_value'] = $search;
      }

      $this->repo_storage_controller->setContainer($this->container);
      $data = $this->repo_storage_controller->execute('getDatatableImports', $query_params);

      return $this->json($data);
    }
    
    /**
     * @Route("/admin/import/{id}/{project_id}", name="import_summary_details", methods="GET")
     *
     * @param int $id Project ID
     * @param object $conn Database connection object
     * @param object $project ProjectsController class
     * @param object $request Symfony's request object
     */
    public function import_summary_details($id, $project_id, Connection $conn, ProjectsController $project, Request $request, DatasetElementsController $data_elements_controller)
    {

      $project = [];
      $project['file_validation_errors'] = [];
      $this->repo_storage_controller->setContainer($this->container);

      if (!empty($id)) {
        // Check to see if the job exists. If it doesn't, throw a createNotFoundException (404).
        $job_data = $this->repo_storage_controller->execute('getRecord', array(
            'base_table' => 'job',
            'id_field' => 'job_id',
            'id_value' => $id,
            'omit_active_field' => true,
          )
        );
        if (empty($job_data)) throw $this->createNotFoundException('The Job record does not exist');
      }

      if (!empty($project_id)) {
        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $project_id));
        if (!$project) throw $this->createNotFoundException('The Project record does not exist');
      }

      // Get the total number of Item records for the import.
      if (!empty($id)) {
        // Get job import data (query the 'job_import_record' table).
        $job_record_data = $this->repo_storage_controller->execute('getImportedItems', array('job_id' => (int)$id));
        // If a record is found within the 'job_import_record' table, fetch the remaining data (uploaded files, file validation errors).
        if ($job_record_data) {

          // Merge job_record_data into $project.
          $project = array_merge($project, $job_record_data);

          // Check for uploaded files.
          $dir = $this->uploads_directory . (int)$id . '/';
          $project['uploaded_files'] = (is_dir($dir) && is_readable($dir)) ? true : false;

          // Get errors if they exist.
          $project['file_validation_errors'] = $this->repo_storage_controller->execute('getRecords', array(
              'base_table' => 'job_log',
              'fields' => array(),
              'search_params' => array(
                0 => array(
                  'field_names' => array(
                    'job_id'
                  ),
                  'search_values' => array(
                    (int)$id
                  ),
                  'comparison' => '='
                ),
                1 => array(
                  'field_names' => array(
                    'job_log_status'
                  ),
                  'search_values' => array(
                    'error'
                  ),
                  'comparison' => '='
                ),
                2 => array(
                  'field_names' => array(
                    'job_log_label'
                  ),
                  'search_values' => array(
                    'BagIt Validation'
                  ),
                  'comparison' => '='
                )
              ),
              'search_type' => 'AND',
              'sort_fields' => array(
                0 => array('field_name' => 'date_created')
              ),
              'omit_active_field' => true,
            )
          );
        }

      }

      return $this->render('import/import_summary_item.html.twig', array(
        'page_title' => $job_record_data ? $project['job_label'] : 'Uploads: ' . $project['project_name'],
        'project' => $project,
        'job_data' => $job_data,
        'id' => $id,
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
      ));
    }

    /**
     * @Route("/admin/import/{id}/datatables_browse_import_details", name="import_details_browse_datatables", methods="POST")
     *
     * Browse Import Details
     *
     * Run a query to retrieve the details of an import.
     *
     * @param  int $id The job ID
     * @param Request $request Symfony's request object
     * @return \Symfony\Component\HttpFoundation\JsonResponse The query result
     */
    public function datatables_browse_import_details($id, Request $request)
    {
      $req = $request->request->all();
      $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
      $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
      $sort_order = $req['order'][0]['dir'];
      $start_record = !empty($req['start']) ? $req['start'] : 0;
      $stop_record = !empty($req['length']) ? $req['length'] : 20;

      $this->repo_storage_controller->setContainer($this->container);

      // Determine what was ingested (e.g. subjects, items, capture datasets, models).
      $job_data = $this->repo_storage_controller->execute('getRecord', array(
          'base_table' => 'job',
          'id_field' => 'job_id',
          'id_value' => $id,
          'omit_active_field' => true,
        )
      );

      // TODO: ^^^ error handling if job is not found? ^^^

      $query_params = array(
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'start_record' => $start_record,
        'stop_record' => $stop_record,
        'id' => $id,
        'job_type' => $job_data['job_type'],
      );

      if ($search) {
        $query_params['search_value'] = $search;
      }

      $data = $this->repo_storage_controller->execute('getDatatableImportDetails', $query_params);

      return $this->json($data);
    }

    /**
     * Create an array of all direcories and files found.
     *
     * @param int $job_id The Job ID.
     * @return array $data An array of all files found for a job.
     */
    private function get_directory_contents($job_id = null) {

      $data = [];

      if (!empty($job_id) && is_dir($this->uploads_directory . $job_id . '/')) {
        $finder = new Finder();
        $finder->files()->in($this->uploads_directory . $job_id . '/');

        foreach ($finder as $file) {
          $this_file = str_replace($this->uploads_directory . $job_id, '', $file->getPathname());
          // The following rigmarole is due to slash differences between Windows and Unix-based systems.
          $this_file = ltrim($this_file, DIRECTORY_SEPARATOR);
          // The simplified path to the file (minus absolute path structures).
          $data[] = str_replace('\\' . $file->getPathname(), '', $this_file);
        }
      }

      return $data;
    }

    /**
     * @Route("/admin/import/get_parent_records", name="get_parent_records", methods="POST")
     *
     * @param Request $request Symfony's request object
     * @return \Symfony\Component\HttpFoundation\JsonResponse The query result
     */
    public function get_parent_records(Request $request)
    {
      $data = $params = array();

      $req = $request->request->all();
      $params['query'] = !empty($req['query']) ? $req['query'] : false;
      $params['limit'] = !empty($req['limit']) ? $req['limit'] : false;
      $params['render'] = !empty($req['render']) ? $req['render'] : false;
      $params['property'] = !empty($req['property']) ? $req['property'] : false;

      $record_types = array(
        'project',
        'subject',
        'item',
        'capture_dataset',
      );

      foreach ($record_types as $key => $value) {

        $params['record_type'] = $value;

        switch($value) {
          case 'subject':
            $params['field_name'] = 'subject_display_name';
            $params['id_field_name'] = 'subject_repository_id';
            break;
          case 'item':
            $params['field_name'] = 'item_display_name';
            $params['id_field_name'] = 'item_repository_id';
            break;
          case 'capture_dataset':
            $params['field_name'] = 'capture_dataset_name';
            $params['id_field_name'] = 'capture_dataset_repository_id';
            break;
          default: // project
            $params['field_name'] = 'project_name';
            $params['id_field_name'] = 'project_repository_id';
        }

        $this->repo_storage_controller->setContainer($this->container);

        // Query the database.
        $results = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => $params['record_type'],
          'fields' => array(),
          'limit' => (int)$params['limit'],
          'search_params' => array(
            // Lots of variables going on. Here's an example of what it looks like without variables:
            // 0 => array('field_names' => array('project.active'), 'search_values' => array(1), 'comparison' => '='),
            // 1 => array('field_names' => array('project.project_name'), 'search_values' => $params['query'], 'comparison' => 'LIKE')
            0 => array('field_names' => array($params['record_type'] . '.active'), 'search_values' => array(1), 'comparison' => '='),
            1 => array('field_names' => array($params['record_type'] . '.' . $params['field_name']), 'search_values' => $params['query'], 'comparison' => 'LIKE')
          ),
          'search_type' => 'AND',
          )
        );

        // Format the $data array for the typeahead-bundle.
        if (!empty($results)) {
          foreach ($results as $key => $value) {
            $data[] = array('id' => $value[ $params['id_field_name'] ], 'value' => $value[ $params['field_name'] ] . ' [ ' . strtoupper(str_replace('_', ' ', $params['record_type'])) . ' ]');
          }
        }
      }

      // Return data as JSON
      return $this->json($data);
    }

    
    /**
     * @param string $parent_record_type The record type (e.g. subject)
     * @return string
     */
    public function get_job_type($parent_record_type = null)
    {

      switch ($parent_record_type) {
        case 'project':
          $data = 'subjects';
          break;

        case 'subject':
          $data = 'items';
          break;

        case 'item':
          $data = 'capture datasets';
          break;

        case 'capture_dataset':
          $data = 'models';
          break;
        
        default:
          $data = null;
          break;
      }

      return $data;
    }

    /**
     * @Route("/admin/create_job/{base_record_id}/{record_type}", name="create_job", defaults={"base_record_id" = null, "record_type" = null}, methods="GET")
     *
     * @param int $project_id The project ID
     * @param string $record_type The record type (e.g. subject)
     * @return JSON
     */
    public function create_job($base_record_id, $record_type, Request $request)
    {
      $job_id = null;
      $parent_records = [];
      $this->repo_storage_controller->setContainer($this->container);

      // Get the parent Project's record ID (unless it's a project to begin with).
      if (!empty($base_record_id) && !empty($record_type) && ($record_type !== 'project')) {
        $parent_records = $this->repo_storage_controller->execute('getParentRecords', array(
          'base_record_id' => $base_record_id,
          'record_type' => $record_type,
        ));
      } else {
        // If the $record_type is a 'project', just use the $base_record_id, since that's the project ID.
        $parent_records['project_repository_id'] = $base_record_id;
      }

      // If there are no results for a parent Project record ID, throw a createNotFoundException (404).
      if (empty($parent_records)) throw $this->createNotFoundException('Could not establish the parent project ID');

      if (!empty($parent_records) && isset($parent_records['project_repository_id'])) {
        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $parent_records['project_repository_id']));
        if (!$project) throw $this->createNotFoundException('The Project record does not exist');
      }

      if (!empty($project)) {
        // Get the job type (what's being ingested?).
        $job_type = $this->get_job_type($record_type);
        // Insert a record into the job table.
        // TODO: Feed the 'job_label' and 'job_type' to the log leveraging fields from a form submission in the UI?
        $job_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job',
          'user_id' => $this->getUser()->getId(),
          'values' => array(
            'project_id' => (int)$project['project_repository_id'],
            'job_label' => 'Metadata Import: "' . $project['project_name'] . '"',
            'job_type' => $job_type . ' metadata import',
            'job_status' => 'uploading',
            'date_completed' => null,
            'qa_required' => 0,
            'qa_approved_time' => null,
          )
        ));
      }

      return $this->json(array('jobId' => (int)$job_id, 'projectId' => (int)$project['project_repository_id']));
    }

    /**
     * @Route("/admin/purge_import/{job_id}", name="purge_imported_data_and_files", defaults={"job_id" = null}, methods="GET")
     *
     * @param int $job_id The Job ID
     * @param object $conn Database connection object
     * @param object $request Symfony's request object
     * @return array
     */
    public function purge_imported_data_and_files($job_id, Connection $conn, Request $request)
    {
      if (empty($job_id)) throw $this->createNotFoundException('Job ID not provided');

      if (!empty($job_id)) {

        // Set container.
        $this->repo_storage_controller->setContainer($this->container);

        // Check to see if the job record exists, and if it doesn't, throw a createNotFoundException (404).
        $job_data = $this->repo_storage_controller->execute('getRecord', array(
            'base_table' => 'job',
            'id_field' => 'job_id',
            'id_value' => $job_id,
            'omit_active_field' => true,
          )
        );
        if (!$job_data) throw $this->createNotFoundException('The Job record does not exist');

        // Remove imported data.
        $results = $this->repo_storage_controller->execute('purgeImportedData', array('job_id' => (int)$job_id));
        // Create a summary of rows deleted.
        $data = '';
        foreach ($results as $key => $value) {
          $data .= '<p><strong>Table:</strong> ' . $key . '&nbsp;&nbsp;&nbsp;<strong>Rows Deleted:</strong> ' . $value . '</p>';
        }

        // Remove the job directory.
        if (is_dir($this->uploads_directory . DIRECTORY_SEPARATOR . $job_id)) {
          $fileSystem = new Filesystem();
          $fileSystem->remove($this->uploads_directory . DIRECTORY_SEPARATOR . $job_id);
        }

        // The message
        $this->addFlash('message', '<h4>Job data and files have been successfully removed</h4>' . $data);
        // Redirect to the main Uploads page.
        return $this->redirect('/admin/import');
      }
    }
}
