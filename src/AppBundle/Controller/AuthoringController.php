<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpKernel\KernelInterface;
use Sabre\DAV;
use Sabre\DAV\Auth;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class AuthoringController extends Controller
{
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
  public function __construct(AppUtilities $u, KernelInterface $kernel, string $uploads_directory)
  {
    $this->u = $u;
    $this->kernel = $kernel;
    $this->project_directory = $this->kernel->getProjectDir() . DIRECTORY_SEPARATOR . '';
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $uploads_directory) : $uploads_directory;
    $this->root_directory = new DAV\FS\Directory($this->project_directory . $this->uploads_directory);
    $this->server = new DAV\Server($this->root_directory);
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

    // If the WebDAV request is not coming from this server, throw a createNotFoundException (404).
    if ($_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_NAME']) throw $this->createNotFoundException('Not found (404)');

    // // Authentication-based Example
    // // Uses a simple file to store usernames and passwords. The format of the file is identical to Apache's htdigest file.
    // // See: Using the File backend - http://sabre.io/dav/authentication/
    // if ($_SERVER['REMOTE_ADDR'] !== $_SERVER['SERVER_NAME']) {
    //   $auth_backend = new Auth\Backend\File($this->project_directory . 'web/uploads/htdigest');
    //   $auth_backend->setRealm('3drepoDAV');
    //   $auth_plugin = new Auth\Plugin($auth_backend);
    //   // Add the plugin to the server.
    //   $this->server->addPlugin($auth_plugin);
    // }
    
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