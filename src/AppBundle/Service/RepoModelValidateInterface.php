<?php

namespace AppBundle\Service;

/**
 * Interface for model validation.
 */

interface RepoModelValidateInterface {

  /**
   * @param string $uuid The directory (UUID) which contains model(s) to be validated and processed.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function validateModels(string $uuid, obj $filesystem);

  /**
   * @param string $path The directory which contains model(s) to be validated and processed.
   * @param array $job_data The repository job's data.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function runValidateModels(string $path, array $job_data, obj $filesystem);

}
