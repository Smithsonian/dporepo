<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\PhpExecutableFinder;

use AppBundle\Service\RepoProcessingService;
use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Service\RepoImport;

use AppBundle\Form\BatchProcessingForm;
use AppBundle\Form\WorkflowParametersForm;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

use AppBundle\Service\RepoUserAccess;

class WorkflowController extends Controller
{

  /**
   * @var object $u
   */
  public $u;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var object $edan
   */
  private $edan;

  /**
   * @var object $processing
   */
  private $processing;

  /**
   * @var string $processing_service_location
   */
  private $processing_service_location;

  /**
   * @var bool $processing_service_on
   */
  private $processing_service_on;

  /**
   * @var object $repo_import
   */
  private $repo_import;

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
   * @var string $accepted_file_types
   */
  private $accepted_file_types;

  /**
   * @var array $derivatives
   */
  private $derivatives;

  /**
   * @var string $check_icon_markup
   */
  private $check_icon_markup;

  /**
   * @var string $processing_service_down_message
   */
  private $processing_service_down_message;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(AppUtilities $u, Connection $conn, RepoProcessingService $processing, RepoImport $repo_import, string $processing_service_location, bool $processing_service_on, KernelInterface $kernel, string $uploads_directory)
  {
    $this->u = $u;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->processing = $processing;
    $this->processing_service_location = $processing_service_location;
    // Check to see if the processing service is available.
    $this->processing_service = $this->processing->isServiceAccessible();
    $this->processing_service_on = $processing_service_on;
    $this->repo_import = $repo_import;

    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $uploads_directory) : $uploads_directory;
    $this->accepted_file_types = '.csv, .txt, .jpg, .tif, .png, .dng, .obj, .ply, .mtl, .zip, .cr2';
    $this->repo_user_access = new RepoUserAccess($conn);

    // Check for the presence of the EDAN bundle.
    $bundles = $this->kernel->getBundles();
    $this->edan = array_key_exists('DpoEdanBundle', $bundles);

    $this->derivatives = array(
      'thumb' => array(
        'file_name' => '-50k-1024-web-thumb.glb',
      ),
      'low' => array(
        'file_name' => '-250k-1024-web-low.glb',
      ),
      'medium' => array(
        'file_name' => '-250k-2048-web-medium.glb',
      ),
      'high' => array(
        'file_name' => '-250k-4096-web-high.glb',
      ),
      'hd' => array(
        'file_name' => '-1000k-8192-web-hd.glb',
      )
    );

    $this->check_icon_markup = '&nbsp;<span class="glyphicon glyphicon-ok" aria-hidden="true" style="color:green;"></span> <span class="text-success">QC done</span>';
    $this->processing_service_down_message = 'The processing service is unavailable. Could not resolve host ';
  }

  /**
   * @Route("/admin/workflow/{uuid}/{workflow_recipe_id}/launch", name="workflow_launch", methods={"GET","POST"})
   * Create or edit a workflow record, and update the workflow status log.
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function launchWorkflow(Request $request) {
    /*
     * Simple user form that takes a UUID and workflow recipe ID,
     * and generates the workflow record.
     */

    $uuid = $request->attributes->get('uuid');
    $workflow_recipe_id = $request->attributes->get('workflow_recipe_id');

    if(empty($uuid) || empty($workflow_recipe_id)) {
      //@todo different response
      $response = new Response();
      $response->setStatusCode(404);
      return $response;
    }

    //@todo check user permissions- needs workflow permission; project-specific
    /*
        $username = $this->getUser()->getUsernameCanonical();
        $access = $this->repo_user_access->get_user_access_any($username, 'create_edit_lookups');

        if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
          $response = new Response();
          $response->setStatusCode(403);
          return $response;
        }
     */


    //@todo include anybody with admin role globally or for this project?
    $workflow['point_of_contact_guid_options'] = array(
      "Anderson, Max"=>0,
      "Blundell, Jon"=>1,
      "Conrad, Joe"=>2,
      "Dattoria, Megan"=>3
    );
    $workflow['point_of_contact_guid_picker'] = NULL;

    $workflow['uuid'] = $uuid;
    $workflow['recipe_id'] = $workflow_recipe_id;

    //@todo If the source JSON for the specified recipe is not found in /web, tell the user, and abort.
    // Look for /web/[workflow_recipe_id]_workflow_recipe.json

    $form = $this->createForm(WorkflowParametersForm::class, $workflow);

    // Handle the request
    $form->handleRequest($request);

    // If form is submitted and passes validation, insert/update the database record.
    if ($form->isSubmitted() && $form->isValid()) {

      $workflow = $form->getData();

      $point_of_contact = isset($workflow['point_of_contact']) ? $workflow['point_of_contact'] : NULL;

      $new_workflow = $this->createWorkflow($uuid, $workflow_recipe_id, $point_of_contact);

      if($new_workflow['return'] == 'success') {
        $this->addFlash('message', 'Workflow successfully created.');
        //@todo return $this->redirect('/admin');
      }
      else {
        $errors = implode('  ', $new_workflow['errors']);
        $this->addFlash('error', 'Workflow could not be created: ' . $errors);
      }
    }

    return $this->render('workflow/workflow_launch.html.twig', array(
      'page_title' => 'Launch Workflow',
      'data' => $workflow,
      'form' => $form->createView(),
    ));

  }

  /**
   * @Route("/admin/workflows/{workflow_id}", name="workflow_detail", methods={"GET","POST"})
   * Same code as used in workflow command
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function viewWorkflowDetails(Request $request) {

    $workflow_id = $request->attributes->get('workflow_id');

    $query_params = array('workflow_id' => $workflow_id);
    $workflow_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);
    $workflow = $workflow_data['workflow_definition'];

    $workflow_history = $this->repo_storage_controller->execute('getWorkflowHistory', $query_params);

    return $this->render('workflow/workflow_details.html.twig', array(
      'page_title' => 'Workflow Details',
      'data' => $workflow_data,
      'history' => $workflow_history,
    ));

  }

  /**
   * @Route("/admin/workflow_test", name="workflows_test", methods={"GET","POST"})
   * @Route("/admin/workflow_test/{workflow_id}", name="workflow_test", methods={"GET","POST"})
   * Test workflows.
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function testWorkflows(Request $request) {
    // Example route allowing user to see all workflows and click through to test.
    // User can click a workflow to view that workflow's details, select an updated step state, and preview the result (next step).

    $workflow_id = $request->attributes->get('workflow_id');

    if(empty($workflow_id)) {
      // Get all workflows
      $workflows_data = $this->repo_storage_controller->execute('getWorkflows', array());
      return $this->render('workflow/workflow_tests.html.twig', array(
        'page_title' => 'Test Workflows',
        'workflows' => $workflows_data,
      ));
    }
    else {
      $query_params = array('workflow_id' => $workflow_id);
      $workflow_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);

      return $this->render('workflow/workflow_test.html.twig', array(
        'page_title' => 'Test Workflow',
        'data' => $workflow_data,
      ));
    }

  }

  /**
   * @Route("/admin/workflow_test/jobcreate/{workflow_id}", name="workflow_test_jobcreate", methods={"GET","POST"})
   * Same code as used in workflow command
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function launchWorkflowStepJob(Request $request) {

    $workflow_id = $request->attributes->get('workflow_id');

    /*
    $query_params = array('workflow_id' => $workflow_id);
    $workflow_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);
    $workflow = $workflow_data['workflow_definition'];

    $recipe_id = NULL;

    // Get the recipeId for the current, un-executed step.
    $workflow_json_array = json_decode($workflow, true);
    foreach($workflow_json_array['steps'] as $step) {
      if($step['stepId'] == $workflow_data['step_id']) {
        $workflow_data['recipe_id'] = $recipe_id = $step['recipeId'];
        break;
      }
    }
    if(NULL == $recipe_id) {
      return;
    }

    switch($recipe_id) {
      case "test-success":
        // Pretend like we just kicked off this test processing recipe.
        $query_params = array(
          'workflow_id' => $workflow_id,
          'step_state' => 'created'
        );
        $this->repo_storage_controller->execute('updateWorkflow', $query_params);
        break;
      case "test-fail":
        // Pretend like we just kicked off this test processing recipe.
        $query_params = array(
          'workflow_id' => $workflow_id,
          'step_state' => 'created'
        );
        $this->repo_storage_controller->execute('updateWorkflow', $query_params);
        break;
    }
    */

    // Pretend like we just kicked off this test processing recipe.
    $query_params = array(
      'workflow_id' => $workflow_id,
      'step_state' => 'created',
      'user_id' => $this->getUser()->getId(),
    );
    $this->repo_storage_controller->execute('updateWorkflow', $query_params);

    return $this->redirect('/admin/workflow_test/' . $workflow_id);

  }

  /**
   * @Route("/admin/workflow_test/{workflow_id}/go", name="workflow_test_step", methods={"GET","POST"}, defaults={"step_state"= NULL})
   * @Route("/admin/workflow_test/{workflow_id}/go/{step_state}", name="workflow_test_step_state", methods={"GET","POST"})
   * Set the status of a workflow
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function advanceWorkflowStep(Request $request) {

    $workflow_id = $request->attributes->get('workflow_id');
    $simulate_step_state = $request->attributes->get('step_state');

    $recipe_id = NULL;
    $query_params = array(
      'workflow_id' => $workflow_id
    );
    $workflow_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);
    $workflow_definition = $workflow_data['workflow_definition'];

    // Get the recipeId for the current, un-executed step.
    $workflow_definition_json_array = json_decode($workflow_definition, true);
    foreach($workflow_definition_json_array['steps'] as $step) {
      if($step['stepId'] == $workflow_data['step_id']) {
        $workflow_data['recipe_id'] = $recipe_id = $step['recipeId'];
        break;
      }
    }

    if(NULL == $recipe_id) {
      return $this->redirect('/admin/workflow_test/' . $workflow_id);
    }

    $recipe_step_state = NULL;
    switch($recipe_id) {
      case "test-success":
        $recipe_step_state = "success";
        break;
      case "test-fail":
        $recipe_step_state = "error";
        break;
    }

    // Favor the user's provided state.
    $new_step_state = isset($simulate_step_state) ? $simulate_step_state : (isset($recipe_step_state) ? $recipe_step_state : "success");
    $query_params = array(
      'workflow_id' => $workflow_id,
      'step_state' => $new_step_state,
      'user_id' => $this->getUser()->getId(),
    );
    $this->repo_storage_controller->execute('updateWorkflow', $query_params);

    if($new_step_state == "success") {
      // Get the next step.
      $query_params = array(
        'workflow_json_array' => $workflow_definition_json_array,
        'step_id' => $workflow_data['step_id']
      );
      $next_step_details = $this->repo_storage_controller->execute('getWorkflowNextStep', $query_params);

      if(isset($next_step_details['status']) && ($next_step_details['status'] == 'done')) {
        $query_params = array(
          'workflow_id' => $workflow_id,
          'step_state' => "done",
          'processing_job_id' => NULL,
        );
      }
      else {
        $query_params = array(
          'workflow_id' => $workflow_id,
          'step_id' => $next_step_details['stepId'],
          'step_type' => $next_step_details['stepType'],
          'step_state' => NULL,
          'processing_job_id' => NULL,
          'user_id' => $this->getUser()->getId(),
        );
      }
      // Update the workflow with the next step.
      $this->repo_storage_controller->execute('updateWorkflow', $query_params);

    }

    return $this->redirect('/admin/workflow_test/' . $workflow_id);

  }

  public function createWorkflow($uuid, $workflow_recipe_id, $user_id) {
    /*
     * Either Upload/ingest or a user clicking a button triggers kicking off a workflow.
        Write to the workflow table the workflow recipe contents (photogrammetry v1 JSON),
        UUID for the project/item,
        and the workflow recipe's first step's details which are extracted from recipe-
        step ID, step type, state=null, job id=null.
     */

    // write [uuid] => 123 [recipe_id] => photogrammetry_v1 [point_of_contact] => 2
    $query_params = array(
      'ingest_job_uuid' => $uuid,
      'workflow_recipe_id' => $workflow_recipe_id,
      'user_id' => $user_id,
    );
    $workflow_data = $this->repo_storage_controller->execute('createWorkflow', $query_params);
    return $workflow_data;
  }

  /**
   * @Route("/admin/datatables_browse_workflows", name="datatables_browse_workflows", methods="POST")
   *
   * Browse Workflows
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function datatablesBrowseWorkflows(Request $request)
  {
    $username = $this->getUser()->getUsernameCanonical();
    //@todo which permission?
    /*$access = $this->repo_user_access->get_user_access_any($username, 'view_projects');

    $project_ids = array(0);
    if(array_key_exists('project_ids', $access) && isset($access['project_ids'])) {
      $project_ids = $access['project_ids'];
    }
    */

    $req = $request->request->all();
    $item_id = !empty($req['item_id']) ? $req['item_id'] : NULL;

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
      'item_id' => $item_id,
    );
    if ($search) {
      $query_params['search_value'] = $search;
    }
    //$query_params['project_ids'] = $project_ids;

    // Look in workflow table for workflows belonging to an item_id.
    $data = $this->repo_storage_controller->execute('getDatatableWorkflows', $query_params);

    return $this->json($data);
  }

  /**
   * @Route("/admin/workflow/{workflow_id}", name="workflow", methods={"GET"})
   *
   * @param Request $request
   * @param object $request Symfony's request object
   */
  public function workflow(Request $request)
  {

    // If the processing service is not turned on (in parameters.yml) or if it is on and not accessible,
    // disable the interface by setting $service_error = true, and display error messages.
    if (!$this->processing_service_on || ($this->processing_service_on && !$this->processing_service)) {
      $this->addFlash('error', $this->processing_service_down_message . $this->processing_service_location);
    }

    $workflow_id = $request->attributes->get('workflow_id');

    $query_params = array('workflow_id' => $workflow_id);
    $workflow_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);

    // If the workflow isn't found, throw a createNotFoundException (404).
    if (empty($workflow_data)) throw $this->createNotFoundException('Workflow not found');

    $model = $this->getModelInfo($workflow_data['item_id'], 'master');

    if (!empty($model)) {
      $workflow_data['model_id'] = $model[0]['model_id'];
    }

    // Get the job's data via job_uuid.
    $workflow_data['ingest_job'] = $this->repo_storage_controller->execute('getJobData', array($workflow_data['ingest_job_uuid']));

    // If the HD QC is done (url GET param qc_hd_done), pass it to $this->setInterface().
    // and write the QC 'done' file to the filesystem.
    if (null !== $request->query->get('qc_hd_done')) {
      $workflow_data['qc_hd_done'] = true;
      // Write the QC 'done' file to the filesystem.
      $this->writeQcDoneFile($workflow_data);
    }

    // If the web thumb QC is done (url GET param qc_thumb_done), pass it to $this->setInterface().
    // and write the QC 'done' file to the filesystem.
    if (null !== $request->query->get('qc_thumb_done')) {
      $workflow_data['qc_thumb_done'] = true;
      // Write the QC 'done' file to the filesystem.
      $this->writeQcDoneFile($workflow_data);
    }

    // If QC for a web derivative is done (url GET param qc_' . $key . '_done), pass it to $this->setInterface().
    // and write the QC 'done' file to the filesystem.
    foreach ($this->derivatives as $key => $value) {
      if (null !== $request->query->get('qc_' . $key . '_done')) {
        $workflow_data['qc_' . $key . '_done'] = true;
        // Write the QC 'done' file to the filesystem.
        $this->writeQcDoneFile($workflow_data);
      }
    }

    // Set a flag for the template for handling the next step (e.g. upload, advance, etc.).
    $workflow_data['interface'] = $this->setInterface($workflow_data);
    $workflow_data['accepted_file_types'] = $this->accepted_file_types;

    // Update the processing_job_id if it's passed by the $workflow_data['interface'] result.
    if (isset($workflow_data['interface']['processing_job_id'])) {
      $workflow_data['processing_job_id'] = $workflow_data['interface']['processing_job_id'];
    }

    return $this->render('workflow/workflow.html.twig', array(
      'page_title' => 'Workflow',
      'data' => $workflow_data,
    ));

  }

  /**
   * Set Interface
   *
   * @param array $w Workflow data
   * @return array
   */
  public function setInterface($w = array())
  {

    // Default interface data.
    $data = array(
      'action' => 'advance',
      'header' => 'Next Step?',
      'message' => 'Continue to the next step.',
    );

    // If the step_state has been set to an error, send a flag to the template for handling.
    if (!empty($w) && ($w['step_type'] === 'manual')) {

      switch ($w['step_id']) {
        case 'qc-hd':
          // Error: Manually upload replacement
          if ($w['step_state'] === 'error') {
            $data = $this->qcHdError($w);
          }
          // Success: Manual QC
          if (empty($w['step_state'])) $data = $this->qcModel($w);
          break;
        case 'qc-web-thumb':
          // Error: Manually upload replacement
          if ($w['step_state'] === 'error') {

            // $this->u->dumper('qc-web-thumb error');

            // @todo
            // $data = $this->qcWebThumbError($w);
          }
          // Success: Manual QC
          if (empty($w['step_state'])) $data = $this->qcModel($w);
          break;
        case 'qc-web':
          // Error: Manually upload replacement
          if ($w['step_state'] === 'error') {
            // @todo
            // $data = $this->qcWebError($w);
          }
          // Success: Manual QC
          if (empty($w['step_state'])) $data = $this->qcModel($w);
          // QC Done
          if ($w['step_state'] === 'done') {
            $data = $this->qcModel($w);
            $data['header'] = 'QC Completed';
            $data['message'] .= 'Web HD and derivative models have been processed and approved.';
          }
          break;
      }
      
    }

    if (!empty($w) && ($w['step_type'] === 'auto')) {

      switch ($w['step_id']) {
        case 'web-hd':
          // Initialize the 'web-multi' procesing job.
          $data = array(
            'action' => 'process',
            'header' => 'Generating HD Web Model',
            'message' => 'The high definition web model is being processed. No actions available.',
            'processing_job_id' => $w['processing_job_id'],
          );
          break;
        case 'web-thumb':
        case 'web-multi':
          // Initialize the 'web-thumb' or 'web-multi' procesing job.
          if (empty($w['step_state'])) {
            $data = $this->initializeProcessingJob($w);
          }
          break;
      }

    }

    // Check for 2D thumbnail images.
    $data['thumbnail_images'] = $this->getThumbnailImages($w);

    return $data;
  }

  /**
   * @Route("/admin/get_processing_status/{workflow_id}", name="get_processing_status", methods={"GET"})
   *
   * @param Request $request
   * @param object $request Symfony's request object
   */
  public function getProcessingStatus(Request $request)
  {

    $json_decoded = array();
    $workflow_id = $request->attributes->get('workflow_id');

    if (!empty($workflow_id)) {
      // Get workflow data.
      $query_params = array('workflow_id' => $workflow_id);
      $workflow_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);
      // If the workflow isn't found, throw a createNotFoundException (404).
      if (empty($workflow_data)) throw $this->createNotFoundException('Workflow not found');

      // Get processing job data.
      $processing_job = $this->processing->getJob($workflow_data['processing_job_id']);
      // If the processing job isn't found, throw a createNotFoundException (404).
      if (!$processing_job['result']) throw $this->createNotFoundException('Processing job not found');

      // Decode the JSON coming from the processing service.
      $json_decoded = json_decode($processing_job['result'], true);
    }

    return $this->json($json_decoded);
  }

  /**
   * @Route("/admin/get_workflow_status/{workflow_id}", name="get_workflow_status", methods={"GET"})
   *
   * @param Request $request
   * @param object $request Symfony's request object
   */
  public function getWorkflowStatus(Request $request)
  {

    $data = array();
    $workflow_id = $request->attributes->get('workflow_id');

    if (!empty($workflow_id)) {
      // Get workflow data.
      $query_params = array('workflow_id' => $workflow_id);
      $data = $this->repo_storage_controller->execute('getWorkflows', $query_params);
      // If the workflow isn't found, throw a createNotFoundException (404).
      if (empty($data)) throw $this->createNotFoundException('Workflow not found');
    }

    return $this->json($data);
  }

  /**
   * Get Thumbnail Images
   *
   * @param array $w Workflow data
   * @return array
   */
  private function getThumbnailImages($w = array())
  {
    $data = false;
    $image_sizes = array('thumb', 'low', 'medium', 'high');

    if (!empty($w)) {

      // Get the master model's path.
      $path = $this->getPathInfo($w);

      // If the model path can't be found, throw a createNotFoundException (404).
      if (empty($path)) throw $this->createNotFoundException('Model path not found');

      $directory = pathinfo($path[0]['asset_path'], PATHINFO_DIRNAME);
      $base_file_name = pathinfo($path[0]['asset_path'], PATHINFO_FILENAME);

      foreach ($image_sizes as $key => $value) {

        // Thumbnail images can be either JPEGs or PNGs.
        $jpg = $directory . DIRECTORY_SEPARATOR . $base_file_name . '-image-' . $value . '.jpg';
        $png = $directory . DIRECTORY_SEPARATOR . $base_file_name . '-image-' . $value . '.png';
        if (is_file($jpg)) $data[$value] = DIRECTORY_SEPARATOR . str_replace($this->project_directory . 'web' . DIRECTORY_SEPARATOR, '', $jpg);
        if (is_file($png)) $data[$value] = DIRECTORY_SEPARATOR . str_replace($this->project_directory . 'web' . DIRECTORY_SEPARATOR, '', $png);

        // Sometimes the Voyager QC tool outputs files without a base name.
        $jpg_plain = $directory . DIRECTORY_SEPARATOR . 'image-' . $value . '.jpg';
        $png_plain = $directory . DIRECTORY_SEPARATOR . 'image-' . $value . '.png';
        if (is_file($jpg_plain)) $data[$value] = DIRECTORY_SEPARATOR . str_replace($this->project_directory . 'web' . DIRECTORY_SEPARATOR, '', $jpg_plain);
        if (is_file($png_plain)) $data[$value] = DIRECTORY_SEPARATOR . str_replace($this->project_directory . 'web' . DIRECTORY_SEPARATOR, '', $png_plain);
      }

    }

    return $data;
  }

  /**
   * Get Model Info
   *
   * @param int $item_id The item ID
   * @param string $type The model type (e.g. master, )
   * @return array
   */
  public function getModelInfo($item_id = null, $type = 'master')
  {

    $data = array();

    if(!empty($item_id)) {
      // Get the master model's ID, so it can linked to.
      $data = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'model',
          'fields' => array(
            array(
              'table_name' => 'model',
              'field_name' => 'model_id',
            ),
            array(
              'table_name' => 'file_upload',
              'field_name' => 'file_name',
            ),
            array(
              'table_name' => 'file_upload',
              'field_name' => 'file_path',
            ),
          ),
          // Joins
          'related_tables' => array(
            array(
              'table_name' => 'model_file',
              'table_join_field' => 'model_id',
              'join_type' => 'LEFT JOIN',
              'base_join_table' => 'model',
              'base_join_field' => 'model_id',
            ),
            array(
              'table_name' => 'file_upload',
              'table_join_field' => 'file_upload_id',
              'join_type' => 'LEFT JOIN',
              'base_join_table' => 'model_file',
              'base_join_field' => 'file_upload_id',
            )
          ),
          'limit' => 1,
          'search_params' => array(
            0 => array('field_names' => array('model.item_id'), 'search_values' => array((int)$item_id), 'comparison' => '='),
            1 => array('field_names' => array('model.model_purpose'), 'search_values' => array($type), 'comparison' => '='),
          ),
          'search_type' => 'AND',
        )
      );
    }

    return $data;
  }

  /**
   * Get Path Info
   *
   * @param array $w Workflow data
   */
  public function getPathInfo($w = null)
  {

    $data = array();

    if (!empty($w)) {

      if (!empty($w['processing_job_id'])) {
        $search_field = 'processing_service_job_id';
        $search_value = $w['processing_job_id'];
      } else {
        $search_field = 'ingest_job_uuid';
        $search_value = $w['ingest_job_uuid'];
      }

      // Get the file path from the processing_job metadata storage.
      $data = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'processing_job',
          'fields' => array(
            array(
              'table_name' => 'processing_job',
              'field_name' => 'asset_path',
            ),
          ),
          'limit' => 1,
          'search_params' => array(
            0 => array('field_names' => array('processing_job.' . $search_field . ''), 'search_values' => array($search_value), 'comparison' => '='),
            1 => array('field_names' => array('processing_job.recipe'), 'search_values' => array('web-'), 'comparison' => 'LIKE'),
          ),
          'search_type' => 'AND',
          'omit_active_field' => true,
        )
      );
    }

    return $data;
  }

  /**
   * Get File Info
   *
   * @param array $path The absolute path to the file
   * @return array
   */
  public function getFileInfo($path = null)
  {
    $data = array();

    if (!empty($path)) {

      // Remove path info from the absolute path to match how the path is stored in the metadata storage.
      // e.g. /var/www/html/web/uploads/repository/5E4ED85E-EB10-374A-7366-6F8BDF49F46C/...
      // becomes: /uploads/repository/5E4ED85E-EB10-374A-7366-6F8BDF49F46C/...
      $search = $this->project_directory . $this->uploads_directory;
      $replace = (DIRECTORY_SEPARATOR === '\\') ? '\\uploads\\repository\\' : '/uploads/repository/';
      $file_path_for_query = str_replace($search, $replace, $path);

      $data = $this->repo_storage_controller->execute('getRecords', array(
          'base_table' => 'file_upload',
          'fields' => array(),
          'limit' => 1,
          'search_params' => array(
            0 => array('field_names' => array('file_upload.file_path'), 'search_values' => array($file_path_for_query), 'comparison' => '='),
          ),
          'search_type' => 'AND',
          'omit_active_field' => true,
        )
      );

      // Temporary hack for Windows paths - when data was imported from a Mac environment.
      if ((DIRECTORY_SEPARATOR === '\\') && empty($data)) {

        $file_path_for_query = str_replace('\\', '/', $file_path_for_query);

        // $this->u->dumper($file_path_for_query);

        $data = $this->repo_storage_controller->execute('getRecords', array(
            'base_table' => 'file_upload',
            'fields' => array(),
            'limit' => 1,
            'search_params' => array(
              0 => array('field_names' => array('file_upload.file_path'), 'search_values' => array($file_path_for_query), 'comparison' => '='),
            ),
            'search_type' => 'AND',
            'omit_active_field' => true,
          )
        );

      }

    }

    return $data;
  }

  /**
   * QC HD
   *
   * @param array $w Workflow data
   * @return array
   */
  public function qcModel($w = array())
  {
    $data = array();
    $document_json_path = null;

    if (!empty($w)) {

      // Get the master model's path.
      $path = $this->getPathInfo($w);
      // If the model path can't be found, throw a createNotFoundException (404).
      if (empty($path)) throw $this->createNotFoundException('Model path not found');

      $directory = pathinfo($path[0]['asset_path'], PATHINFO_DIRNAME);
      $base_file_name = pathinfo($path[0]['asset_path'], PATHINFO_FILENAME);
      // Since we're building the WebDAV path, all slashes need to be forward slashes.
      $webdav_directory = str_replace('\\', '/', $directory);
      $project_directory = str_replace('\\', '/', $this->project_directory);
      // Path to the document.json file.
      $document_json_path = $directory . DIRECTORY_SEPARATOR . $base_file_name . '-document.json';
      // If the document.json file doesn't exist, throw a createNotFoundException (404).
      if (!is_file($document_json_path)) throw $this->createNotFoundException('JSON not found - ' . $base_file_name . '-document.json');

      // The webDav-based path to the root.
      $root = str_replace($project_directory . 'web/uploads/repository', '/webdav', $webdav_directory) . '/';
      // The webDav-based path to the document.json.
      $document = $root . $base_file_name . '-document.json';

      // Loop through and QC web derivatives (low, medium, and high).
      if ($w['step_id'] === 'qc-web') {

        // Interface data.
        $data = array(
          'action' => 'qc',
          'header' => 'QC: Web Models',
          'message' => '',
        );

        // If the step_state is done, add the HD web and web thumb models.
        if ($w['step_state'] === 'done') {
          $data['message'] .= $this->qcDoneAdd($w);
        }

        $start = '<p><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> ';
        $end = '</strong></a></p>';
        $url = '/lib/javascripts/voyager-tools/voyager-story-dev.html?';

        // Unset the thumb and hd derivatives since we're dealing with the low, medium, and high derivatives.
        unset($this->derivatives['thumb'], $this->derivatives['hd']);

        $data['qc_done_count'] = 0;
        foreach ($this->derivatives as $key => $value) {

          // Make sure the .glb file exists.
          $glb_file_path = $directory . DIRECTORY_SEPARATOR . $base_file_name . $value['file_name'];
          $glb_file_info = $this->getFileInfo($glb_file_path);
          // If the .glb file can't be found, throw a createNotFoundException (404).
          if (empty($glb_file_info)) throw $this->createNotFoundException('Model not found - ' . $base_file_name . $value['file_name']);
          // The URL parameters.
          $url_params = array(
            'root' => $root,
            'document' => $document,
            'mode' => 'QC',
            'referrer' => '/admin/workflow/' . $w['workflow_id'] . '?qc_' . $key . '_done',
          );
          // If QC is done, add a check icon.
          $check_icon_web = is_file($directory . DIRECTORY_SEPARATOR . 'qc_' . $key . '_done.txt') ? $this->check_icon_markup : '';
          // Overwrite the $end variable.
          $end = '</strong></a>' . $check_icon_web . '</p>';
          // Advance the qc_done_count, so we have an indication when to display the "Complete QC Workflow" button.
          if (!empty($check_icon_web)) $data['qc_done_count']++;
          // Set the message for the UI interface.
          $data['message'] .= $start . '<a href="' . $url . http_build_query($url_params) . '"><strong>QC model (' . $key . ')' . $end;
        }

      }
      // QC HD model and web thumb
      else {

        switch($w['step_id']) {
          case 'qc-hd':
            $file_name = $this->derivatives['hd']['file_name'];
            $done_file_name = 'qc_hd_done.txt';
            $referrer_param = '?qc_hd_done';
            $qc_title = 'QC/Position HD model';
            $next_step_title = 'Generate web thumb';
            break;
          case 'qc-web-thumb':
            $file_name = $this->derivatives['thumb']['file_name'];
            $done_file_name = 'qc_thumb_done.txt';
            $referrer_param = '?qc_thumb_done';
            $qc_title = 'QC model (thumb)';
            $next_step_title = 'Generate web derivatives';
            break;
        }

        // Make sure the .glb file exists.
        $glb_file_path = $directory . DIRECTORY_SEPARATOR . $base_file_name . $file_name;
        $glb_file_info = $this->getFileInfo($glb_file_path);
        // If the .glb file can't be found, throw a createNotFoundException (404).
        if (empty($glb_file_info)) throw $this->createNotFoundException('Model not found - ' . $base_file_name . $file_name);
        // The URL parameters.
        $url_params = array(
          'root' => $root,
          'document' => $document,
          'mode' => 'QC',
          'referrer' => '/admin/workflow/' . $w['workflow_id'] . $referrer_param,
        );
        // If QC is done, add a check icon.
        $check_icon = is_file($directory . DIRECTORY_SEPARATOR . $done_file_name) ? $this->check_icon_markup : '';
        // Interface data.
        $data = array(
          'action' => 'qc',
          'header' => $qc_title,
          'message' => '<p><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <a href="/lib/javascripts/voyager-tools/voyager-story-dev.html?' . http_build_query($url_params) . '"><strong>' . $qc_title . '</strong></a>' . $check_icon . '</p>',
        );
        // If QC is done, add a link to generate web thumb.
        if (is_file($directory . DIRECTORY_SEPARATOR . $done_file_name)) {
          // If the processing service turned on (in parameters.yml) and is accessible, display the link. Otherwise just display text.
          if ($this->processing_service_on && $this->processing_service) {
            $link = '<a href="/admin/workflow/' . $w['workflow_id'] . '/go/success"><strong>' . $next_step_title . '</strong></a>';
          } else {
            $link = '<span class="text-danger">' . $next_step_title . ' (disabled)</span>';
          }
          $data['message'] .= '<p><span class="glyphicon glyphicon-cog" aria-hidden="true"></span> ' . $link . '</p>';
        }

      }

    }

    return $data;
  }

  /**
   * QC HD Error
   *
   * @param array $w Workflow data
   * @return array
   */
  public function qcHdError($w = array())
  {
    $data = array();

    if (!empty($w)) {

      // Get the master model's path.
      $path = $this->getPathInfo($w);

      // If the model path can't be found, throw a createNotFoundException (404).
      if (empty($path)) throw $this->createNotFoundException('Model path not found');

      // Get metadata for the errored file so pertinent information can be logged to the new file_upload record.
      $original_file_info = $this->getFileInfo($path[0]['asset_path']);
      // If the original model metadata can't be found, throw a createNotFoundException (404).
      if (empty($original_file_info)) throw $this->createNotFoundException('Original HD model metadata not found');

      // Interface data.
      $data = array(
        'action' => 'upload',
        'header' => 'Upload a Replacement HD Model and UV Map',
        'message' => 'Drop a replacement HD model file and diffuse UV map file here or click "Add Files" to upload.',
        'upload_path' => pathinfo($path[0]['asset_path'], PATHINFO_DIRNAME),
        'errored_file_name' => pathinfo($path[0]['asset_path'], PATHINFO_FILENAME),
        'job_id' => $w['ingest_job_uuid'],
        'parent_record_id' => $original_file_info[0]['parent_record_id'],
        'parent_record_type' => $original_file_info[0]['parent_record_type'],
      );

    }

    return $data;
  }

  /**
   * QC Done Add
   *
   * @param array $w Workflow data
   * @return array
   */
  public function qcDoneAdd($w = array())
  {
    $data = '';
    $check_icon = $check_icon_thumb = $check_icon_web = '';

    if (!empty($w)) {

      // Get the master model's path.
      $path = $this->getPathInfo($w);

      // If the model path can't be found, throw a createNotFoundException (404).
      if (empty($path)) throw $this->createNotFoundException('Model path not found');

      $directory = pathinfo($path[0]['asset_path'], PATHINFO_DIRNAME);
      $base_file_name = pathinfo($path[0]['asset_path'], PATHINFO_FILENAME);
      // Since we're building the WebDAV path, all slashes need to be forward slashes.
      $webdav_directory = str_replace('\\', '/', $directory);
      $project_directory = str_replace('\\', '/', $this->project_directory);
      // Path to the document.json file.
      $document_json_path = $directory . DIRECTORY_SEPARATOR . $base_file_name . '-document.json';
      // If the document.json file doesn't exist, throw a createNotFoundException (404).
      if (!is_file($document_json_path)) throw $this->createNotFoundException('JSON not found - ' . $base_file_name . '-document.json');

      // The webDav-based path to the root.
      $root = str_replace($project_directory . 'web/uploads/repository', '/webdav', $webdav_directory) . '/';
      // The webDav-based path to the document.json.
      $document = $root . $base_file_name . '-document.json';

      // Web HD
      // Make sure the .glb file exists.
      $glb_file_path = $directory . DIRECTORY_SEPARATOR . $base_file_name . $this->derivatives['hd']['file_name'];
      $glb_file_info = $this->getFileInfo($glb_file_path);
      // If the .glb file can't be found, throw a createNotFoundException (404).
      if (empty($glb_file_info)) throw $this->createNotFoundException('Model not found - ' . $base_file_name . $this->derivatives['hd']['file_name']);
      // The URL parameters.
      $url_params = array(
        'root' => $root,
        'document' => $document,
        'mode' => 'QC',
        'referrer' => '/admin/workflow/' . $w['workflow_id'],
      );
      // If QC is done, add a check icon.
      $check_icon = is_file($directory . DIRECTORY_SEPARATOR . 'qc_hd_done.txt') ? $this->check_icon_markup : '';
      // Interface data.
      $data .= '<p><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <a href="/lib/javascripts/voyager-tools/voyager-story-dev.html?' . http_build_query($url_params) . '"><strong>QC/Position HD model</strong></a>' . $check_icon . '</p>';

      // Web Thumb
      // Make sure the .glb file exists.
      $glb_file_path = $directory . DIRECTORY_SEPARATOR . $base_file_name . $this->derivatives['thumb']['file_name'];
      $glb_file_info = $this->getFileInfo($glb_file_path);
      // If the .glb file can't be found, throw a createNotFoundException (404).
      if (empty($glb_file_info)) throw $this->createNotFoundException('Model not found - ' . $base_file_name . $this->derivatives['thumb']['file_name']);
      // The URL parameters.
      $url_params = array(
        'root' => $root,
        'document' => $document,
        'mode' => 'QC',
        'referrer' => '/admin/workflow/' . $w['workflow_id'],
      );
      // If QC is done, add a check icon.
      $check_icon_thumb = is_file($directory . DIRECTORY_SEPARATOR . 'qc_thumb_done.txt') ? $this->check_icon_markup : '';
      // Interface data.
      $data .= '<p><span class="glyphicon glyphicon-eye-open" aria-hidden="true"></span> <a href="/lib/javascripts/voyager-tools/voyager-story-dev.html?' . http_build_query($url_params) . '"><strong>QC Web Thumb model</strong></a>' . $check_icon_thumb . '</p>';
    }

    return $data;
  }

  /**
   * Initialize Processing Job
   *
   * @param array $w Workflow data
   * @return array
   */
  public function initializeProcessingJob($w = array())
  {
    $data = array();
    $step_name = $uv_map = null;
    $workflow_definition_json_array = json_decode($w['workflow_definition'], true);

    // Get the workflow step info.
    foreach($workflow_definition_json_array['steps'] as $step) {
      if($step['stepId'] === $w['step_id']) {
        $step_name = $step['name'];
        break;
      }
    }

    if (!empty($w) && !empty($step_name)) {

      // Check to see if the processing job already exists.
      if (isset($w['processing_job_id']) && !empty($w['processing_job_id'])) {

        // Interface data.
        $data = array(
          'action' => 'process',
          'header' => $step_name,
          'message' => 'The processing job has been initialized and launched.',
          'processing_job_id' => $w['processing_job_id'],
        );

        return $data;
      }

      // Get the master model's path.
      $path = $this->getPathInfo($w);
      // If the model path can't be found, throw a createNotFoundException (404).
      if (empty($path)) throw $this->createNotFoundException('Model path not found');
      // Get metadata for the errored file so pertinent information can be logged to the new file_upload record.
      $original_file_info = $this->getFileInfo($path[0]['asset_path']);
      // If the original model metadata can't be found, throw a createNotFoundException (404).
      if (empty($original_file_info)) throw $this->createNotFoundException('Model metadata not found');
      // If the model can't be found, throw a createNotFoundException (404).
      if (!is_file($path[0]['asset_path'])) throw $this->createNotFoundException('Model file not found');

      // Get the ID of the recipe, so it can be passed to processing service's job creation endpoint (post_job).
      $recipe = $this->processing->getRecipeByName($w['step_id']);

      // If the web-thumb or web-multi recipe can't be found, throw a createNotFoundException (404).
      if (isset($recipe['error']) && !empty($recipe['error'])) throw $this->createNotFoundException($recipe['error']);

      // Get the UV map.
      $uv_map = $this->processing->getUvMap($path[0]['asset_path']);

      if (!empty($uv_map)) {
        $directory = $this->project_directory . 'web' . pathinfo($uv_map, PATHINFO_DIRNAME);
        $base_file_name = pathinfo($uv_map, PATHINFO_BASENAME);
      }


      // Initialize the processing job.
      // Create a timestamp for the procesing job name.
      $job_name = str_replace('+00:00', 'Z', gmdate('c', strtotime('now')));
      // Processing job parameters.
      $params = array(
        'highPolyMeshFile' => pathinfo($path[0]['asset_path'], PATHINFO_BASENAME)
      );

      // Add the UV map to the parameters.
      if (!empty($directory) && is_file($directory . DIRECTORY_SEPARATOR . $base_file_name)) $params['highPolyDiffuseMapFile'] = $base_file_name;

      // Add the document.json file to the parameters.
      $model_file_name = pathinfo($path[0]['asset_path'], PATHINFO_FILENAME);
      $document_file_name = $model_file_name . '-document.json';
      if (!empty($directory) && is_file($directory . DIRECTORY_SEPARATOR . $document_file_name)) $params['documentFile'] = $document_file_name;

      // Get subject record from EDAN to inject tombstone information into document.json.
      if ($this->edan && ($w['step_id'] === 'web-thumb')) {
        $edan_json = $this->repo_import->addEdanDataToJson($w['item_id']);
        // Send errors to the front end, if present.
        if (is_array($edan_json) && array_key_exists('error', $edan_json)) {
          // If an error is returned, throw a createNotFoundException (404).
          throw $this->createNotFoundException($edan_json['error']);
        } else {
          // Set the metaDataFile parameter and file name value for the Cook.
          if (!empty($edan_json)) $params['metaDataFile'] = 'metaDataFile.json';
        }
      }

      // Post the job to the processing service.
      $result = $this->processing->postJob($recipe['id'], $job_name, $params);

      // If the response http code isn't a 201, throw a createNotFoundException (404).
      if ($result['httpcode'] !== 201) throw $this->createNotFoundException('Error: The processing service returned HTTP code ' . $result['httpcode']);

      // Get the job data.
      $job = $this->processing->getJobByName($job_name);

      // If an error is returned, throw a createNotFoundException (404).
      if (isset($job['error']) && !empty($job['error'])) throw $this->createNotFoundException($job['error']);

      // TODO: Break-out to a function?
      // Log job data to the metadata storage
      $processing_job_id = $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => 'processing_job',
        'user_id' => $this->getUser()->getId(),
        'values' => array(
          'ingest_job_uuid' => $w['ingest_job_uuid'],
          'processing_service_job_id' => $job['id'],
          'recipe' =>  $job['recipe']['name'],
          'job_json' => json_encode($job),
          'state' => $job['state'],
          'asset_path' => $path[0]['asset_path'],
        )
      ));

      // Log the processing job's ID to the workflow record.
      $workflow_update = $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => 'workflow',
        'record_id' => $w['workflow_id'],
        'user_id' => $this->getUser()->getId(),
        'values' => array(
          'processing_job_id' => $job['id'],
        )
      ));

      // Interface data.
      $data = array(
        'action' => 'process',
        'header' => $step_name,
        'message' => 'The processing job has been initialized.',
        'processing_job_id' => $job['id'],
      );

    }

    return $data;
  }

  /**
   * @Route("/admin/workflow/{workflow_id}/go", name="workflow_step", methods={"GET","POST"}, defaults={"step_state"= NULL})
   * @Route("/admin/workflow/{workflow_id}/go/{step_state}", name="workflow_step_state", methods={"GET","POST"})
   * Set the status of a workflow
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function advanceWorkflow(Request $request) {

    $workflow_id = $request->attributes->get('workflow_id');
    $simulate_step_state = $request->attributes->get('step_state');

    $recipe_id = NULL;
    $query_params = array(
      'workflow_id' => $workflow_id
    );
    $workflow_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);
    $workflow_definition = $workflow_data['workflow_definition'];

    // Get the recipeId for the current, un-executed step.
    $workflow_definition_json_array = json_decode($workflow_definition, true);
    foreach($workflow_definition_json_array['steps'] as $step) {
      if($step['stepId'] == $workflow_data['step_id']) {
        $workflow_data['recipe_id'] = $recipe_id = $step['recipeId'];
        break;
      }
    }

    if(NULL == $recipe_id) {
      return $this->redirect('/admin/workflow/' . $workflow_id);
    }

    $recipe_step_state = NULL;
    switch($recipe_id) {
      case "test-success":
        $recipe_step_state = "success";
        break;
      case "test-fail":
        $recipe_step_state = "error";
        break;
    }

    // Favor the user's provided state.
    $new_step_state = isset($simulate_step_state) ? $simulate_step_state : (isset($recipe_step_state) ? $recipe_step_state : "success");
    $query_params = array(
      'workflow_id' => $workflow_id,
      'step_state' => $new_step_state,
      'user_id' => $this->getUser()->getId(),
    );
    $this->repo_storage_controller->execute('updateWorkflow', $query_params);

    if($new_step_state == "success") {
      // Get the next step.
      $query_params = array(
        'workflow_json_array' => $workflow_definition_json_array,
        'step_id' => $workflow_data['step_id']
      );
      $next_step_details = $this->repo_storage_controller->execute('getWorkflowNextStep', $query_params);

      if(isset($next_step_details['status']) && ($next_step_details['status'] == 'done')) {
        $query_params = array(
          'workflow_id' => $workflow_id,
          'step_state' => "done",
          'processing_job_id' => NULL,
        );
      }
      else {
        $query_params = array(
          'workflow_id' => $workflow_id,
          'step_id' => $next_step_details['stepId'],
          'step_type' => $next_step_details['stepType'],
          'step_state' => NULL,
          'processing_job_id' => NULL,
          'user_id' => $this->getUser()->getId(),
        );
      }
      // Update the workflow with the next step.
      $this->repo_storage_controller->execute('updateWorkflow', $query_params);

    }

    return $this->redirect('/admin/workflow/' . $workflow_id);
  }

  /**
   * Write QC Done File
   *
   * @param array $w Workflow data
   * @return bool
   */
  public function writeQcDoneFile($w = array())
  {
    $data = array();

    if (!empty($w)) {

      // Get the master model's path.
      $path = $this->getPathInfo($w);
      $directory = pathinfo($path[0]['asset_path'], PATHINFO_DIRNAME);
      $base_file_name = pathinfo($path[0]['asset_path'], PATHINFO_FILENAME);

      if (!empty($path)) {

        $type = isset($w['qc_hd_done']) ? 'hd' : null;
        $type = isset($w['qc_thumb_done']) ? 'thumb' : $type;

        // Web HD
        if (!empty($type)) {
          // Make sure the document.json exists before writing the 'done' file.
          if (is_file($directory . DIRECTORY_SEPARATOR . $base_file_name . '-document.json')) {
            // Move into the target directory.
            chdir($directory);
            $handle = fopen($directory . DIRECTORY_SEPARATOR . 'qc_' . $type . '_done.txt', 'w');
            // Write the 'done' file.
            fwrite($handle, '');
            if (is_resource($handle)) fclose($handle);
          }
        }

        // Web derivatives
        foreach ($this->derivatives as $key => $value) {
          // Check for the QC status to determine what's being set to 'done'.
          if (isset($w['qc_' . $key . '_done'])) {
            // Make sure the document.json exists before writing the 'done' file.
            // if (is_file($directory . DIRECTORY_SEPARATOR . $base_file_name . $value['json_file_name'])) {
              // Move into the target directory.
              chdir($directory);
              $handle = fopen($directory . DIRECTORY_SEPARATOR . 'qc_' . $key . '_done.txt', 'w');
              // Write the 'done' file.
              fwrite($handle, '');
              if (is_resource($handle)) fclose($handle);
            // }
          }
        }

      }

    }

    return $data;
  }




  /**
   * @Route("/admin/batch/detail", name="batch_detail_processing", methods="POST")
   * @param Request $request
   */
  public function batchDetailProcessing(Request $request) {
    $recipeID = $request->request->get('recipeID');
    $recipe = $this->processing->getRecipeDetails($recipeID);

    $recipeArray = [];
    if (!empty($recipe['result'])) {
      $recipeArray = json_decode($recipe['result'], true);
      $recipeArray['name'] = str_replace("-", " ",$recipeArray['name']);
      $recipeArray['name'] = ucwords($recipeArray['name']);
    }

    // use this if you want to dump the result and die :-)
    return new JsonResponse($recipeArray);
  }

  /**
 * @Route("/admin/batch/launch", name="batch_launch_processing", methods="POST")
 * @param Request $request
 */
  public function batchProcessingLaunch(Request $request) {

    $data = array();

    $filesystem = $this->container->get("oneup_flysystem.processing_filesystem");
    $recipe = $request->request->get("workflow");
    $workflow = explode(",", $recipe);
    // Need to replace spaces with dashes and convert the capitalized words to lower case.
    $workflow_name = strtolower(str_replace(' ', '-', $workflow[1]));
    $assets = $request->request->get("assets");
    $assets =  explode(",", $assets);

    $modelID = $request->request->get("modelID");
    $params = $request->request->get("params");
    $query_params = array(
      'file_id' => $assets[0],
    );

    $files = $this->repo_storage_controller->execute('getFile', $query_params);

    for ($i=0; $i < count($files); $i++) {
      // The path to the file.
      $local_path = $this->project_directory . DIRECTORY_SEPARATOR  . 'web' . $files[$i]['file_path'];
      // Windows path fix.
      $local_path = str_replace("/", DIRECTORY_SEPARATOR, $local_path);
      $parent_record_data = array('record_id' => $modelID, 'record_type' => 'model');

      // Initialize the processing job.
      // TODO: Since this is being called from a loop, this will need to return as a multi-dimentional array ( example: $data[] ).
      $data = $this->processing->initializeJob($workflow_name, $params, $local_path, $this->getUser()->getId(), $parent_record_data, $filesystem);
    }

    // On success, this is what's returned by initializeJob()
    // array(12) {
    //   ["id"]=>
    //   string(36) "A63B2CCE-969B-F065-0691-85000961D601"
    //   ["name"]=>
    //   string(20) "2018-12-05T19:32:42Z"
    //   ["clientId"]=>
    //   string(36) "7210f16c-d71a-4845-837f-b598ea38d36b"
    //   ["recipe"]=>
    //   array(4) {
    //     ["id"]=>
    //     string(36) "ee77ee05-d832-4729-9914-18a96939f205"
    //     ["name"]=>
    //     string(12) "inspect-mesh"
    //     ["description"]=>
    //     string(49) "Inspects a mesh and returns a report with results"
    //     ["version"]=>
    //     string(1) "1"
    //   }
    //   ["priority"]=>
    //   string(6) "normal"
    //   ["submission"]=>
    //   string(20) "2018-12-05T19:32:42Z"
    //   ["start"]=>
    //   string(0) ""
    //   ["end"]=>
    //   string(0) ""
    //   ["duration"]=>
    //   int(0)
    //   ["state"]=>
    //   string(7) "created"
    //   ["step"]=>
    //   string(0) ""
    //   ["error"]=>
    //   string(0) ""
    // }

    return new JsonResponse($data);
  }

  /**
 * @Route("/admin/batch/{model_id}/", name="batch_processing", methods="GET")
 * @param Request $request
 */
  public function batchProcessing(Request $request, $model_id) {
    $results = array();
    // Get available recipes.
    $results = $this->processing->getRecipes();
    // Decode the JSON.

    $query_params = array(
        'model_id' => $model_id,
    );

    $files = $this->repo_storage_controller->execute('getModelFiles', $query_params);

    // If no model files are found, throw a createNotFoundException (404).
    if(!$files) throw $this->createNotFoundException('Not found');

    $contacts = $this->repo_storage_controller->execute('getPointofContact');

    $json_decoded = json_decode($results['result'], true);
    for ($i=0; $i < count($json_decoded); $i++) {
      $json_decoded[$i]['name'] = str_replace("-", " ",$json_decoded[$i]['name']);
      $json_decoded[$i]['name'] = ucwords($json_decoded[$i]['name']);
    }

    // Create the form
    $batch['batch_processing_workflow_guid_picker'] = NULL;
    $batch['batch_processing_workflow_guid_options'] = NULL;
    $batch['batch_processing_assests_guid_options'] = NULL;
    $batch['batch_processing_assests_guid_picker'] = NULL;

    foreach ($json_decoded as $wk) {
      $batch['batch_processing_workflow_guid_options'][$wk['name']] = $wk['id'];
    }

    for ($i=0; $i < count($files); $i++) {
      $batch['batch_processing_assests_guid_options'][$files[$i]['file_name']] = $files[$i]['file_upload_id'].",".$files[$i]['file_name'];
    }

    $form = $this->createForm(BatchProcessingForm::class, $batch, array(
            'action' => '/admin/batch/review',
            'method' => 'POST',
        ));

    // Handle the request
    $form->handleRequest($request);

    return $this->render('workflow/batch_processing_form.html.twig', array(
      'page_title' => 'Batch Processing',
      'workflows'=>$json_decoded,
      'modelID'=>$model_id,
      'contacts'=>$contacts,
      //'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      'form' => $form->createView(),
    ));

  }

}
