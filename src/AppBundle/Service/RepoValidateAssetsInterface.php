<?php

namespace AppBundle\Service;

/**
 * Interface for asset validation.
 */

interface RepoValidateAssetsInterface {

  /**
   * Validate Assets
   * @param array  $params  Parameters. For now, only 'localpath' is being sent.
   * @return array 
   */
  public function validate_assets(array $params);

  /**
   * Validate Images
   * @param array $localpath The local path to uploaded assets.
   * @return array containing success/fail value, and any messages.
   */
  public function validate_images(string $localpath);

  /**
   * Validate Image Pairs
   * @param array $data The data to validate.
   * @param string $job_status The job status string.
   * @return array containing success/fail value, and any messages.
   */
  public function validate_image_pairs(array $data, string $job_status);

  /**
   * Get Mime Type
   *
   * @param string  $filename  The file name
   * @return string
   */
  public function getMimeType(string $filename);

}
