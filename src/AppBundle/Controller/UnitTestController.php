<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use PDO;
use GUMP;

// Custom utility bundles
use AppBundle\Utils\GumpParseErrors;
use AppBundle\Service\FileHelperService;

class UnitTestController extends Controller
{
  /**
   * @var object $f
   */
  public $f;
  private $repo_storage_controller;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(FileHelperService $f, Connection $conn)
  {
    // Usage: $this->u->dumper($variable);
    $this->f = $f;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);

  }

  /**
   * @Route("/admin/test/get_alternate_file_paths", name="test_file_path_alternates", methods="GET")
   */
  public function getAlternateFilePaths(Request $request)
  {

    $path_in = $request->query->get("path");
    $v_test = $request->query->get("v");
    $verbose = isset($v_test) && $v_test == 1 ? true : false;

    $response = $this->f->getAlternateFilePaths($path_in, $verbose);
    $response["incoming_path"] = $path_in;

    return new JsonResponse($response);
  }

  /**
   * @Route("/admin/test/get_capture_dataset_images", name="test_get_capture_dataset_images", methods="GET")
   */
  public function getCaptureDatasetImages(Request $request)
  {

    $job_id = $request->query->get("job_uuid");
    $limit = $request->query->get("limit");

    $query_params['job_uuid'] = $job_id;
    if(isset($limit)) {
      $query_params['limit'] = $limit;
    }

    $response = $this->repo_storage_controller->execute('getImportedCaptureDatasetImages', $query_params);

    return new JsonResponse($response);
  }

}
