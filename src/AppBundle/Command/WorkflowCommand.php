<?php

namespace AppBundle\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArrayInput;
use AppBundle\Controller\RepoStorageHybridController;

class WorkflowCommand extends ContainerAwareCommand
{
  protected $container;
  private $repo_storage_controller;

  public function __construct(object $conn)
  {
    // Storage controller
    $this->repo_storage_controller = new RepoStorageHybridController($conn);

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
    $query_params = array('step_type' => 'auto', 'step_state' => NULL);
    $workflows_data = $this->repo_storage_controller->execute('getWorkflows', $query_params);

    // For each of these, kick off the job,
    // and write back to the workflow table the job ID, and step state = created
    foreach($workflows_data as $workflow_data) {
      $this->launch_job($workflow_data);
    }

  }

  private function launch_job($workflow) {

    // $workflow contains an array of the row from the workflow table.
    // $workflow['workflow_definition'] contains the JSON definition of the workflow in use
    // $workflow['steps'] contains the steps, and each step has a stepId and optionally a recipeId.

    $recipe_id = NULL;

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

    switch($recipe_id) {
      case "web-master":
        //@todo kick off recipe
        break;
      case "web-hd":
        //@todo kick off recipe
        break;
      case "web-multi":
        //@todo kick off recipe
        break;
      case "test-success":
        // Pretend like we just kicked off this test processing recipe.
        break;
      case "test-fail":
        // Pretend like we just kicked off this test processing recipe.
        break;
    }

    $query_params = array(
      'workflow_id' => $workflow['workflow_id'],
      'step_state' => 'created'
    );
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
      $this->complete_job($workflow_data);
    }
  }

  private function complete_job($workflow) {
    $recipe_id = NULL;

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
        break;
      case "web-hd":
        //@todo complete job
        break;
      case "web-multi":
        //@todo complete job
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
        'job_id' => NULL,
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