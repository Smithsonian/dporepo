<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class WorkflowController extends Controller {

  private $repo_storage_controller;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(AppUtilities $u, Connection $conn)
  {
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
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

}