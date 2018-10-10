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
   * @var string $processing_service_client_id
   */
  private $processing_service_client_id;

  /**
   * @var object $conn
   */
  private $conn;

  /**
   * @var object $repo_storage_controller
   */
  // private $repo_storage_controller;

  /**
   * @var object $repo_storage_controller
   */
  private $client_id;

  /**
   * Constructor
   * @param object  $kernel  Symfony's kernel object
   * @param string  $external_file_storage_path  External file storage path
   * @param string  $processing_service_location  Processing service location (e.g. URL)
   * @param string  $processing_service_client_id  Processing service client ID
   * @param string  $conn  The database connection
   */
  public function __construct(KernelInterface $kernel, string $external_file_storage_path, string $processing_service_location, string $processing_service_client_id, \Doctrine\DBAL\Connection $conn)
  {
    $this->u = new AppUtilities();
    // $this->u->dumper('hello');
    $this->kernel = $kernel;
    // $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    // $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    $this->external_file_storage_path = $external_file_storage_path;
    $this->processing_service_location = $processing_service_location;
    $this->processing_service_client_id = $processing_service_client_id;
    $this->conn = $conn;
    // $this->repo_storage_controller = new RepoStorageHybridController($conn);
  }

  /**
   * Get recipes
   *
   * @return array
   */
  public function get_recipes() {

    $data = array();

    $params = array(
      'recipes',
    );

    $data = $this->query_api($params, 'GET');

    return $data;
  }

  /**
   * Post job
   *
   * @param string $recipe_id
   * @param string $job_name
   * @param string $file_name
   * @return array
   */
  public function post_job($recipe_id = null, $job_name = null, $file_name = null) {

    $data = array();

    if (empty($recipe_id) || empty($job_name) || empty($file_name)) {
      $data['error'] = 'Error: Missing parameter(s). Required parameters: recipe_id, job_name, file_name';
    }

    // If there are no errors, executte the API call.
    if (empty($data['error'])) {

      $params = array(
        'job',
      );

      $post_params = array(
        'id' => $this->create_guid(),
        'name' => $job_name,
        'clientId' => $this->processing_service_client_id,
        'recipeId' => $recipe_id,
        'parameters' => array(
          'meshFile' => $file_name
        ),
        'priority' => 'normal',
        'submission' => str_replace('+00:00', 'Z', gmdate('c', strtotime('now'))),
      );

      // API returns 200 for a successful POST,
      // and a 404 for an unsuccessful POST. 
      $data = $this->query_api($params, 'POST', $post_params);
    }

    return $data;
  }

  /**
   * Run job
   *
   * @param $job_id
   * @return array
   */
  public function run_job($job_id = null) {

    $data = array();

    if (empty($job_id)) $data['error'] = 'Error: Missing parameter. Required parameters: job_id';

    // If there are no errors, executte the API call.
    if (empty($data['error'])) {

      // /clients/{clientId}/jobs/{jobId}/run
      $params = array(
        'clients',
        $this->processing_service_client_id,
        'jobs',
        $job_id,
        'run'
      );

      $data = $this->query_api($params, 'PATCH');
    }

    return $data;
  }

  /**
   * Cancel job
   *
   * @param $job_id
   * @return array
   */
  public function cancel_job($job_id = null) {

    $data = array();

    if (empty($job_id)) $data['error'] = 'Error: Missing parameter. Required parameters: job_id';

    // If there are no errors, executte the API call.
    if (empty($data['error'])) {

      // /clients/{clientId}/jobs/{jobId}/cancel
      $params = array(
        'clients',
        $this->processing_service_client_id,
        'jobs',
        $job_id,
        'cancel'
      );

      $data = $this->query_api($params, 'PATCH');
    }

    return $data;
  }

  /**
   * Delete job
   *
   * @param $job_id
   * @return array
   */
  public function delete_job($job_id = null) {

    $data = array();

    if (empty($job_id)) $data['error'] = 'Error: Missing parameter. Required parameters: job_id';

    // If there are no errors, executte the API call.
    if (empty($data['error'])) {

      // /clients/{clientId}/jobs/{jobId}
      $params = array(
        'clients',
        $this->processing_service_client_id,
        'jobs',
        $job_id
      );

      // API returns 200 for a successful DELETE.
      $data = $this->query_api($params, 'DELETE');

    }

    return $data;
  }

  /**
   * Get job
   *
   * @param $job_id
   * @return array
   */
  public function get_job($job_id = null) {

    $data = array();

    if (empty($job_id)) $data['error'] = 'Error: Missing parameter. Required parameters: job_id';

    // If there are no errors, executte the API call.
    if (empty($data['error'])) {

      // /clients/{clientId}/jobs/{jobId}
      $params = array(
        'clients',
        $this->processing_service_client_id,
        'jobs',
        $job_id
      );

      // API returns 200 and one job's data for a successful GET,
      // and a 400 and error message for an unsuccessful GET.
      $data = $this->query_api($params, 'GET');

    }

    return $data;
  }

  /**
   * Get jobs
   *
   * @return array
   */
  public function get_jobs() {

    $data = array();

    // /clients/{clientId}/jobs
    $params = array(
      'clients',
      $this->processing_service_client_id,
      'jobs'
    );

    // API returns 200 and all job data for a successful GET,
    // and a 200 for an unsuccessful GET (for an invalid client ID).
    $data = $this->query_api($params, 'GET');

    return $data;
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
  public function query_api($params = array(), $method = 'GET', $post_params = array(), $return_output = true, $content_type = 'Content-type: application/json; charset=utf-8')
  {

    $data = array();

    // Make sure parameters are passed.
    if (is_array($params) && !empty($params)) {

      if (!function_exists('curl_init')){ 
        die('CURL is not installed!');
      }

      $url_path = implode('/', $params);
      $url_path = str_replace('.', '%2E', $url_path);

      // $this->u->dumper('cURL URL:',0);
      // $this->u->dumper($this->processing_service_location . $url_path);

      $ch = curl_init($this->processing_service_location . $url_path);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
      curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
      curl_setopt($ch, CURLOPT_HTTPHEADER, array($content_type));
      curl_setopt($ch, CURLINFO_HEADER_OUT, true);

      switch (strtoupper($method)) {
        case "POST":
          curl_setopt($ch, CURLOPT_POST, 1);
          curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($post_params));
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
          break;
        case "PATCH":
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
          break;
        case "DELETE":
          curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
          break;
      }

      // Return output.
      if($return_output) $data['result'] = curl_exec($ch);
      // Suppress output.
      if(!$return_output) curl_exec($ch);
      // Return the HTTP code.
      $data['httpcode'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      $data['response_header'] = curl_getinfo($ch, CURLINFO_HEADER_OUT);
      curl_close($ch);

    }

    return $data;
  }

  /**
   * Create GUID
   *
   * @return string
   */
  public function create_guid() {

    if (function_exists('com_create_guid')){
      return com_create_guid();
    } else {
      mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
      $charid = strtoupper(md5(uniqid(rand(), true)));
      $hyphen = chr(45);// "-"
      $uuid = substr($charid, 0, 8).$hyphen
          .substr($charid, 8, 4).$hyphen
          .substr($charid,12, 4).$hyphen
          .substr($charid,16, 4).$hyphen
          .substr($charid,20,12);
      return $uuid;
    }

  }

}