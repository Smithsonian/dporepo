<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use AppBundle\Service\RepoProcessingService;
// Custom utility bundle
use AppBundle\Utils\AppUtilities;
use AppBundle\Form\BatchProcessingForm;
use AppBundle\Form\WorkflowParamatersForm;
class WorkflowController extends Controller {

  private $repo_storage_controller;
  /**
   * @var object $processing
   */
  private $processing;
  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(AppUtilities $u, Connection $conn,RepoProcessingService $processing)
  {
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->processing = $processing;

  }


  /**
   * @Route("/workflow/status/set", name="workflow_status_set", methods="POST")
   * Given a record_type and record_id, set the status details as indicated.
   *
   */
  public function set_workflow_status(Request $request)
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
  public function get_workflow_status(Request $request)
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
   * @Route("/admin/workflow/{project_id}/batch-processing/", name="batch_processing", methods="GET")
   * Given a record_id, record_type and values array, create or edit a record, and update the workflow status log.
   *
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function batchProcessing(Request $request,$project_id) {
    $results = array();
    // Get available recipes.
    $results = $this->processing->get_recipes();
    // Decode the JSON.

    $query_params = array(
        'project_repository_id' => $project_id,
    );
    $datasets = $this->repo_storage_controller->execute('getDatasets', $query_params);

    if ($results['result'] == false) {
      $results['result'] = '[{"id":"7ce5c5b1-00d2-4d7f-bebc-ea99ae5f6640","name":"decimate","description":"Decimate high poly mesh","version":"4"},{"id":"ee77ee05-d832-4729-9914-18a96939f205","name":"inspect-mesh","description":"Inspects a mesh and returns a report with results","version":"1"},{"id":"e06ade8e-b36a-4aa2-9145-6616ede1e5fa","name":"rc-to-hd","description":"[DRAFT!] Converts RC output files to HD web assets","version":"1"},{"id":"19f06147-d460-4e47-a55d-2b58dc84a4ab","name":"rc-to-play","description":"[DRAFT!] Converts RC output to PLAY assets, including mesh, textures and descriptor file","version":"1"},{"id":"967ed977-055e-41c8-a836-b1372be3b3ca","name":"unwrap","description":"Unwrap decimated mesh using Unfold","version":"2"},{"id":"1c795703-8ef9-4392-8a68-bb8680209516","name":"vz-to-play","description":"VZ Collection CT mesh to Web, decimate (preserve bounds, topo), fix, unwrap, and bake, generate PLAY-ready assets","version":"9"},{"id":"c3825c38-27ab-4909-8d9e-928182199c03","name":"web-hd","description":"Generates high definition (1M, 8k) web asset","version":"2"},{"id":"721d459c-af09-4525-a28b-e71a89439282","name":"web-multi","description":"Generates multi-level web assets","version":"3"},{"id":"05debd35-efab-40d4-9145-cb6d819d1859","name":"web-thumb","description":"Generates thumbnail web asset","version":"3"}]';
    }
    $json_decoded = json_decode($results['result'], true);

    // Create the form
    $batch['batch_processing_workflow_guid_picker'] = NULL;
    $batch['batch_processing_workflow_guid_options'] = NULL;
    $batch['batch_processing_assests_guid_options'] = NULL;
    $batch['batch_processing_assests_guid_picker'] = NULL;

    foreach ($json_decoded as $wk) {
      $batch['batch_processing_workflow_guid_options'][$wk['name']] = $wk['id'];
    }
    foreach ($datasets as $data) {
      $batch['batch_processing_assests_guid_options'][$data['capture_dataset_name']] = $data['parent_project_repository_id'];
    }
    $form = $this->createForm(BatchProcessingForm::class, $batch);

    // Handle the request
    $form->handleRequest($request);
    return $this->render('workflow/batch_processing_form.html.twig', array(
            'page_title' => 'Batch Processing',
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