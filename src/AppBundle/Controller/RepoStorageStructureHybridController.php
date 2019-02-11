<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PDO;

use AppBundle\Service\RepoStorageStructureHybrid;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Form\BackupForm;

use AppBundle\Service\RepoUserAccess;
use AppBundle\Controller\RepoStorageHybridController;
use AppBundle\Controller\FilesystemHelperController;
use League\Flysystem;

use Symfony\Component\HttpKernel\KernelInterface;


class RepoStorageStructureHybridController extends Controller {

  private $repo_storage_structure;
  private $connection;

  /**
   * @var string $uploads_directory
   */
  private $uploads_directory;

  private $repo_user_access;

  /**
   * @var string $project_directory
   */
  private $project_directory;

  //private $external_file_storage_path;

  private $fs;

  public function __construct(KernelInterface $kernel, Connection $conn, string $uploads_directory, FilesystemHelperController $fs) { // , string $external_file_storage_path
    $this->connection = $conn;
    $this->uploads_directory = (DIRECTORY_SEPARATOR === '\\') ? str_replace('\\', '/', $uploads_directory) : $uploads_directory;
    //$this->external_file_storage_path = (DIRECTORY_SEPARATOR === '\\') ? str_replace('/', '\\', $external_file_storage_path) : $external_file_storage_path;;
    $this->project_directory = $kernel->getProjectDir() . DIRECTORY_SEPARATOR;
    $this->repo_user_access = new RepoUserAccess($conn);
    $this->fs = $fs;
  }

  public function backup($include_schema = true, $include_data = true) {

    $this->repo_storage_structure = new RepoStorageStructureHybrid(
      $this->connection,
      $this->uploads_directory,
      $this->project_directory,
      $this->fs
      //, $this->getUser()->getId()
    );

    // Create the backup.
    $backup_results = $this->repo_storage_structure->createBackup((bool)$include_schema, (bool)$include_data);

    // $backup_results is an array containing 'result' (success or fail) and optionally 'errors' array.
    return $backup_results;

  }

  /**
   * @Route("/admin/datatables_browse_backups", name="backups_browse_datatables", methods={"GET","POST"})
   *
   * Browse backups
   *
   * Run a query to retrieve all backups in the database.
   *
   * @param   object  Request     Request object
   * @return  array|bool          The query result
   */
  public function datatablesBrowseBackups(Request $request)
  {
    $req = $request->request->all();

    $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
    $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
    $sort_order = $req['order'][0]['dir'];
    $start_record = !empty($req['start']) ? $req['start'] : 0;
    $stop_record = !empty($req['length']) ? $req['length'] : 20;

    $query_params = array(
      'record_type' => 'backup',
      'sort_field' => $sort_field,
      'sort_order' => $sort_order,
      'start_record' => $start_record,
      'stop_record' => $stop_record,
    );
    if ($search) {
      $query_params['search_value'] = $search;
    }

    $rc = new RepoStorageHybridController($this->connection);
    //@todo getDatatable default handling expects a field 'label' in the db table
    // create a new function for getting datatable values for the table "backups"
    $data = $rc->execute('getDatatableBackup', $query_params);

    return $this->json($data);
  }

  /**
   * @Route("/admin/backups/", name="backups_browse", methods="GET")
   */
  public function browseBackups(Connection $conn, Request $request, IsniController $isni)
  {

    $username = $this->getUser()->getUsernameCanonical();
    //@todo backup permission
    $access = $this->repo_user_access->get_user_access_any($username, 'view_projects');

    if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
      $response = new Response();
      $response->setStatusCode(403);
      return $response;
    }

    return $this->render('admin/browse_backups.html.twig', array(
      'page_title' => 'Browse Backups',
      'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
    ));
  }

  /**
   * Matches /admin/backup/*
   *
   * @Route("/admin/backup/add", name="backup_add", methods={"GET","POST"}, defaults={"backup_id" = null})
   *
   * @param   object  Connection  Database connection object
   * @param   object  Request     Request object
   * @return  array|bool          The query result
   */
  function showBackupForm( Connection $conn, Request $request )
  {

    // Create the form
    $form = $this->createForm(BackupForm::class);

    // Handle the request
    $form->handleRequest($request);

    // If form is submitted and passes validation, insert/update the database record.
    if($form->isSubmitted() && $form->isValid()) {
      $return = $this->backup(true, true);
      $id = isset($return['id']) ? $return['id'] : 0;

      if(!isset($return['errors'])) {
        $this->addFlash('message', 'Backup created.');
        return $this->redirect('/admin/backups');
      }
      else {
        $this->addFlash('error', 'Backup not created: ' . implode(" ", $return['errors']));
      }

    }

    return $this->render('admin/backup_form.html.twig', array(
      'page_title' => (int)$id > 0
        ? 'Backup: ' . $id
        : 'Add Backup',
      'backup_data' => $return,
      'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
      'form' => $form->createView(),
    ));

  }

}