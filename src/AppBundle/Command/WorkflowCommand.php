<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Service\RepoProcessingService;
use AppBundle\Utils\AppUtilities;

class WorkflowCommand extends ContainerAwareCommand
{
  protected $container;
  private $repo_storage_controller;
  private $processing;
  public $u;

  public function __construct(object $conn, RepoProcessingService $processing)
  {
    // Storage controller
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    // Processing service.
    $this->processing = $processing;
    // App Utilities
    $this->u = new AppUtilities();
    // This is required due to parent constructor, which sets up name.
    parent::__construct();
  }

  protected function configure()
  {
    $this
      // The name of the command (the part after "bin/console").
      ->setName('app:workflow')
      // The short description shown while running "php bin/console list".
      ->setDescription('Launch and update workflows.')
      // The full command description shown when running the command with
      // the "--help" option.
      ->addArgument('action', InputArgument::REQUIRED, 'Action- update or complete.')
      ->setHelp('These commands handle processing workflows.');
      // Add arguments...
      //->addArgument('local_file_path', InputArgument::REQUIRED, 'Full path of file on local filesystem.')
      //->addArgument('destination_file_path', InputArgument::REQUIRED, 'Relative path and filename for destination.');
  }

  protected function execute(InputInterface $input, OutputInterface $output) {
    $action = $input->getArgument('action');

    if($action == 'update') {
      $this->update($input, $output);
    }
    elseif($action == 'complete') {
      $this->complete($input, $output);
    }

  }

  protected function update(InputInterface $input, OutputInterface $output) {

    // Look in workflow table for steps where step type=auto and step state is null.
    $query_params = array('step_type' => 'auto', 'step_state' => 'null');
    $workflows_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);

    // For each of these, kick off the job,
    // and write back to the workflow table the job ID, and step state = created
    foreach($workflows_data as $workflow_data) {
      $this->launchJob($workflow_data);
    }

  }

  private function launchJob($workflow) {

    // $workflow contains an array of the row from the workflow table.
    // $workflow['workflow_definition'] contains the JSON definition of the workflow in use
    // $workflow['workflow_definition']['steps'] contains the steps, and each step has a stepId and optionally a recipeId.

    $recipe_id = NULL;

    // Set up flysystem.
    $container = $this->getContainer();
    $flysystem = $container->get('oneup_flysystem.processing_filesystem');

    // Get the recipeId for the current, un-executed step.
    $workflow_definition = $workflow['workflow_definition'];
    $workflow_definition_json_array = json_decode($workflow_definition, true);
    foreach($workflow_definition_json_array['steps'] as $step) {
      if($step['stepId'] == $workflow['step_id']) {
        $workflow['recipe_id'] = $recipe_id = $step['recipeId'];
        break;
      }
    }
    if(NULL == $recipe_id) {
      // If the step doesn't have a recipeId, we don't have anything to launch.
      return;
    }

    // echo '<pre>';
    // var_dump($recipe_id);
    // echo '</pre>';
    // die();

    // By default we set the step_state to created.
    $query_params = array(
      'workflow_id' => $workflow['workflow_id'],
      'step_state' => 'created'
    );

    switch($recipe_id) {
      case "web-master":
        //@todo kick off recipe
        //@todo get the job_id, and put it in $query_params
        break;
      case "web-hd":
        //@todo kick off recipe
        //@todo get the job_id, and put it in $query_params
        // $data = $this->processing->initializeJob($workflow_name, $params, $local_path, $this->getUser()->getId(), $parent_record_data, $filesystem);
        break;
      case "web-multi":
        //@todo kick off recipe
        //@todo get the job_id, and put it in $query_params
        break;
      case "test-success":
        // Pretend like we just kicked off a test processing recipe.
        break;
      case "test-fail":
        // Pretend like we just kicked off a test processing recipe.
        break;
    }

    // Update the workflow record. Set the step state to created.
    $this->repo_storage_controller->execute('updateWorkflow', $query_params);

  }

  protected function complete(InputInterface $input, OutputInterface $output) {

    // Look in workflow table for steps where step type=auto and step state = created or processing.
    $query_params = array('step_type' => 'auto', 'step_state' => 'created');
    $created_workflows_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);

    $query_params = array('step_type' => 'auto', 'step_state' => 'processing');
    $workflows_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);

    $workflows_data = array_merge($created_workflows_data, $workflows_data);

    // For each workflow, check the job status.
    foreach($workflows_data as $workflow_data) {
      $this->completeJob($workflow_data);
    }
  }

  private function completeJob($workflow) {
    $recipe_id = NULL;

    // This is where we poll for the job.

    //  If cook says the job is processing, set step state=processing (this is just for jobs that formerly had a state of "created").
    //  If cook says the job is done, handle completion (whatever else has to be done in the repo for this step) and update the workflow table:
    //      If all workflow steps are done, mark step status=done (should we make any other changes to the workflow table here? should we use a different status, like "workflow_done"?).
    //      Or if there is a next step, set the next step's values in the workflow table (ID of step, state=null, step type, job id=null).
    //  If cook reports error or failed, update workflow table (step state=error or failed)

    // Get the recipeId for the current, un-executed step.
    $workflow_definition = $workflow['workflow_definition'];
    $workflow_definition_json_array = json_decode($workflow_definition, true);
    foreach($workflow_definition_json_array['steps'] as $step) {
      if($step['stepId'] == $workflow['step_id']) {
        $workflow['recipe_id'] = $recipe_id = $step['recipeId'];
        break;
      }
    }
    if(NULL == $recipe_id) {
      return;
    }
    $step_state = $workflow['step_state'];

    switch($recipe_id) {
      case "web-master":
        //@todo complete job
        // poll for job completion
        // If completed
        //    handle completion- metadata storage or whatever
        //    set $step_state to success if done, or fail if error/fail
        break;
      case "web-hd":
        //@todo complete job
        // poll for job completion
        // If completed
        //    handle completion- metadata storage or whatever
        //    set $step_state to success if done, or fail if error/fail
        break;
      case "web-multi":
        //@todo complete job
        // poll for job completion
        // If completed
        //    handle completion- metadata storage or whatever
        //    set $step_state to success if done, or fail if error/fail
        break;
      case "test-success":
        // Pretend like this step succeeded.
        $step_state = "success";
        break;
      case "test-fail":
        // Pretend like this step failed. And that's it, workflow stalled.
        $step_state = "error";
        break;
    }

    if($step_state == "success") {
      $query_params = array(
        'workflow_id' => $workflow['workflow_id'],
        'step_state' => $step_state
      );
      $this->repo_storage_controller->execute('updateWorkflow', $query_params);

      // Get the next step.
      $query_params = array(
        'workflow_json_array' => $workflow_definition_json_array,
        'step_id' => $workflow['step_id']
      );
      $next_step_details = $this->repo_storage_controller->execute('getWorkflowNextStep', $query_params);

      // Update the workflow with the next step.
      $query_params = array(
        'workflow_id' => $workflow['workflow_id'],
        'step_id' => $next_step_details['stepId'],
        'step_type' => $next_step_details['stepType'],
        'step_state' => NULL,
        'processing_job_id' => NULL,
      );
      $this->repo_storage_controller->execute('updateWorkflow', $query_params);
    }
    else {
      //@todo what to do?
      $query_params = array(
        'workflow_id' => $workflow['workflow_id'],
        'step_state' => $step_state
      );
      $this->repo_storage_controller->execute('updateWorkflow', $query_params);
    }

  }


}