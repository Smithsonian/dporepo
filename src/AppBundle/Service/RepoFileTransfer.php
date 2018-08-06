<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class RepoFileTransfer implements RepoFileTransferInterface {

  /**
   * @var object $u
   */
  public $u;

  /**
   * @var object $kernel
   */
  public $kernel;

  /**
   * @var string $project_directory
   */
  private $project_directory;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  // , string $external_file_storage_location, string $external_file_storage_protocol, string $external_file_storage_username, string $external_file_storage_password
  public function __construct(KernelInterface $kernel, string $uploads_directory)
  {
    $this->u = new AppUtilities();
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
  }

  /**
   * @param $target_directory The directory which contains files to be transferred.
   * @param $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return mixed array containing success/fail value, and any messages.
   */
  // Create Directories
  // $response = $filesystem->createDir('data2');
  // $this->u->dumper($response);
  // Check if a file exists
  // $exists = $filesystem->has('data2');
  // $this->u->dumper($exists);
  //
  // createDir
  // write
  // read
  // update
  // listContents
  // delete
  // getTimestamp
  public function transferFiles($target_directory = null, $filesystem = null)
  {

    $file_path = $target_directory . DIRECTORY_SEPARATOR . 'data2' . DIRECTORY_SEPARATOR . '_RA_7654.jpg';
    $path = $this->project_directory . $this->uploads_directory . $file_path;

    // If a file exists, catch the error.
    if ($filesystem->has($path)) {
      $this->u->dumper('Error: file already exists');
    }

    // If a file does not exist, write the file.
    if (!$filesystem->has($path)) {

      $fh = fopen($path, 'rb');
      // Write the file
      $response = $filesystem->write($path, $fh);
      fclose($fh);
      // Catch error.
      if (!$response) {
        $this->u->dumper('Error: could not not transfer file');
      } else {
        $this->u->dumper('Success: file has been transferred');
      }

    }

  }

}