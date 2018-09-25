<?php

namespace AppBundle\Service;

/**
 * Interface for processing 3D assets.
 */

interface RepoProcessingServiceInterface {

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
