<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Controller\RepoStorageHybridController;
use PDO;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoStorageStructureHybrid;

class InstallController extends Controller
{
  public $fs;
  public $project_directory;
  public $remote_uploads_directory;
  public $uploads_directory;
  private $kernel;

  private $repo_storage_controller;
  private $connection;

  /**
  * Constructor
  * @param object  $u  Utility functions object
  */
  public function __construct(object $conn, FilesystemHelperController $fs, string $remote_uploads_directory, KernelInterface $kernel, string $uploads_directory)
  {

    $this->fs = $fs;
    $this->project_directory = $kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->remote_uploads_directory = $remote_uploads_directory;
    $this->uploads_directory = $uploads_directory;
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
    $this->connection = $conn;
    $this->kernel = $kernel;
  }

  /**
   * @Route("/install/", name="install")
   */
  public function install(Connection $conn, Request $request, $flag = null)
  {

    $ret = $this->build('installDatabase', null);

    $database_installed = false;
    $database_error = '';

    if(is_array($ret)) {
      if(isset($ret['installed'])) {
        $database_installed = $ret['installed'];
      }
      if(isset($ret['error'])) {
        $database_error = $ret['error'];
      }
    }

    return $this->render('install/install.html.twig', array(
    'page_title' => 'Install Database',
    'database_installed' => $database_installed,
    'database_error' => $database_error
    ));
  }

  public function build($function, $parameters) {

    $this->repo_storage_structure = new RepoStorageStructureHybrid(
      $this->connection,
      $this->uploads_directory,
      $this->project_directory,
      $this->fs,
      $this->uploads_directory //container->getParameter('uploads_directory')
  );

    if(!method_exists($this->repo_storage_structure, $function)) {
      //@todo
      return NULL;
    }
    else {
      if ($parameters) {
        $ret = $this->repo_storage_structure->$function($parameters);
      }else{
        $ret = $this->repo_storage_structure->$function();
      }

      if(is_array($ret) && isset($ret['installed']) && $ret['installed'] == 1) {
        $this->createUserOne();
      }

      return $ret;

    }
    return array('installed' => true);

  }

  public function createUserOne() {

      //@todo get from parameters.yml or prompt user
      $email = 'test@test.com';
      $username = 'admin';
      $password = 'foundation';

      $userManager = $this->get('fos_user.user_manager');

      // Or you can use the doctrine entity manager if you want instead the fosuser manager
      // to find
      //$em = $this->getDoctrine()->getManager();
      //$usersRepository = $em->getRepository("mybundleuserBundle:User");
      // or use directly the namespace and the name of the class
      // $usersRepository = $em->getRepository("mybundle\userBundle\Entity\User");
      //$email_exist = $usersRepository->findOneBy(array('email' => $email));

      //@todo not really needed but...
      // Check if the user exists to prevent Integrity constraint violation error in the insertion
      $email_exist = $userManager->findUserByEmail($email);
      if($email_exist){
          return false;
      }

      $user = $userManager->createUser();
      $user->setUsername($username);
      $user->setEmail($email);
      $user->setEmailCanonical($email);
      $user->setEnabled(1); // enable the user or enable it later with a confirmation token in the email
      // this method will encrypt the password with the default settings :)
      $user->setPlainPassword($password);
      $userManager->updateUser($user);

      $params = array(
        'user_id' => 1,
        'username_canonical' => 'admin',
        'role_id' => 1
      );
      $this->repo_storage_controller->execute('saveUserRole', $params);

  }

}
