<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpKernel\KernelInterface;

use AppBundle\Service\RepoProcessingService;
use AppBundle\Controller\RepoStorageHybridController;

use AppBundle\Form\BatchProcessingForm;
use AppBundle\Form\WorkflowParamatersForm;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

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
   * @var object $processing
   */
  private $processing;

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
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(AppUtilities $u, Connection $conn, RepoProcessingService $processing, KernelInterface $kernel, string $uploads_directory)
  {
    $this->u = $u;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->processing = $processing;
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR  . 'web';
  }

  /**
   * @Route("/workflow/status/set", name="workflow_status_set", methods="POST")
   * Given a record_type and record_id, set the status details as indicated.
   *
   */
  public function setWorkflowStatus(Request $request)
  {

    $req = $request->request->all();

    $record_type = !empty($req['record_type']) ? $req['record_type'] : '';
    $record_id = !empty($req['record_id']) ? $req['record_id'] : '';

    $project_id = !empty($req['project_id']) ? $req['project_id'] : '';

    $workflow_id = !empty($req['workflow_id']) ? $req['workflow_id'] : '';
    $processing_step = !empty($req['processing_step']) ? $req['processing_step'] : '';
    $status = !empty($req['status']) ? $req['status'] : '';
    $status_detail = !empty($req['status_detail']) ? $req['status_detail'] : '';

    $user_id = $this->getUser()->getId();

    $query_params = array(
      'record_type' => $record_type,
      'record_id' => $record_id,
      'project_id' => $project_id,
      'workflow_id' => $workflow_id,
      'processing_step' => $processing_step,
      'status' => $status,
      'status_detail' => $status_detail,
      'user_id' => $user_id,
    );

    $data = $this->repo_storage_controller->execute('setWorkflowProcessingStatus', $query_params);

    return $this->json($data);
  }

  /**
   * @Route("/workflow/status/get", name="workflow_status_get", methods="GET")
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function getWorkflowStatus(Request $request)
  {
    $req = $request->query->all();

    $record_type = !empty($req['record_type']) ? $req['record_type'] : '';
    $record_id = !empty($req['record_id']) ? $req['record_id'] : '';

    $query_params = array(
      'record_type' => $record_type,
      'record_id' => $record_id,
    );

    $data = $this->repo_storage_controller->execute('getWorkflowProcessingStatus', $query_params);

    return $this->json($data);
  }


  /**
   * @Route("/workflow/record/write", name="workflow_record_write", methods="POST")
   * Given a record_id, record_type and values array, create or edit a record, and update the workflow status log.
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function writeRecord(Request $request) {

    $req = $request->request->all();

    $record_type = !empty($req['record_type']) ? $req['record_type'] : '';
    $record_id = !empty($req['record_id']) ? $req['record_id'] : '';
    $record_values = !empty($req['record_values']) ? $req['record_values'] : array();

    $uid = is_object($this->getUser()) ? $this->getUser()->getId() : 0;

    $id = $this->repo_storage_controller->execute('saveRecord', array(
      'base_table' => $record_type,
      'record_id' => $record_id,
      'user_id' => $uid,
      'values' => $record_values,
    ));

    $data = array();

    if($record_type == 'model' || $record_type == 'capture_dataset') {
      if(array_key_exists('workflow_processing_step', $record_values)) {

        // Write status log, for model or capture_dataset.
        $workflow_id = array_key_exists('workflow_id', $record_values) ? $record_values['workflow_id'] : 0;
        $processing_step = array_key_exists('workflow_processing_step', $record_values) ? $record_values['workflow_processing_step'] : '';
        $status = array_key_exists('workflow_status', $record_values) ? $record_values['workflow_status'] : '';
        $status_detail = array_key_exists('workflow_status_detail', $record_values) ? $record_values['workflow_status_detail'] : '';
        $created_by_user_account_id = array_key_exists('created_by_user_account_id', $record_values) ? $record_values['created_by_user_account_id'] : 0;

        $status_values = array(
          'record_id' => isset($id) ? $id : $record_id,
          'record_type' => $record_type,
          'workflow_id' => $workflow_id,
          'processing_step' => $processing_step,
          'status' => $status,
          'status_detail' => $status_detail,
        );

        $log_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'workflow_status_log',
          'user_id' => ($uid > 0) ? $uid : $created_by_user_account_id,
          'values' => $status_values,
        ));

        $data = array(
          'record_id' => $id,
          'record_type' => $record_type,
          'record_values' => $record_values,
          'workflow_status_log_id' => $log_id,
          'workflow_details' => $status_values,
        );

      }
    }

    return $this->json($data);
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
        $local_path = $this->project_directory . $files[$i]['file_path'];
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
    /**
   * @Route("/admin/workflow/{project_id}/batch-processing/{workflow_id}", name="workflow_batch_processing", methods="GET")
   * Given a record_id, record_type and values array, create or edit a record, and update the workflow status log.
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function WorkflowBatchProcessing(Request $request,$project_id,$workflow_id) {
    
    $workflow['point_of_contact_guid_options'] = array("Anderson, Max"=>0,"Blundell, Jon"=>1,"Conrad, Joe"=>2,"Dattoria, Megan"=>3);
    $workflow['point_of_contact_guid_picker'] = NULL;

    $form = $this->createForm(WorkflowParamatersForm::class, $workflow);

    // Handle the request
    $form->handleRequest($request);
    return $this->render('workflow/workflow_parameters.html.twig', array(
      'page_title' => 'Workflow Parameters',
      //'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      'form' => $form->createView(),
    ));

  }

}

// This is one way to check for the status of a processing job or multiple processing jobs

// // Check to see if jobs are running. Don't pass "Go" until all jobs are finished.
// while ($this->processing->are_jobs_running($processing_job['job_ids'])) {
//   $this->processing->are_jobs_running($processing_job['job_ids']);
//   sleep(5);
// }

// // Retrieve all of the logs produced by the processing service.
// foreach ($processing_job['job_ids'] as $job_id_value) {
//   $processing_assets[] = $this->processing->get_processing_assets($filesystem, $job_id_value);
// }