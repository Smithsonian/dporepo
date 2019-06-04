<?php

namespace AppBundle\Service;

/**
 * Interface for processing 3D assets.
 */

interface RepoProcessingServiceInterface {

  /**
   * Is Service Accessible
   *
   * @return bool
   */
  public function isServiceAccessible();

  /**
   * Get recipes
   *
   * @return array
   */
  public function getRecipes();

  /**
   * Get recipe by name
   *
   * @param string $recipe_name
   * @return array
   */
  public function getRecipeByName($recipe_name = '');

  /**
   * Post job
   *
   * @param string $recipe_id
   * @param string $job_name
   * @param array $params
   * @return array
   */
  public function postJob($recipe_id = '', $job_name = '', $params = array());

  /**
   * Run job
   *
   * @param $job_id
   * @return array
   */
  public function runJob($job_id = '');

  /**
   * Cancel job
   *
   * @param $job_id
   * @return array
   */
  public function cancelJob($job_id = '');

  /**
   * Delete job
   *
   * @param $job_id
   * @return array
   */
  public function deleteJob($job_id = '');

  /**
   * Get job
   *
   * @param $job_id
   * @return array
   */
  public function getJob($job_id = '');

  /**
   * Get jobs
   *
   * @return array
   */
  public function getJobs();

  /**
   * Get job by name
   *
   * @param string $job_name
   * @return array
   */
  public function getJobByName($job_name = '');

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
  public function query_api($params = array(), $method = '', $post_params = array(), $return_output, $content_type = '');

  /**
   * See if a job or set of jobs are running.
   *
   * @param array $job_ids An array of job ids
   * @return bool
   */
  public function are_jobs_running($job_ids = array());

  /**
   * Get processing assets.
   *
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @param string $job_id The processing service job ID.
   * @return bool
   */
  public function get_processing_assets($filesystem, $job_id = '');

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
  public function initializeJob($recipe = '', $params = array(), $path = '', $user_id = '', $project_data = array(), $filesystem);

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
  public function sendJob($path = '', $recipe = '', $user_id = '', $params = array(), $project_data = array(), $filesystem);

  /**
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function executeJob($filesystem);

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
  public function getProcessingResults($job_id = '', $user_id = '', $path = '', $filesystem);

  /**
   * Get UV Map
   *
   * @param string $asset_path The path to the model
   * @return string
   */
  public function getUvMap($asset_path = '');

}
