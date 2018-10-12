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
   * @param string $file_name
   * @return array
   */
  public function post_job(string $recipe_id, string $job_name, string $file_name);

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
   * See if a job or set of jobs are running.
   *
   * @param array $job_ids An array of job ids
   * @return bool
   */
  public function are_jobs_running(array $job_ids);

  /**
   * Get processing assets.
   *
   * @param array $job_ids An array of job ids
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return bool
   */
  public function get_processing_assets(array $job_ids, obj $filesystem);

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
   * Create GUID
   *
   * @return string
   */
  public function create_guid();

}
