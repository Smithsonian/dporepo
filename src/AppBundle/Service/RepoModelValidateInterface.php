<?php

namespace AppBundle\Service;

/**
 * Interface for model validation.
 */

interface RepoModelValidateInterface {

  /**
   * @param $target_directory The directory which contains model(s) to be validated and processed.
   * @param $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function validate_models(string $target_directory, obj $filesystem);

}
