<?php

namespace AppBundle\Service;

/**
 * Interface for processing 3D assets.
 */

interface RepoProcessingServiceInterface {

  /**
   * Get recipes
   *
   * @return array
   */
  public function get_recipes();

  /**
   * Get recipe by name
   *
   * @param string $recipe_name
   * @return array
   */
  public function get_recipe_by_name(string $recipe_name);

  /**
   * Post job
   *
   * @param string $recipe_id
   * @param string $job_name
   * @param array $params
   * @return array
   */
  public function post_job(string $recipe_id, string $job_name, array $params);

  /**
   * Run job
   *
   * @param $job_id
   * @return array
   */
  public function run_job(string $job_id);

  /**
   * Cancel job
   *
   * @param $job_id
   * @return array
   */
  public function cancel_job(string $job_id);

  /**
   * Delete job
   *
   * @param $job_id
   * @return array
   */
  public function delete_job(string $job_id);

  /**
   * Get job
   *
   * @param $job_id
   * @return array
   */
  public function get_job(string $job_id);

  /**
   * Get jobs
   *
   * @return array
   */
  public function get_jobs();

  /**
   * Get job by name
   *
   * @param string $job_name
   * @return array
   */
  public function get_job_by_name(string $job_name);

  /**
   * Retrieve the server machine state
   *
   * @return array
   */
  public function machine_state();

  /**
   * Query API
   *
   * @param array $params
   * @param bool $return_output
   * @param string $method
   * @param array $post_params
   * @param string $content_type
   * @return array
   */
  public function query_api(array $params, string $method, array $post_params, bool $return_output, string $content_type);

  /**
   * See if a job or set of jobs are running.
   *
   * @param array $job_ids An array of job ids
   * @return bool
   */
  public function are_jobs_running(array $job_ids);

  /**
   * Get processing assets.
   *
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @param string $job_id The processing service job ID.
   * @return bool
   */
  public function get_processing_assets(obj $filesystem, string $job_id);

  /**
   * @param string $recipe The processing service recipe.
   * @param array $params Parameters for the processing service.
   * @param string $path The path to the assets to be processed.
   * @param string $user_id The ID of the repository user.
   * @param array $parent_record_data The repository parent record type and ID.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function initialize_job(string $recipe, array $params, string $path, string $user_id, array $project_data, obj $filesystem);

  /**
   * @param string $path The path to the assets to be processed.
   * @param string $recipe The processing service recipe.
   * @param string $user_id The ID of the repository user.
   * @param array $params Parameters for the processing service.
   * @param array $parent_record_data The repository parent record type and ID.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function send_job(string $path, string $recipe, string $user_id, array $params, $project_data = array(), obj $filesystem);

  /**
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function execute_job(obj $filesystem);

  /**
   * Get Processing Results
   *
   * @param string $job_id The processing service job ID
   * @param string $user_id The user's repository ID
   * @param string $path The path to the assets to be processed.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return 
   */
  public function get_processing_results(string $job_id, string $user_id, string $path, $filesystem);

  /**
   * Create GUID
   *
   * @return string
   */
  public function create_guid();

}
