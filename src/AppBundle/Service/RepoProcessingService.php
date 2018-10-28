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
   * @var object $conn
   */
  private $conn;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

  /**
   * @var string $processing_service_location
   */
  private $processing_service_location;

  /**
   * @var string $processing_service_client_id
   */
  private $processing_service_client_id;

  /**
   * Constructor
   * @param object  $kernel  Symfony's kernel object
   * @param string  $conn  The database connection
   * @param string  $processing_service_location  Processing service location (e.g. URL)
   * @param string  $processing_service_client_id  Processing service client ID
   */
  public function __construct(KernelInterface $kernel, \Doctrine\DBAL\Connection $conn, string $processing_service_location, string $processing_service_client_id)
  {
    $this->u = new AppUtilities();
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->processing_service_location = $processing_service_location;
    $this->processing_service_client_id = $processing_service_client_id;
  }

  /**
   * Get recipes
   *
   * @return array
   */
  public function get_recipes() {

    $data = array();

    // /recipes
    $params = array(
      'recipes',
    );

    $data = $this->query_api($params, 'GET');

    return $data;
  }

  /**
   * Get recipe by name
   *
   * @param string $recipe_name
   * @return array
   */
  public function get_recipe_by_name($recipe_name = null) {

    $data = array();

    if (empty($recipe_name)) {
      $data['error'] = 'Error: Missing parameter(s). Required parameters: recipe_name';
    }

    // If there are no errors, execute the API call.
    if (empty($data['error'])) {

      $recipes = $this->get_recipes();

      if ($recipes['httpcode'] === 200) {
        // Get all recipes.
        $recipes_array = json_decode($recipes['result'], true);
        // Loop through recipes to find the target recipe.
        foreach ($recipes_array as $key => $value) {
          if ($value['name'] === $recipe_name) {
            $data = $value;
          } 
        }
        // Set an error if the recipe can't be found.
        if (empty($data)) $data['error'] = 'Error: Recipe doesn\'t exist';
      } else {
        // Set an error if the recipes endpoint returns something other than a 200 HTTP code.
        $data['error'] = 'Error: Could not retrieve recipes. ';
        $data['error'] .= 'HTTP code: ' . $recipes['httpcode'] . '. ';
        $data['error'] .= 'Response header: ' . $recipes['response_header'];
      }

    }

    return $data;
  }

  /**
   * Post job
   *
   * @param string $recipe_id
   * @param string $job_name
   * @param array $params
   * @return array
   */
  public function post_job($recipe_id = null, $job_name = null, $params = array()) {

    $data = array();

    if (empty($recipe_id) || empty($job_name) || empty($params)) {
      $data['error'] = 'Error: Missing parameter(s). Required parameters: recipe_id, job_name, params (array)';
    }

    // If there are no errors, execute the API call.
    if (empty($data['error'])) {

      $url_params = array(
        'job',
      );

      $post_params = array(
        'id' => $this->create_guid(),
        'name' => $job_name,
        'clientId' => $this->processing_service_client_id,
        'recipeId' => $recipe_id,
        'parameters' => $params,
        'priority' => 'normal',
        'submission' => str_replace('+00:00', 'Z', gmdate('c', strtotime('now'))),
      );

      // API returns 200 for a successful POST,
      // and a 404 for an unsuccessful POST. 
      $data = $this->query_api($url_params, 'POST', $post_params);
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

    // If there are no errors, execute the API call.
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

    // If there are no errors, execute the API call.
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

    // If there are no errors, execute the API call.
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

    // If there are no errors, execute the API call.
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
   * Get job by name
   *
   * @param string $job_name
   * @return array
   */
  public function get_job_by_name($job_name = null) {

    $data = array();

    if (empty($job_name)) {
      $data['error'] = 'Error: Missing parameter(s). Required parameters: job_name';
    }

    // If there are no errors, execute the API call.
    if (empty($data['error'])) {

      $recipes = $this->get_jobs();

      if ($recipes['httpcode'] === 200) {
        // Get all recipes.
        $jobs_array = json_decode($recipes['result'], true);
        // Loop through recipes to find the target recipe.
        foreach ($jobs_array as $key => $value) {
          if ($value['name'] === $job_name) {
            $data = $value;
          } 
        }
        // Set an error if the recipe can't be found.
        if (empty($data)) $data['error'] = 'Error: Job doesn\'t exist';
      } else {
        // Set an error if the recipes endpoint returns something other than a 200 HTTP code.
        $data['error'] = 'Error: Could not retrieve jobs. ';
        $data['error'] .= 'HTTP code: ' . $recipes['httpcode'] . ', ';
        $data['error'] .= 'Response header: ' . $recipes['response_header'];
      }

    }

    return $data;
  }

  /**
   * Retrieve the server machine state
   *
   * @return array
   */
  public function machine_state() {

    $data = array();

    // /machine
    $params = array(
      'machine'
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
    // $this->u->dumper($method,0);
    // $this->u->dumper($params,0);
    // $this->u->dumper(json_encode($post_params),0);

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
   * See if a job or set of jobs are running.
   *
   * @param array $job_ids An array of job ids
   * @return bool
   */
  public function are_jobs_running($job_ids = array()) {

    $data = false;
    $client_jobs = array();

    if (!empty($job_ids)) {
      
      // Get the machine state.
      $state = $this->machine_state();

      if (!empty($state['result'])) {

        // Get the repository client's jobs.
        $json_decoded = json_decode($state['result'], true);

        foreach ($json_decoded['clients'] as $key => $value) {

          if ($value['name'] === 'Goran Halusa') {
            $client_jobs = $json_decoded['clients'][$key];
            break;
          }
        }

        // Check for job_ids in the repository client's runningJobs.
        if (!empty($client_jobs) && !empty($client_jobs['runningJobs'])) {
          foreach ($job_ids as $key => $value) {
            // If a running job is found, set $data to true and break.
            if (in_array($value, $client_jobs['runningJobs'])) {
              $data = true;
              break;
            }
          }
        }

      }
    }

    return $data;
  }

  /**
   * Get processing assets.
   *
   * @param string $job_id The processing service job ID
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return bool
   */
  public function get_processing_asset_logs($job_id = null, $filesystem) {

    $data = array();
    $client_jobs = array();

    if (!empty($job_id)) {
      
      // Loop through jobs, and retrieve outputted assets.
      // Retrieve a read-stream
      try {

        // Call the processing service via WebDAV to get the directory contents.
        $files = $filesystem->listContents($job_id, false);

        if (!empty($files)) {

          foreach ($files as $file_key => $file_value) {

            // Only grab application/json and text/plain mimetypes.
            // TODO: transfer (pull) files to the repository for all other file types (e.g. .obj, .ply, or whatever).
            // And then, transfer to the file storage service (or leave them on the repository filesystem).
            if (isset($file_value['mimetype'])) {
              if (($file_value['mimetype'] === 'text/plain; charset=utf-8') || ($file_value['mimetype'] === 'application/json; charset=utf-8')) {
                // Set the file path minus the protocol and host.
                $file_path = str_replace('http://si-3ddigip01.si.edu:8000/', '', $file_value['path']);
                // Set the file name
                $file_path_array = explode('/', $file_path);
                $file_name = array_pop($file_path_array);

                // Read the file and get the contents.
                // !!!WARNING!!!
                // Had to hack:
                // vendor/league/flysystem-webdav/src/WebDAVAdapter.php (lines 129-131)
                // vendor/league/flysystem/src/Filesystem.php (line 273)
                $stream = $filesystem->readStream($file_path);
                $contents = stream_get_contents($stream);
                // Before calling fclose on the resource, check if it’s still valid using is_resource.
                if (is_resource($stream)) fclose($stream);

                $data[] = array(
                  'job_id' => $job_id,
                  'file_name' => $file_name,
                  'file_contents' => $contents,
                );

              }
            }

          }

        }

      }
      // Catch the error.
      catch(\League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
        throw $this->createNotFoundException($e->getMessage() . ' - The directory, ' . $job_id . ', does not exist.');
      }

    }

    return $data;
  }

  /**
   * @param string $recipe The processing service recipe.
   * @param string $path The path to the assets to be processed.
   * @param string $user_id The ID of the repository user.
   * @param array $params Parameters for the processing service.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function initialize_job($recipe = null, $path = null, $user_id = null, $params = array(), $filesystem)
  {

    $data = array();

    if (!empty($path) && !empty($recipe) && !empty($user_id)) {
      // If the path or file doesn't exist, prepare an error.
      if (!is_dir($path) && !is_file($path)) {
        $data[]['errors'][] = 'Target directory not found - ' . $path;
        return $data;
      }
      // If the path or file does exist, send job to the processing service.
      if (is_dir($path) || is_file($path)) {
        // Create a new job and run.
        $data = $this->send_job($path, $recipe, $user_id, $params, $filesystem);
      }
    }

    return $data;
  }

  /**
   * @param string $path The path to the assets to be processed.
   * @param string $recipe The processing service recipe.
   * @param string $user_id The ID of the repository user.
   * @param array $params Parameters for the processing service.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function send_job($path = null, $recipe = array(), $user_id = null, $params = array(), $filesystem)
  {

    $data = array();

    if (!empty($path) && !empty($recipe) && !empty($user_id) && !empty($params)) {

      // Get the ID of the recipe, so it can be passed to processing service's job creation endpoint (post_job).
      $recipe = $this->get_recipe_by_name($recipe);

      // Error handling
      if (isset($recipe['error']) && !empty($recipe['error'])) {
        $data[]['errors'][] = $recipe['error'];
        return $data;
      }

      if (!isset($recipe['error'])) {

        // Create a timestamp for the procesing job name.
        $job_name = str_replace('+00:00', 'Z', gmdate('c', strtotime('now')));
        // Post a new job.
        $result = $this->post_job($recipe['id'], $job_name, $params);

        // Error handling
        if ($result['httpcode'] !== 201) {
          $data[]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];
          return $data;
        }

        if ($result['httpcode'] === 201) {

          // Get the job data.
          $data = $this->get_job_by_name($job_name);
          // Error handling
          if (isset($data['error']) && !empty($data['error'])) {
            $data[]['errors'][] = $data['error'];
            return $data;
          }

          // Log job data to the metadata storage.
          $processing_job_id = $this->repo_storage_controller->execute('saveRecord', array(
            'base_table' => 'processing_job',
            'user_id' => $user_id,
            'values' => array(
              'job_id' => rand(1, 4000000),
              'processing_service_job_id' => $data['id'], 
              'recipe' =>  $data['recipe']['name'], 
              'job_json' => json_encode($data), 
              'state' => $data['state']
            )
          ));

          // Transfer assets to the processing service.
          $data = $this->transfer_assets_to_processing_service($path, $data, $filesystem);
        }

      }

    }

    return $data;
  }

  /**
   * @param string $path The path to the assets to be processed.
   * @param array $data Processing service's job data.
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return array
   */
  public function transfer_assets_to_processing_service($path = null, $data = array(), $filesystem)
  {

    $job_data = array();

    if (!empty($path) && !empty($data)) {

      // If this is one file, no need to traverse. Just transfer the file.
      if (is_file($path)) {
        // The external path.
        $path_external = $data['id'] . DIRECTORY_SEPARATOR . basename($path);
        // Transfer the file to the processing service via WebDAV.
        try {
          $stream = fopen($path, 'r+');
          $filesystem->writeStream($path_external, $stream);
          // Before calling fclose on the resource, check if it’s still valid using is_resource.
          if (is_resource($stream)) fclose($stream);
          // Now that the file has been transferred, go ahead and run the job.
          $result = $this->run_job($data['id']);
          // Error handling
          if ($result['httpcode'] !== 202) $job_data[]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];
        }
        // Catch the error.
        catch(\League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
          $job_data[]['errors'][] = $e->getMessage();
        }
      }

      // If this is a directory, traverse the local path.
      if (is_dir($path)) {

        $finder = new Finder();
        $finder->in($path);
        $i = 0;
        foreach ($finder as $file) {

          // Make sure the asset is a file and not a directory (directories are automatically detected by WebDAV).
          if ($file->isFile()) {
            // The external path.
            $path_external = $data['id'] . DIRECTORY_SEPARATOR . $file->getFilename();
            // Transfer the file to the processing service via WebDAV.
            try {
              $stream = fopen($file->getPathname(), 'r+');
              $filesystem->writeStream($path_external, $stream);
              // Before calling fclose on the resource, check if it’s still valid using is_resource.
              if (is_resource($stream)) fclose($stream);
              // Now that the file has been transferred, go ahead and run the job.
              $result = $this->run_job($data['id']);
              // Error handling
              if ($result['httpcode'] !== 202) $job_data[$i]['errors'][] = 'The processing service returned HTTP code ' . $result['httpcode'];
            }
            // Catch the error.
            catch(\League\Flysystem\FileNotFoundException | \Sabre\HTTP\ClientException $e) {
              $job_data[$i]['errors'][] = $e->getMessage();
            }

            $i++;
          }

        }

      }
    }

    $data = array_merge($data, $job_data);

    return $data;
  }

  /**
   * Get Processing Results
   *
   * @param string $job_id The processing service job ID
   * @param string $user_id The user's repository ID
   * @param object $filesystem Filesystem object (via Flysystem).
   * See: https://flysystem.thephpleague.com/docs/usage/filesystem-api/
   * @return 
   */
  public function get_processing_results($job_id = null, $user_id = null, $filesystem)
  {

    $data = array();

    // Retrieve all of the logs produced by the processing service.
    $processing_assets = $this->get_processing_asset_logs($job_id, $filesystem);

    // Insert processing-based logs into the metadata storage service.
    if (!empty($processing_assets)) {
      // Loop through the processing-based logs.
      foreach ($processing_assets as $asset) {
        // Insert one processing-based log.
        $id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'processing_job_file',
          'user_id' => $user_id,
          'values' => $asset,
        ));
      }
    }

    // Get the processing job from the processing service.
    $processing_job = $this->get_job($job_id);

    // Error handling
    if ($processing_job['httpcode'] !== 200) $data[]['errors'][] = 'The processing service returned HTTP code ' . $processing_job['httpcode'];

    if ($processing_job['httpcode'] === 200) {

      // JSON decode it.
      $processing_job = json_decode($processing_job['result'], true);

      // // If there's an error, set the repository's upload/ingest job status to 'failed'.
      // $job_status = ($processing_job['state'] === 'error') ? 'failed' : $job_status;

      // Query the database for the corresponding processing job record,
      // so we can use the repository's ID (processing_job_id) to update the repository's processing job record.
      $repo_processing_job_data = $this->repo_storage_controller->execute('getRecords', array(
        'base_table' => 'processing_job',
        'fields' => array(),
        'limit' => 1,
        'search_params' => array(
          0 => array('field_names' => array('processing_job.processing_service_job_id'), 'search_values' => array($job_id), 'comparison' => '='),
        ),
        'search_type' => 'AND',
        'omit_active_field' => true,
        )
      );

      // Update the main job record with the results of the processing job.
      if ($repo_processing_job_data) {

        // Update one job record.
        $processing_job_id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'processing_job',
          'record_id' => $repo_processing_job_data[0]['processing_job_id'],
          'user_id' => $user_id,
          'values' => array(
            'job_json' => json_encode($processing_job), 
            'state' => $processing_job['state']
          )
        ));

        // Get the report file.
        foreach ($processing_assets as $asset) {
          if (stristr($asset['file_name'], '-report.json')) {
            $data['report'] = json_decode($asset['file_contents'], true);
          }
        }

        // // Log the errors to the database.
        // if ($processing_job['state'] === 'error') {

        //   // Get the model's file name
        //   foreach ($processing_assets as $asset) {
        //     if (stristr($asset['file_name'], '-report.json')) {
        //       $data['report'] = json_decode($asset['file_contents'], true);
        //     }
        //   }
        //   $this->repoValidate->logErrors(
        //     array(
        //       'job_id' => $job_data['job_id'],
        //       'user_id' => $this->user_id,
        //       'job_log_label' => 'Asset Validation',
        //       'errors' => array($processing_job['error'] . ' (Processing job ID: ' . $processing_job['id'] . ', Model file name: ' . $data['report']['parameters']['meshFile'] . ')'),
        //     )
        //   );
        // }

      }

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




// // Check to see if jobs are running. Don't pass "Go" until all jobs are finished.
// while ($this->are_jobs_running($processing_job['job_ids'])) {
//   $this->are_jobs_running($processing_job['job_ids']);
//   sleep(5);
// }



// // Continue only if a job ID returned.
// if (!empty($processing_job_id)) {
//   // Create the job entry in the repository's 'job table.
//   $uuid = uniqid('3df_', true);
//   // Insert a record into the job table.
//   // TODO: Feed the 'job_label' and 'job_type' to the log leveraging fields from a form submission in the UI?
//   $job_id = $this->repo_storage_controller->execute('saveRecord', array(
//     'base_table' => 'job',
//     'user_id' => $user_id,
//     'values' => array(
//       'uuid' => $uuid,
//       'project_id' => (int)$project['project_repository_id'],
//       'job_label' => 'Processing Job: "' . $project['project_name'] . '"',
//       'job_type' => 'processing job',
//       'job_status' => 'initialized',
//     )
//   ));
// }



// if (!empty($data[$i]['errors'])) {
//   // Log the errors to the database.
//   $this->repoValidate->logErrors(
//     array(
//       'job_id' => $job_data['job_id'],
//       'user_id' => $this->user_id,
//       'job_log_label' => 'Validate Model',
//       'errors' => $data[$i]['errors'],
//     )
//   );
// }


// // Log job data to the metadata storage
// if (isset($job['error']) && empty($job['error'])) {
//   $processing_job_id = $this->repo_storage_controller->execute('saveRecord', array(
//     'base_table' => 'processing_job',
//     'user_id' => $this->user_id,
//     'values' => array(
//       'job_id' => $job_data['job_id'],
//       'processing_service_job_id' => $job['id'], 
//       'recipe' =>  $job['recipe']['name'], 
//       'job_json' => json_encode($job), 
//       'state' => $job['state']
//     )
//   ));
// }