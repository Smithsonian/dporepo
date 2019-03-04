<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use Symfony\Component\HttpFoundation\Session\Session;
use PDO;
use GUMP;

// Custom utility bundles
use AppBundle\Utils\GumpParseErrors;
use AppBundle\Utils\AppUtilities;

class ProfileSettingController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
    }

    /**
     * @Route("/admin/settings/", name="settings", methods="GET")
     */
    public function showProfileSettings(Connection $conn, Request $request)
    {
        $roles = $this->getUser()->getRoles();

        return $this->render('profileSettings/profile_settings.html.twig', array(
          'page_title' => 'Profile and Settings',
          'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

}
