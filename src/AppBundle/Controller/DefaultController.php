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
        $ret = $repo_storage_controller->build('databaseExist', null);
        //$ret = $repo_storage_controller->build('installDatabase', null);

        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        "dbexist"=>$ret]);
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
