<?php

namespace AppBundle\Service;

/**
 * Interface for model validation.
 */

interface RepoModelValidateInterface {

  /**
   * @param string $target_directory The directory which contains model(s) to be validated and processed.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function validate_models(string $target_directory, obj $filesystem);

  /**
   * @param string $path The directory which contains model(s) to be validated and processed.
   * @param array $job_data The repository job's data.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function run_validate_models(string $path, array $job_data, obj $filesystem);

}
