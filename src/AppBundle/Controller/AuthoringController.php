<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;
use Symfony\Component\HttpFoundation\JsonResponse;
use AppBundle\Controller\RepoStorageHybridController;
use Symfony\Component\HttpKernel\KernelInterface;
use Sabre\DAV;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

class AuthoringController extends Controller
{
  /**
   * @var object $u
   */
  public $u;

  /**
   * @var object $repo_storage_controller
   */
  private $repo_storage_controller;

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
   * @var string $external_file_storage_path
   */
  private $external_file_storage_path;

  /**
   * @var array $accepted_types
   */
  private $accepted_types;

  /**
   * @var object $root_directory
   */
  private $root_directory;

  /**
   * @var object $server
   */
  private $server;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(AppUtilities $u, KernelInterface $kernel, string $uploads_directory, string $external_file_storage_path, Connection $conn)
  {
    $this->u = $u;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . '';
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    $this->root_directory = new DAV\FS\Directory($this->project_directory . $this->uploads_directory);
    $this->server = new DAV\Server($this->root_directory);

    $this->accepted_types = array('item', 'presentation');
  }

  /**
   * @Route("/author", name="authoring", methods="POST")
   *
   * Author
   *
   * @param object  $request  Request object
   * @return string
   */
  public function author(Request $request) {

    $data = array();
    $req = $request->request->all();

    if (!isset($req['type']) && empty($data['type'])) $data['error'][] = 'The authoring type is empty. Possible types: ' . implode(', ', $this->accepted_types);
    if (!isset($req['json']) && empty($data['json'])) $data['error'][] = 'JSON is empty';

    if (!isset($data['error'])) {
      if (!in_array($req['type'], $this->accepted_types)) $data['error'][] = 'The authoring type is invalid. Possible types: ' . implode(',', $this->accepted_types);
    }

    if (!isset($data['error'])) {

      // Remove any "pretty print" formatting from the JSON.
      $json = json_encode(json_decode($req['json'], true));

      // Save to metadata storage.
      $id = $this->repo_storage_controller->execute('saveRecord', array(
        'base_table' => ($req['type'] === 'item') ? 'authoring_item' : 'authoring_presentation',
        'record_id' => false,
        'user_id' => 0,
        'values' => array('json' => $req['json'])
      ));

      if ($id) $data['id'] = $id;
    }

    return $this->json($data);
  }

  /**
   * @Route("/webdav{path}", name="webdav", methods={"GET","POST","PUT","DELETE","MKCOL","PROPFIND"}, defaults={"path" = null}, requirements={"path"=".+"})
   *
   * WebDAV Server
   *
   * @param object  $request  Request object
   * @return string
   */
  public function webdav(Request $request) {
    
    // If the server is not in the webroot, make sure the following line has the correct information
    $this->server->setBaseUri('/webdav');

    // The lock manager is reponsible for making sure users don't overwrite each others changes.
    $lock_backend = new DAV\Locks\Backend\File('data/locks');
    $lock_plugin = new DAV\Locks\Plugin($lock_backend);
    $this->server->addPlugin($lock_plugin);

    // This ensures that we get a pretty index in the browser, but it is optional.
    $this->server->addPlugin(new DAV\Browser\Plugin());

    // Fire up the server.
    $this->server->exec();

    die();
  }

}