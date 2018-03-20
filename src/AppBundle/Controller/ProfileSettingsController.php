<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Session\Session;
use PDO;
use GUMP;

// Custom utility bundles
use AppBundle\Utils\GumpParseErrors;
use AppBundle\Utils\AppUtilities;

class ProfileSettingsController extends Controller
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
    public function __construct(AppUtilities $u)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController();
    }

    /**
     * @Route("/admin/settings/", name="settings", methods="GET")
     */
    public function show_profile_settings(Connection $conn, Request $request)
    {
        $roles = $this->getUser()->getRoles();

        return $this->render('profileSettings/profile_settings.html.twig', array(
            'page_title' => 'Profile and Settings',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

}
