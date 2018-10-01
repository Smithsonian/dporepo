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
   * Post job
   *
   * @param string $recipe_id
   * @param string $job_name
   * @param string $file_name
   * @return array
   */
  public function post_job(string $recipe_id, string $job_name, string $file_name);

  /**
   * Start job
   *
   * @param $job_id
   * @return array
   */
  public function start_job(string $job_id);

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

}
