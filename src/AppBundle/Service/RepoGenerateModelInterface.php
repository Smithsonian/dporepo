<?php

namespace AppBundle\Service;

/**
 * Interface for model generation.
 */

interface RepoGenerateModelInterface {

  /**
   * @param string $uuid The directory (UUID) which contains model(s) to be validated and processed.
   * @param string $recipe_name The processing recipe name.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function generateModelAssets(string $uuid, string $recipe_name, obj $filesystem);

  /**
   * @param string $path The directory which contains model(s) to be validated and processed.
   * @param array $job_data The repository job's data.
   * @param string $recipe_name The processing recipe name.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function runProcessingJob(string $path, array $job_data, string $recipe_name, obj $filesystem);

}
