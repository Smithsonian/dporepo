<?php

namespace AppBundle\Service;

/**
 * Interface for file transfers.
 */

interface RepoFileTransferInterface {

  /**
   * @param $target_directory The directory which contains files to be transferred.
   * @param $destination The destination.
   * @param $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return mixed array containing success/fail value, and any messages.
   */
  public function transferFiles($target_directory, $filesystem);

}
