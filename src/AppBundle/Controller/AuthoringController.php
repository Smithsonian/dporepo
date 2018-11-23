<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

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
   * @var array $accepted_types
   */
  private $accepted_types;

  /**
   * Constructor
   * @param object  $u  Utility functions object
   */
  public function __construct(Connection $conn)
  {
    $this->u = new AppUtilities();
    $this->repo_storage_controller = new RepoStorageHybridController($conn);
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
      // $this->u->dumper($json);

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

}