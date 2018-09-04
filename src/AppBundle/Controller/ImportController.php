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

use AppBundle\Controller\ItemsController;
use AppBundle\Controller\DatasetsController;
use AppBundle\Controller\ModelController;

// use Psr\Log\LoggerInterface;

use AppBundle\Form\UploadsParentPickerForm;
use AppBundle\Entity\UploadsParentPicker;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

use AppBundle\Service\RepoFileTransfer;

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
    private $itemsController;
    private $datasetsController;
    private $modelsController;
    private $fileTransfer;

    /**
     * Constructor
     * @param object  $u  Utility functions object
     */
    public function __construct(AppUtilities $u, Connection $conn, TokenStorageInterface $tokenStorage, ItemsController $itemsController, DatasetsController $datasetsController, ModelController $modelsController, RepoFileTransfer $fileTransfer) // , LoggerInterface $logger
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->tokenStorage = $tokenStorage;

        $this->itemsController = $itemsController;
        $this->datasetsController = $datasetsController;
        $this->modelsController = $modelsController;
        $this->fileTransfer = $fileTransfer;

        // $this->logger = $logger;
        // Usage:
        // $this->logger->info('Import started. Job ID: ' . $job_id);

        // TODO: move this to parameters.yml and bind in services.yml.
        $this->uploads_directory = __DIR__ . '/../../../web/uploads/repository/';
        $this->uploads_path = '/uploads/repository';
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
        $accepted_file_types = '.csv, .txt, .jpg, .tif, .png, .dng, .obj, .ply, .mtl, .zip, .cr2';

        return $this->render('import/import_summary_dashboard.html.twig', array(
            'page_title' => 'Uploads',
            'form' => $form->createView(),
            'accepted_file_types' => $accepted_file_types,
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

      $data = $this->repo_storage_controller->execute('getDatatableImports', $query_params);

      return $this->json($data);
    }
    
    /**
     * @Route("/admin/import/{uuid}/{parent_project_id}/{parent_record_id}/{parent_record_type}", name="import_summary_details", defaults={"uuid" = null, "parent_project_id" = null, "parent_record_id" = null, "parent_record_type" = null}, methods="GET")
     *
     * @param int $uuid Job ID
     * @param int $parent_project_id Parent Project ID
     * @param int $parent_record_id Parent Record ID
     * @param int $parent_record_type Parent Record Type
     * @param object $conn Database connection object
     * @param object $project ProjectsController class
     * @param object $request Symfony's request object
     */
    public function import_summary_details($uuid, $parent_project_id, $parent_record_id, $parent_record_type, Connection $conn, ProjectsController $project, Request $request, DatasetElementsController $data_elements_controller, ValidateImagesController $images, KernelInterface $kernel)
    {

      // $this->u->dumper($uuid,0);
      // $this->u->dumper($parent_project_id,0);
      // $this->u->dumper($parent_record_id,0);
      // $this->u->dumper($parent_record_type);

      $project = [];
      $project['file_validation_errors'] = [];

      if (!empty($uuid)) {
        // Check to see if the job exists. If it doesn't, throw a createNotFoundException (404).
        $job_data = $this->repo_storage_controller->execute('getJobData', array($uuid));
        if (empty($job_data)) throw $this->createNotFoundException('The Job record does not exist');
      }

      if (!empty($parent_project_id)) {
        // Check to see if the parent record exists/active, and if it doesn't, throw a createNotFoundException (404).
        $project = $this->repo_storage_controller->execute('getProject', array('project_repository_id' => $parent_project_id));
        if (!$project) throw $this->createNotFoundException('The Project record does not exist');
      }

      // Get the total number of Item records for the import.
      if (!empty($uuid)) {

        // Get job import data (query the 'job_import_record' table).
        $job_record_data = $this->repo_storage_controller->execute('getImportedItems', array('job_id' => $job_data['job_id']));

        // If a record is NOT found within the 'job_import_record' table, add a message and execute validations and metadata ingests.
        if (!$job_record_data && ($job_data['job_status'] !== 'failed') && ($job_data['job_status'] !== 'complete')) {

          $this->addFlash('message', '<span class="glyphicon glyphicon-ok"></span> Files have been successfully uploaded. Validations and metadata ingests are currently in progress.');

          // // Execute validations and metadata ingests.
          // $application = new Application($kernel);
          // $application->setAutoExit(false);

          // $input = new ArrayInput(array(
          //   'command' => 'app:validate',
          //   'uuid' => $job_data['uuid'],
          //   'parent_project_id' => $parent_project_id,
          //   'parent_record_id' => $parent_record_id,
          //   'parent_record_type' => $parent_record_type
          // ));

          $input = array(
            $job_data['uuid'],
            $parent_project_id,
            $parent_record_id,
            $parent_record_type,
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
            $command = $php_binary_path . ' bin/console app:validate ' . implode(' ', $input) . ' > NUL';
          } else {
            $command = $php_binary_path . ' bin/console app:validate ' . implode(' ', $input) . ' > /dev/null 2>&1 &';
          }

          // $this->u->dumper($php_binary_path,0);
          // $this->u->dumper($command);

          $process = new Process($command);
          // $process->disableOutput();
          new NullOutput();
          // $process->start();
          try {
              $process->mustRun();
              // echo $process->getOutput();
          } catch (ProcessFailedException $exception) {
              echo $exception->getMessage();
          }

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

          // $this->u->dumper($res);
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
            $project['csv'][$file->getBasename()] = $this->construct_import_data($dir, $file->getBasename());
            $project['csv_row_count'][$file->getBasename()] = count($project['csv'][$file->getBasename()]);
          }
          // If there's csv data, encode to JSON so it can be passed on to Handsontables (JavaScript).
          if(isset($project['csv'])) {
            $project['csv'] = json_encode($project['csv']);
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

        // Get image validation errors if they exist.
        $project['image_validation_errors'] = $this->repo_storage_controller->execute('getRecords', array(
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
                  'Image Validation'
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

      }

      return $this->render('import/import_summary_item.html.twig', array(
        'page_title' => $job_record_data ? $project['job_label'] : 'Uploads: ' . $project['project_name'],
        'project' => $project,
        'job_data' => $job_data,
        'id' => $job_data['job_id'],
        'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
      ));
    }

    /**
     * @Route("/admin/import/{uuid}/datatables_browse_import_details", name="import_details_browse_datatables", methods="POST")
     *
     * Browse Import Details
     *
     * Run a query to retrieve the details of an import.
     *
     * @param  int $uuid The job's UUID
     * @param Request $request Symfony's request object
     * @return \Symfony\Component\HttpFoundation\JsonResponse The query result
     */
    public function datatables_browse_import_details($uuid, Request $request)
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
        // 'capture_dataset',
      );

      foreach ($record_types as $key => $value) {

        $params['record_type'] = $value;

        switch($value) {
          case 'subject':
            $params['field_name'] = 'subject_name';
            $params['id_field_name'] = 'subject_repository_id';
            break;
          case 'item':
            $params['field_name'] = 'item_description';
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
        $uuid = uniqid('3df_', true);
        // Insert a record into the job table.
        // TODO: Feed the 'job_label' and 'job_type' to the log leveraging fields from a form submission in the UI?
        $job_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'job',
          'user_id' => $this->getUser()->getId(),
          'values' => array(
            'uuid' => $uuid,
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

      return $this->json(array('jobId' => (int)$job_id, 'uuid' => $uuid, 'projectId' => (int)$project['project_repository_id']));
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
    public function set_job_status($job_id, $status, Request $request)
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
    public function get_job_status($uuid = null, Request $request)
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
    public function construct_import_data($job_id_directory = null, $filename = null)
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

        $csv = explode("\r\n", $csv);

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
     * @Route("/admin/purge_import/{uuid}", name="purge_imported_data_and_files", defaults={"uuid" = null}, methods="GET")
     *
     * @param int $uuid The job's UUID
     * @param object $conn Database connection object
     * @param object $request Symfony's request object
     * @return array
     */
    public function purge_imported_data_and_files($uuid, Connection $conn, Request $request)
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
                $this->addFlash('error', '<h4>External File Removal - ' . $evalue . '</h4>');
              }
            }
          }
        }

        // The message
        $this->addFlash('message', '<h4>Job data and files have been successfully removed</h4>' . $data);
        // Redirect to the main Uploads page.
        return $this->redirect('/admin/import');
      }
    }
}
