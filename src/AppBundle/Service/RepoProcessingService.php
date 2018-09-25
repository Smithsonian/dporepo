<?php

namespace AppBundle\Service;

use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Finder\Finder;

use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Utils\AppUtilities;

class RepoProcessingService implements RepoProcessingServiceInterface {

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
  // private $project_directory;

  /**
   * @var string $external_file_storage_path
   */
  private $external_file_storage_path;

  /**
   * @var string $processing_service_location
   */
  private $processing_service_location;

  /**
   * @var object $conn
   */
  private $conn;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * Constructor
   * @param object  $kernel  Symfony's kernel object
   * @param string  $external_file_storage_path  External file storage path
   * @param string  $processing_service_location  Processing service location (e.g. URL)
   * @param string  $conn  The database connection
   */
  public function __construct(KernelInterface $kernel, string $external_file_storage_path, string $processing_service_location, \Doctrine\DBAL\Connection $conn)
  {
    $this->u = new AppUtilities();
    // $this->u->dumper('hello');
    $this->kernel = $kernel;
    // $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    // $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    $this->external_file_storage_path = $external_file_storage_path;
    $this->processing_service_location = $processing_service_location;
    $this->conn = $conn;
    // $this->repo_storage_controller = new RepoStorageHybridController($conn);
  }

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
  public function query_api($params = array(), $method = 'get', $post_params = array(), $return_output = true, $content_type = 'Content-type: application/json; charset=utf-8')
  {
    // $this->u->dumper($method,0);
    // $this->u->dumper($params,0);
    // $this->u->dumper(json_encode($post_params));

    $data = array();

    // Make sure parameters are passed.
    if (is_array($params) && !empty($params)) {

      if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
      }

      $url_path = implode('/', $params);
      // $this->u->dumper($this->processing_service_location . $url_path);

      $ch = curl_init($this->processing_service_location . $url_path);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array($content_type));
      curl_setopt($ch, CURLINFO_HEADER_OUT, TRUE);

      switch ($method) {
        case "post":
          // $this->u->dumper('posting');
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_params));
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
          break;
        case "delete":
          // $this->u->dumper('deleting');
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
          break;
      }

      // Return output.
      if($return_output) $data['output'] = curl_exec($ch);
      // Suppress output.
      if(!$return_output) curl_exec($ch);
      // Return the HTTP code.
      $data['httpcode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $data['response_header'] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
      curl_close($ch);

    }

    return $data;
  }

}