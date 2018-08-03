<?php

namespace AppBundle\Controller;

use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        // replace this example code with whatever you need
        return $this->render('default/index.html.twig', [
            'base_dir' => realpath($this->getParameter('kernel.project_dir')).DIRECTORY_SEPARATOR,
        ]);
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
