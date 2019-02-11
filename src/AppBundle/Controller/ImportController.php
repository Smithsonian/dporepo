<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
// For running a console command from a controller.
// See: https://symfony.com/doc/3.4/console/command_in_controller.html
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
// use Symfony\Component\Console\Output\BufferedOutput;

use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;

// use Psr\Log\LoggerInterface;

use AppBundle\Form\UploadsParentPickerForm;
use AppBundle\Entity\UploadsParentPicker;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

use AppBundle\Service\RepoFileTransfer;
use AppBundle\Service\RepoProcessingService;

use AppBundle\Form\SubjectForm;
use AppBundle\Entity\Subject;

use AppBundle\Form\ItemForm;
use AppBundle\Entity\Item;
use AppBundle\Controller\ItemController;

use AppBundle\Form\CaptureDatasetForm;
use AppBundle\Entity\CaptureDataset;
use AppBundle\Controller\CaptureDatasetController;

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
    private $datasetsController;
    private $itemsController;
    private $fileTransfer;

    /**
     * @var object $processing
     */
    private $processing;

    /**
     * @var string $external_file_storage_on
     */
    private $external_file_storage_on;

    /**
     * Constructor
     * @param object  $u  Utility functions object
     */
    public function __construct(AppUtilities $u, Connection $conn, TokenStorageInterface $tokenStorage, CaptureDatasetController $datasetsController, ItemController $itemsController, RepoFileTransfer $fileTransfer, RepoProcessingService $processing, bool $external_file_storage_on) // , LoggerInterface $logger
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->tokenStorage = $tokenStorage;

        $this->datasetsController = $datasetsController;
        $this->itemsController = $itemsController;
        $this->fileTransfer = $fileTransfer;
        $this->processing = $processing;
        $this->external_file_storage_on = $external_file_storage_on;

        // $this->logger = $logger;
        // Usage:
        // $this->logger->info('Import started. Job ID: ' . $job_id);

        // TODO: move this to parameters.yml and bind in services.yml.
        $this->uploads_directory = __DIR__ . '/../../../web/uploads/repository/';
        $this->uploads_path = '/uploads/repository';
    }

    /**
     * @Route("/admin/ingest", name="import_summary_dashboard", methods="GET")
     *
     * Browse Ingests
     *
     * @param object $conn Database connection object
     * @param object $request Symfony's request object
     */
    public function importSummaryDashboard(Connection $conn, Request $request)
    {
      return $this->render('import/import_summary_dashboard.html.twig', array(
        'page_title' => 'Browse Ingests',
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        'current_tab' => 'ingest'
      ));
    }

    /**
     * @Route("/admin/ingest/datatables_browse_imports", name="imports_browse_datatables", methods="POST")
     *
     * Browse Ingests Datatable
     *
     * Run a query to retrieve all imports in the database.
     *
     * @param Request $request Symfony's request object
     * @return \Symfony\Component\HttpFoundation\JsonResponse The query result
     */
    public function datatablesBrowseImports(Request $request)
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

      $data = $this->repo_storage_controller->execute('getDatatableImports', $query_params);

      foreach ($data['aaData'] as $key => $value) {
        switch ($value['job_status']) {
          case 'cancelled':
          case 'failed':
            $data['aaData'][$key]['job_status'] = '<span class="text-danger"><span class="glyphicon glyphicon-exclamation-sign"></span> ' . $value['job_status'] . '</span>';
            break;
          case 'complete':
            $data['aaData'][$key]['job_status'] = '<span class="text-success"><span class="glyphicon glyphicon-ok"></span> ' . $value['job_status'] . '</span>';
            break;
          default:
            $data['aaData'][$key]['job_status'] = '<span class="text-info"><span class="glyphicon glyphicon-time"></span> ' . $value['job_status'] . '</span>';
        }
      }

      return $this->json($data);
    }

    /**
     * @Route("/admin/simple_ingest", name="simple_ingest", methods="GET")
     *
     * Simple Ingest
     *
     * @param object $conn Database connection object
     * @param object $request Symfony's request object
     */
    public function simpleIngest(Connection $conn, Request $request)
    {
        // Patch vendor overrides.
        $this->u->patchVendorOverrides();

        $service_error = false;
        $obj = new UploadsParentPicker();

        // If the external file storage service is turned on in parameters.yml,
        // check to see if the external storage service is accessible.
        if($this->external_file_storage_on) {
          // TODO: Send email alerts to admins?
          // Set up flysystem.
          $flysystem = $this->container->get('oneup_flysystem.assets_filesystem');
          // Transfer files.
          $result = $this->fileTransfer->checkExternalStorage('checker', $flysystem);
          // If errors exist, serve out flash notifications.
          if (!empty($result)) {
            foreach ($result as $key => $value) {
              if (isset($value['errors'])) {
                $this->addFlash('error', '<strong>Ingest Service Down</strong>. The interface has been disabled (see below for details).');
                $service_error = true;
                foreach ($value['errors'] as $ekey => $evalue) {
                  $this->addFlash('error', $evalue);
                }
              }
            }
          }
        }

        // Create the subject form
        $subject = new Subject();
        $subject->access_model_purpose = NULL;
        $subject->inherit_publication_default = '';
        $subject->api_publication_options = array(
          'Published, Discoverable' => '11',
          'Published, Not Discoverable' => '10',
          'Not Published' => '00',
        );
        // Get values for options.
        $model_purpose_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_purpose',
          'value_field' => 'model_purpose_description',
          'id_field' => 'model_purpose_id',
          ));
        $model_face_count_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_face_count',
          'value_field' => 'model_face_count',
          'id_field' => 'model_face_count_id',
        ));
        $uv_map_size_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'uv_map_size',
          'value_field' => 'uv_map_size',
          'id_field' => 'uv_map_size_id',
        ));
        $subject->model_face_count_options = $model_face_count_options;
        $subject->uv_map_size_options = $uv_map_size_options;
        $subject->model_purpose_options = $model_purpose_options;

        $subject = (array)$subject;
        
        $subject_form = $this->createForm(SubjectForm::class, $subject);

        // Create the item form
        $item = new Item();
        $item->access_model_purpose = NULL;
        $item->inherit_publication_default = '';

        // Get data from lookup tables.
        $item->item_type_lookup_options = $this->itemsController->getItemTypes();
        $item->subject_lookup_options = $this->itemsController->getSubjects();
        $item->api_publication_options = array(
          'Published, Discoverable' => '11',
          'Published, Not Discoverable' => '10',
          'Not Published' => '00',
        );
        $model_purpose_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_purpose',
          'value_field' => 'model_purpose_description',
          'id_field' => 'model_purpose_id',
        ));
        $model_face_count_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_face_count',
          'value_field' => 'model_face_count',
          'id_field' => 'model_face_count_id',
        ));
        $uv_map_size_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'uv_map_size',
          'value_field' => 'uv_map_size',
          'id_field' => 'uv_map_size_id',
        ));

        $item->model_face_count_options = $model_face_count_options;
        $item->uv_map_size_options = $uv_map_size_options;
        $item->model_purpose_options = $model_purpose_options;

        $item_form = $this->createForm(ItemForm::class, $item);

        // Create the dataset form.
        $dataset = new CaptureDataset();
        $dataset->access_model_purpose = NULL;
        $dataset->inherit_publication_default = '';

        // Get data from lookup tables.
        $dataset->capture_methods_lookup_options = $this->datasetsController->getCaptureMethods();
        $dataset->dataset_types_lookup_options = $this->datasetsController->getDatasetTypes();
        $dataset->item_position_types_lookup_options = $this->datasetsController->getItemPositionTypes();
        $dataset->focus_types_lookup_options = $this->datasetsController->getFocusTypes();
        $dataset->light_source_types_lookup_options = $this->datasetsController->getLightSourceTypes();
        $dataset->background_removal_methods_lookup_options = $this->datasetsController->getBackgroundRemovalMethods();
        $dataset->camera_cluster_types_lookup_options = $this->datasetsController->getCameraClusterTypes();
        $dataset->calibration_object_type_options = $this->datasetsController->getCalibrationObjectTypes();

        $dataset->api_publication_options = array(
          'Published, Discoverable' => '11',
          'Published, Not Discoverable' => '10',
          'Not Published' => '00',
        );
        $model_purpose_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_purpose',
          'value_field' => 'model_purpose_description',
          'id_field' => 'model_purpose_id',
        ));
        $model_face_count_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'model_face_count',
          'value_field' => 'model_face_count',
          'id_field' => 'model_face_count_id',
        ));
        $uv_map_size_options = $this->repo_storage_controller->execute('getDataForLookup', array(
          'table_name' => 'uv_map_size',
          'value_field' => 'uv_map_size',
          'id_field' => 'uv_map_size_id',
        ));

        $dataset->model_face_count_options = $model_face_count_options;
        $dataset->uv_map_size_options = $uv_map_size_options;
        $dataset->model_purpose_options = $model_purpose_options;

        // Create the form
        $form = $this->createForm(CaptureDatasetForm::class, $dataset);

        $accepted_file_types = '.csv, .txt, .jpg, .tif, .png, .dng, .obj, .ply, .mtl, .zip, .cr2';

        return $this->render('import/simple_ingest.html.twig', array(
          'page_title' => 'Simple Ingest',
          'form' => $form->createView(),
          'subject_form' => $subject_form->createView(),
          'item_form' => $item_form->createView(),
          'accepted_file_types' => $accepted_file_types,
          'service_error' => $service_error,
          'dataset_data' => $dataset,
          'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
          'current_tab' => 'ingest'
        ));
    }

    /**
     * @Route("/admin/bulk_ingest", name="bulk_ingest", methods="GET")
     *
     * Bulk Ingest
     *
     * @param object $conn Database connection object
     * @param object $request Symfony's request object
     */
    public function bulkIngest(Connection $conn, Request $request)
    {
        // Patch vendor overrides.
        $this->u->patchVendorOverrides();

        $service_error = false;
        $obj = new UploadsParentPicker();

        // If the external file storage service is turned on in parameters.yml,
        // check to see if the external storage service is accessible.
        if($this->external_file_storage_on) {
          // TODO: Send email alerts to admins?
          // Set up flysystem.
          $flysystem = $this->container->get('oneup_flysystem.assets_filesystem');
          // Transfer files.
          $result = $this->fileTransfer->checkExternalStorage('checker', $flysystem);
          // If errors exist, serve out flash notifications.
          if (!empty($result)) {
            foreach ($result as $key => $value) {
              if (isset($value['errors'])) {
                $this->addFlash('error', '<strong>Ingest Service Down</strong>. The interface has been disabled (see below for details).');
                $service_error = true;
                foreach ($value['errors'] as $ekey => $evalue) {
                  $this->addFlash('error', $evalue);
                }
              }
            }
          }
        }

        // Create the parent record picker typeahead form.
        $form = $this->createForm(UploadsParentPickerForm::class, $obj);
        $accepted_file_types = '.csv, .txt, .jpg, .tif, .png, .dng, .obj, .ply, .mtl, .zip, .cr2';

        return $this->render('import/bulk_ingest.html.twig', array(
          'page_title' => 'Bulk Ingest',
          'form' => $form->createView(),
          'accepted_file_types' => $accepted_file_types,
          'service_error' => $service_error,
          'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
          'current_tab' => 'ingest'
        ));
    }

    /**
     * @Route("/admin/execute_jobs/{uuid}/{project_id}/{record_id}/{record_type}", name="execute_jobs", defaults={"uuid" = null, "project_id" = null, "record_id" = null, "record_type" = null}, methods="GET")
     *
     * @param int $uuid Job ID
     * @param int $project_id Parent Project ID
     * @param int $record_id Parent Record ID
     * @param int $record_type Parent Record Type
     * @param object $kernel KernelInterface class
     */
    public function executeJobs($uuid, $project_id, $record_id, $record_type, KernelInterface $kernel) {

      $input = array(
        'uuid' => $uuid,
        'project_id' => $project_id,
        'record_id' => $record_id,
        'record_type' => $record_type,
      );

      // Hack for XAMPP on Windows.
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $php_binary_path = 'c:/xampp/php/php.exe';
      } else {
        // Find the executable PHP binary.
        $php_binary_finder = new PhpExecutableFinder();
        $php_binary_path = $php_binary_finder->find();
      }

      // $command = 'cd ' . $this->container->getParameter('kernel.project_dir') . ' && ';
      chdir($this->container->getParameter('kernel.project_dir'));
      if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {            
        $command = $php_binary_path . ' bin/console app:validate-assets ' . implode(' ', $input) . ' > NUL';
      } else {
        $command = $php_binary_path . ' bin/console app:validate-assets ' . implode(' ', $input) . ' > /dev/null 2>&1 &';
      }

      // $this->u->dumper($php_binary_path,0);
      // $this->u->dumper($command);

      // $this->u->dumper($this->container->getParameter('kernel.project_dir'));

      // $process = new Process($command);
      // $process->setTimeout(3600);
      // $process->start();

      $process = new Process($command);
      $process->setTimeout(3600);

      // $process->run(function ($type, $buffer) {
      //     if (Process::ERR === $type) {
      //         echo 'ERR > ' . $buffer;
      //     } else {
      //         echo 'OUT > ' . $buffer;
      //     }
      // });

      $process->start();
      foreach ($process as $type => $data) {
          if ($process::OUT === $type) {
              echo "\nRead from stdout: ".$data;
          } else { // $process::ERR === $type
              echo "\nRead from stderr: ".$data;
          }
      }

      $process->wait();

      $input['pid'] = $process->getPid();

      // $this->u->dumper($pid);

      // new NullOutput();
      // try {
      //   $process->mustRun();
      // } catch (ProcessFailedException $exception) {
      //   $this->addFlash('error', '<strong>Error:</strong> ' . $exception->getMessage());
      // }

      // // Use NullOutput() if you don't need the output
      // new NullOutput();
      // $application->run($input);

      // // You can use NullOutput() if you don't need the output
      // $output = new BufferedOutput();
      // $application->run($input, $output);

      // // return the output, don't use if you used NullOutput()
      // $content = $output->fetch();

      // // return new Response(""), if you used NullOutput()
      // $res = new Response($content);

      return $this->json($input);

    }
    
    /**
     * @Route("/admin/ingest/{uuid}/{project_id}/{record_id}/{record_type}", name="import_summary_details", defaults={"uuid" = null, "project_id" = null, "record_id" = null, "record_type" = null}, methods="GET")
     *
     * @param int $uuid Job ID
     * @param int $project_id Parent Project ID
     * @param int $record_id Parent Record ID
     * @param int $record_type Parent Record Type
     * @param object $conn Database connection object
     * @param object $project ProjectsController class
     * @param object $request Symfony's request object
     */
    public function importSummaryDetails($uuid, $project_id, $record_id, $record_type, Connection $conn, Request $request)
    {

      // $this->u->dumper($uuid,0);
      // $this->u->dumper($project_id,0);
      // $this->u->dumper($record_id,0);
      // $this->u->dumper($record_type);

      $project = [];

      if (!empty($uuid)) {
        // Check to see if the job exists. If it doesn't, throw a createNotFoundException (404).
        $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid));
        if (empty($job_data)) throw $this->createNotFoundException('The Job record does not exist');
      }

      if (!empty($project_id)) {
        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $project = $this->repo_storage_controller->execute('getProject', array('project_id' => $project_id));
        if (!$project) throw $this->createNotFoundException('The Project record does not exist');
      }

      $project['file_validation_errors'] = [];

      // Get the total number of Item records for the import.
      if (!empty($uuid)) {

        // Get job import data (query the 'job_import_record' table).
        $job_record_data = $this->repo_storage_controller->execute('getImportedItems', array('job_id' => $job_data['job_id']));

        // If a record is NOT found within the 'job_import_record' table, add a message and execute validations and metadata ingests.
        if (!$job_record_data && ($job_data['job_status'] !== 'cancelled') && ($job_data['job_status'] !== 'failed') && ($job_data['job_status'] !== 'complete')) {

          $this->addFlash('message', 'Files have been successfully uploaded. Validations and metadata ingests are currently in progress.');

          // Set the parameters for the app:validate-assets command (passed to client side, then executed asynchronously via the /admin/execute_jobs route)
          $project['execute_jobs_input'] = array(
            'uuid' => $job_data['uuid'],
            'project_id' => $project_id,
            'record_id' => $record_id,
            'record_type' => $record_type,
          );
          
        }

        // If a query result is produced against the 'job_import_record' table, add to the $project array.
        // Example query result:
        // {
        //     "subjects_total": "5",
        //     "items_total": "8",
        //     "capture_datasets_total": "3",
        //     "models_total": "2",
        //     "record_table": "subject",
        //     "job_label": "Metadata Import: \"Space Shuttle Discovery\"",
        //     "date_created": "2018-07-25 18:03:36",
        //     "date_completed": "2018-07-25 18:03:40",
        //     "job_status": "complete",
        //     "username": "ghalusa"
        // }
        if ($job_record_data) {
          // Merge job_record_data into $project.
          $project = array_merge($project, $job_record_data);
        }

        // Check for uploaded files.
        $dir = $this->uploads_directory . $job_data['uuid'] . '/';
        $project['uploaded_files'] = (is_dir($dir) && is_readable($dir)) ? true : false;

        // Get CSV data.
        if ($project['uploaded_files']) {
          $project['csv'] = array();
          $project['csv_row_count'] = array();
          $finder = new Finder();
          $finder->files()->name('*.csv');
          foreach ($finder->in($dir) as $file) {
            $project['csv'][$file->getBasename()] = $this->constructImportData($dir, $file->getBasename());
            $project['csv_row_count'][$file->getBasename()] = count($project['csv'][$file->getBasename()]);
          }
          // If there's csv data, encode to JSON so it can be passed on to Handsontables (JavaScript).
          if(isset($project['csv'])) {
            $project['csv'] = json_encode($project['csv'], JSON_HEX_APOS);
            $project['csv_row_count'] = json_encode($project['csv_row_count']);
          }
        }

        // Get bagit validation errors if they exist.
        $project['bagit_validation_errors'] = $this->repo_storage_controller->execute('getRecords', array(
            'base_table' => 'job_log',
            'fields' => array(),
            'search_params' => array(
              array(
                'field_names' => array(
                  'job_id'
                ),
                'search_values' => array(
                  $job_data['job_id']
                ),
                'comparison' => '='
              ),
              array(
                'field_names' => array(
                  'job_log_status'
                ),
                'search_values' => array(
                  'error'
                ),
                'comparison' => '='
              ),
              array(
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

        // Get asset validation errors if they exist.
        $project['asset_validation_errors'] = $this->repo_storage_controller->execute('getRecords', array(
            'base_table' => 'job_log',
            'fields' => array(),
            'search_params' => array(
              array(
                'field_names' => array(
                  'job_id'
                ),
                'search_values' => array(
                  $job_data['job_id']
                ),
                'comparison' => '='
              ),
              array(
                'field_names' => array(
                  'job_log_status'
                ),
                'search_values' => array(
                  'error'
                ),
                'comparison' => '='
              ),
              array(
                'field_names' => array(
                  'job_log_label'
                ),
                'search_values' => array(
                  'Asset Validation'
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

        // Get metadata ingest errors if they exist.
        $project['metadata_ingest_errors'] = $this->repo_storage_controller->execute('getRecords', array(
            'base_table' => 'job_log',
            'fields' => array(),
            'search_params' => array(
              array(
                'field_names' => array(
                  'job_id'
                ),
                'search_values' => array(
                  $job_data['job_id']
                ),
                'comparison' => '='
              ),
              array(
                'field_names' => array(
                  'job_log_status'
                ),
                'search_values' => array(
                  'error'
                ),
                'comparison' => '='
              ),
              array(
                'field_names' => array(
                  'job_log_label'
                ),
                'search_values' => array(
                  'Metadata Ingest'
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

        // Get file transfer errors if they exist.
        $project['file_transfer_errors'] = $this->repo_storage_controller->execute('getRecords', array(
            'base_table' => 'job_log',
            'fields' => array(),
            'search_params' => array(
              array(
                'field_names' => array(
                  'job_id'
                ),
                'search_values' => array(
                  $job_data['job_id']
                ),
                'comparison' => '='
              ),
              array(
                'field_names' => array(
                  'job_log_status'
                ),
                'search_values' => array(
                  'error'
                ),
                'comparison' => '='
              ),
              array(
                'field_names' => array(
                  'job_log_label'
                ),
                'search_values' => array(
                  'File Transfer'
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

        $query_params = array('ingest_job_uuid' => $uuid);
        $workflow_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);
        $job_data['workflow'] = $workflow_data;

      }

      return $this->render('import/import_summary_item.html.twig', array(
        'page_title' => $job_record_data ? $project['job_label'] : 'Uploads: ' . $project['project_name'],
        'project' => $project,
        'job_data' => $job_data,
        'id' => $job_data['job_id'],
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        'current_tab' => 'ingest'
      ));
    }

    /**
     * @Route("/admin/ingest/{uuid}/datatables_browse_import_details", name="import_details_browse_datatables", methods="POST")
     *
     * Browse Import Details
     *
     * Run a query to retrieve the details of an import.
     *
     * @param  int $uuid The job's UUID
     * @param Request $request Symfony's request object
     * @return \Symfony\Component\HttpFoundation\JsonResponse The query result
     */
    public function datatablesBrowseImportDetails($uuid, Request $request)
    {
      $req = $request->request->all();
      $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
      $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
      $sort_order = $req['order'][0]['dir'];
      $start_record = !empty($req['start']) ? $req['start'] : 0;
      $stop_record = !empty($req['length']) ? $req['length'] : 20;

      // Determine what was ingested (e.g. subjects, items, capture datasets, models).
      $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid));

      // If there are no results, throw a createNotFoundException (404).
      if (empty($job_data)) throw $this->createNotFoundException('No records found');

      // TODO: ^^^ error handling if job is not found? ^^^

      $query_params = array(
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'start_record' => $start_record,
        'stop_record' => $stop_record,
        'id' => $job_data['job_id'],
        'job_type' => $job_data['job_type'],
      );

      if ($search) {
        $query_params['search_value'] = $search;
      }

      $data = $this->repo_storage_controller->execute('getDatatableImportDetails', $query_params);

      return $this->json($data);
    }

    /**
     * @Route("/admin/ingest/get_parent_records", name="get_parent_records", methods="POST")
     *
     * @param Request $request Symfony's request object
     * @return \Symfony\Component\HttpFoundation\JsonResponse The query result
     */
    public function getParentRecords(Request $request)
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
        // 'capture_dataset',
      );

      foreach ($record_types as $key => $value) {

        $params['record_type'] = $value;

        switch($value) {
          case 'subject':
            $params['field_name'] = 'subject_name';
            $params['id_field_name'] = 'subject_id';
            break;
          case 'item':
            $params['field_name'] = 'item_description';
            $params['id_field_name'] = 'item_id';
            break;
          case 'capture_dataset':
            $params['field_name'] = 'capture_dataset_name';
            $params['id_field_name'] = 'capture_dataset_id';
            break;
          default: // project
            $params['field_name'] = 'project_name';
            $params['id_field_name'] = 'project_id';
        }

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

          // $this->u->dumper($results);

          foreach ($results as $key => $value) {
            // Truncate long field values.
            $more_indicator = (strlen($value[ $params['field_name'] ]) > 38) ? '...' : '';
            $truncated_value = substr($value[ $params['field_name'] ], 0, 38) . $more_indicator;
            // Add to the $data array.
            $data[] = array('id' => $value[ $params['id_field_name'] ], 'value' => $truncated_value . ' [ ' . strtoupper(str_replace('_', ' ', $params['record_type'])) . ' ]'); // ', Project: ' . $value['project_name'] . ']'
          }
        }
      }

      // Return data as JSON
      return $this->json($data);
    }

    
    /**
     * @param string $record_type The record type (e.g. subject)
     * @return string
     */
    public function getJobType($record_type = null)
    {

      switch ($record_type) {
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
    public function createJob($base_record_id, $record_type, Request $request)
    {
      $job_id = null;
      $parent_records = [];

      // Get the parent Project's record ID (unless it's a project to begin with).
      if (!empty($base_record_id) && !empty($record_type) && ($record_type !== 'project')) {
        $parent_records = $this->repo_storage_controller->execute('getParentRecords', array(
          'base_record_id' => $base_record_id,
          'record_type' => $record_type,
        ));
      } else {
        // If the $record_type is a 'project', just use the $base_record_id, since that's the project ID.
        $parent_records['project_id'] = $base_record_id;
      }

      // If there are no results for a parent Project record ID, throw a createNotFoundException (404).
      if (empty($parent_records)) throw $this->createNotFoundException('Could not establish the parent project ID');

      if (!empty($parent_records) && isset($parent_records['project_id'])) {
        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $project = $this->repo_storage_controller->execute('getProject', array('project_id' => $parent_records['project_id']));
        if (!$project) throw $this->createNotFoundException('The Project record does not exist');
      }

      if (!empty($project)) {
        // Get the job type (what's being ingested?).
        $job_type = $this->getJobType($record_type);
        $uuid = $this->u->createUuid();
        // Insert a record into the job table.
        // TODO: Feed the 'job_label' and 'job_type' to the log leveraging fields from a form submission in the UI?
        $job_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job',
          'user_id' => $this->getUser()->getId(),
          'values' => array(
            'uuid' => $uuid,
            'project_id' => (int)$project['project_id'],
            'job_label' => 'Metadata Import: "' . $project['project_name'] . '"',
            'job_type' => $job_type . ' metadata import',
            'job_status' => 'uploading',
            'date_completed' => null,
            'qa_required' => 0,
            'qa_approved_time' => null,
          )
        ));
      }

      return $this->json(array('jobId' => (int)$job_id, 'uuid' => $uuid, 'projectId' => (int)$project['project_id']));
    }

    /**
     * Set Job Status
     * @Route("/admin/set_job_status/{job_id}/{status}", name="set_job_status", defaults={"job_id" = null, "status" = null}, methods="GET")
     *
     * @param string $job_id  The job ID
     * @param string $status  The status text
     * @param object $request Symfony's request object
     * @return bool
     */
    public function setJobStatus($job_id, $status, Request $request)
    {

      // If $job_id is empty, throw a createNotFoundException (404).
      if (empty($job_id)) throw $this->createNotFoundException('Job ID is empty');
      // If $status is empty, throw a createNotFoundException (404).
      if (empty($status)) throw $this->createNotFoundException('Status text is empty');

      // Update the record in the job table.
      // TODO: Feed the 'job_label' and 'job_type' to the log leveraging fields from a form submission in the UI?
      $result = $this->repo_storage_controller->execute('setJobStatus', array('job_id' => $job_id, 'status' => $status));

      return $this->json(array('statusSet' => $result));
    }

    /**
     * Get Job Status
     * @Route("/admin/get_job_status/{uuid}", name="get_job_status", defaults={"uuid" = null}, methods="GET")
     *
     * @param string $uuid  The job ID
     * @param object $request Symfony's request object
     * @return string
     */
    public function getJobStatus($uuid = null, Request $request)
    {
      $result = array();

      // If $uuid is empty, throw a createNotFoundException (404).
      if (empty($uuid)) throw $this->createNotFoundException('Job ID is empty');

      // Check the database to find the next job which hasn't had a BagIt validation performed against it.
      $result = $this->repo_storage_controller->execute('getJobData', array($uuid));

      // If $result is empty, throw a createNotFoundException (404).
      if (empty($result)) throw $this->createNotFoundException('Job status not found (404)');
      // If $result is not empty, limit to only returning the 'job_status'.
      if (!empty($result)) return $this->json($result['job_status']); 
    }

    /**
     * @param string $job_id_directory  The upload directory
     * @param string $filename  The file name
     * @return array  Import result and/or any messages
     */
    public function constructImportData($job_id_directory = null, $filename = null)
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

        // Changed to a regular expression.
        // See: https://stackoverflow.com/questions/3997336/explode-php-string-by-new-line
        $csv = preg_split('/\r\n|\r|\n/', $csv);

        foreach ($csv as $key => $line) {
          $json_array[$key] = str_getcsv($line);
        }

        // Read the first key from the array, which is the column headers.
        $target_fields = $json_array[0];

        foreach ($json_array as $key => $value) {
          // Replace numeric keys with field names.
          foreach ($value as $k => $v) {
            $field_name = $target_fields[$k];
            unset($json_array[$key][$k]);
            $json_array[$key][$field_name] = $v;
          }
          // Convert the array to an object.
          $json_object[] = (object)$json_array[$key];
        }

      }

      return $json_object;
    }

    /**
     * Remove Temporary Directory
     * @Route("/admin/remove_temporary_directory/{dirname}", name="remove_temporary_directory", defaults={"dirname" = null}, methods="GET")
     *
     * @param string $dirname  The directory name
     * @param object $request Symfony's request object
     * @return string
     */
    public function remove_temporary_directory($dirname = null, Request $request)
    {
      // If $dirname is empty, throw a createNotFoundException (404).
      if (empty($dirname)) throw $this->createNotFoundException('The directory name is empty');
      // If the directory doesn't exist, throw a createNotFoundException (404).
      if (!is_dir($this->uploads_directory . $dirname)) throw $this->createNotFoundException('The ' . $dirname . ' directory doesn\'t exist');
      // If the directory isn't a temporary directory, don't remove it, and throw a createNotFoundException (404).
      if (!strstr($dirname, 'temp-')) throw $this->createNotFoundException('The ' . $dirname . ' directory isn\'t a temporary directory');
      // Remove the temporary directory, and all of it's contents.
      $fileSystem = new Filesystem();
      $fileSystem->remove($this->uploads_directory . $dirname);
      // Return the response (200).
      return new Response('The ' . $dirname . ' directory has been removed', Response::HTTP_OK);
    }

    /**
     * @Route("/admin/purge_import/{uuid}", name="purge_imported_data_and_files", defaults={"uuid" = null}, methods="GET")
     *
     * @param int $uuid The job's UUID
     * @param object $conn Database connection object
     * @param object $request Symfony's request object
     * @return array
     */
    public function purgeImportedDataAndFiles($uuid, Connection $conn, Request $request)
    {
      if (empty($uuid)) throw $this->createNotFoundException('UUID not provided');

      if (!empty($uuid)) {

        // Check to see if the job record exists, and if it doesn't, throw a createNotFoundException (404).
        $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid));
        if (!$job_data) throw $this->createNotFoundException('The Job record does not exist');

        // Remove imported data.
        $results = $this->repo_storage_controller->execute('purgeImportedData', array('uuid' => $uuid));
        // Create a summary of rows deleted.
        $data = '';
        foreach ($results as $key => $value) {
          $data .= '<p><strong>Table:</strong> ' . $key . '&nbsp;&nbsp;&nbsp;<strong>Rows Deleted:</strong> ' . $value . '</p>';
        }

        // Remove the job directory.
        if (is_dir($this->uploads_directory . DIRECTORY_SEPARATOR . $uuid)) {
          $fileSystem = new Filesystem();
          $fileSystem->remove($this->uploads_directory . DIRECTORY_SEPARATOR . $uuid);
        }

        // Remove files from external storage.
        $flysystem = $this->container->get('oneup_flysystem.assets_filesystem');
        $result = $this->fileTransfer->removeFiles($uuid, $flysystem);
        // Return errors from the file removal process.
        if (!empty($result)) {
          foreach ($result as $key => $value) {
            if (isset($value['errors'])) {
              foreach ($value['errors'] as $ekey => $evalue) {
                $this->addFlash('error', 'External File Removal - ' . $evalue);
              }
            }
          }
        }

        // The message
        $this->addFlash('message', 'Job data and files have been successfully removed' . $data);
        // Redirect to the main Uploads page.
        return $this->redirect('/admin/ingest');
      }
    }

    /**
     * @Route("/admin/pjobs/remove", name="remove_jobs_from_processing_server", methods="GET")
     *
     * @param object $request Symfony's request object
     */
    public function removeJobsFromProcessingServer(Request $request)
    {

      $jobs = array();
      $message_type = 'error';
      $message = 'No processing jobs found to remove.';

      // Get the machine state.
      $jobs = $this->processing->getJobs();

      // Decode the JSON.
      $json_decoded = json_decode($jobs['result'], true);

      if (!empty($json_decoded)) {
        // Loop through jobs and delete each one by ID.
        foreach ($json_decoded as $key => $value) {
          $this->processing->deleteJob($value['id']);
        }
        $message_type = 'message';
        $message = 'All processing jobs have been removed from the processing server.';
      }

      // The message
      $this->addFlash($message_type, $message);
      // Redirect to the main Uploads page.
      return $this->redirect('/admin');
    }

    /**
     * Initialize a Processing Job
     *
     * @Route("/admin/initialize", name="initialize_processing_job", methods="GET")
     *
     * @param object $request Symfony's request object
     */
    public function initializeProcessingJob(Request $request)
    {

      $data = $processing_results = array();
      $filesystem = $this->container->get('oneup_flysystem.processing_filesystem');

      // Parameters to send to the processing service API.
      $params = array('meshFile' => 'nmnh-usnm_v_512384522-skull-master_model-2018_10_22.ply');
      // Path to the directory or file.
      $local_path = $this->uploads_directory . '3df_5bd4c0846fd3f0.72669883/testupload04-1model/data/1/nmnh-usnm_v_512384522-skull-master_model-2018_10_22.ply';
      // Modify the path for Windows if need be.
      $local_path = str_replace('/', DIRECTORY_SEPARATOR, $local_path);
      // Need to pass the parent record type and ID so we can associate a processing job with something.
      $parent_record_data = array(
        'record_id' => 7,
        'record_type' => 'model',
      );

      // Initialize the processing job.
      $data = $this->processing->initializeJob('inspect-mesh', $params, $local_path, $this->getUser()->getId(), $parent_record_data, $filesystem);

      // Send a message to the UI.
      $this->addFlash('message', 'Initialized processing job');

      // Render the page.
      return $this->render('import/processing_job.html.twig', array(
        'page_title' => 'Initialize Processing Job',
        'data' => $data,
        'processing_results' => $processing_results,
        'current_tab' => 'ingest'
      ));
    }

    /**
     * Get Processing Job
     *
     * @Route("/admin/getjob/{job_id}", name="get_processing_job", defaults={"job_id" = null}, methods="GET")
     *
     * @param object $request Symfony's request object
     */
    public function getProcessingJob($job_id, Request $request)
    {
      $data = $processing_results = array();

      // if (empty($job_id)) throw $this->createNotFoundException('Job ID not provided');

      if (!empty($job_id)) {

        $filesystem = $this->container->get('oneup_flysystem.processing_filesystem');

        // Path to the directory or file.
        $local_path = $this->uploads_directory . '3df_5bd4c0846fd3f0.72669883/testupload04-1model/data/1/nmnh-usnm_v_512384522-skull-master_model-2018_10_22.ply';

        // Get processing job status from the processing service.
        $result = $this->processing->getJob( $job_id );

        // Decode the returned JSON to a PHP array.
        $data = json_decode($result['result'], true);

        // Get processing results
        if (in_array($data['state'], array('error', 'done'))) {
          $processing_results = $this->processing->getProcessingResults($data['id'], $this->getUser()->getId(), $local_path, $filesystem);
        }

      }

      // Render the page.
      return $this->render('import/processing_job.html.twig', array(
        'page_title' => 'Get Processing Job',
        'data' => $data,
        'processing_results' => $processing_results,
        'current_tab' => 'ingest'
      ));
    }

    /**
     * Get Processing Job
     *
     * @Route("/admin/post_job_import_record", name="post_job_import_record", methods="POST")
     *
     * @param object $request Symfony's request object
     */
    public function postJobImportRecord(Request $request)
    {
      $response = $this->json(array());
      $req = $request->request->all();
      
      if (!empty($req['record_table']) && !empty($req['job_uuid']) && !empty($req['project_id']) && !empty($req['capture_dataset_id'])) {

        // Get the job data (for the job_id).
        $job_data = $this->repo_storage_controller->execute('getJobData', array($req['job_uuid']));

        // Insert into the job_import_record table
        $job_import_record_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job_import_record',
          'user_id' => $this->getUser()->getId(),
          'values' => array(
            'job_id' => $job_data['job_id'],
            'record_id' => $req['capture_dataset_id'],
            'project_id' => (int)$req['project_id'],
            'record_table' => $req['record_table'],
            'description' => null,
          )
        ));
        // Return the $job_import_record_id
        if ($job_import_record_id) $response = $this->json(array('id' => $job_import_record_id));
      }

      return $response;
    }
}
