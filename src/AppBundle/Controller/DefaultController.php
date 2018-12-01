<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Controller\RepoStorageHybridController;
use Doctrine\DBAL\Driver\Connection;
class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request,Connection $conn)
    {
        $repo_storage_controller = new RepoStorageHybridController($conn);
        $ret = $repo_storage_controller->build('checkDatabaseExists', null);

        $database_exists = false;
        $database_error = '';

        if(is_array($ret)) {
          if(isset($ret['installed'])) {
            $database_exists = $ret['installed'];
          }
          if(isset($ret['error'])) {
            $database_error = $ret['error'];
          }
        }

        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
          'database_exists' => $database_exists,
          'database_error' => $database_error]);
    }

    /**
    * @Route("/admin/plupload", name="plupload_test_page")
    *
    * https://github.com/1up-lab/OneupUploaderBundle
    */
    public function uploadTest(Request $request)
    {
        return $this->render('default/plupload_test.html.twig', array(
            'page_title' => 'Plupload Example',
        ));
    }
}
