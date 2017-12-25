<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use PDO;
use GUMP;

// Custom utility bundles
use AppBundle\Utils\GumpParseErrors;
use AppBundle\Utils\AppUtilities;

// Projects methods
use AppBundle\Controller\ProjectsController;
// Subjects methods
use AppBundle\Controller\SubjectsController;
// Items methods
use AppBundle\Controller\ItemsController;
// Datasets methods
use AppBundle\Controller\DatasetsController;

class AdminController extends Controller
{
    /**
     * @var object $u
     */
    public $u;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u)
    {
        $this->u = $u;
    }

    /**
     * @Route("/admin/", name="admin_home", methods="GET")
     */
    public function show_admin(Connection $conn, Request $request)
    {
        return $this->render('admin/admin.html.twig', array(
            'page_title' => 'Dashboard',
        ));
    }

}
