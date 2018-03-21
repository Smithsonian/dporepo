<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use Symfony\Component\DependencyInjection\Container;
use PDO;
use GUMP;

// Custom utility bundles
use AppBundle\Utils\GumpParseErrors;
use AppBundle\Utils\AppUtilities;

class CaptureMethodsController extends Controller
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

        // Table name and field names.
        $this->table_name = 'capture_methods';
        $this->id_field_name_raw = 'capture_methods_id';
        $this->id_field_name = 'capture_methods.' . $this->id_field_name_raw;
        $this->label_field_name_raw = 'label';
        $this->label_field_name = 'capture_methods.' . $this->label_field_name_raw;
    }

    /**
     * @Route("/admin/resources/capture_methods/", name="capture_methods_browse", methods="GET")
     */
    public function browse(Connection $conn, Request $request)
    {
        // Database tables are only created if not present.
        $this->repo_storage_controller->setContainer($this->container);
        $ret = $this->repo_storage_controller->build('createTable', array('table_name' => $this->table_name));

        return $this->render('resources/browse_capture_methods.html.twig', array(
            'page_title' => "Browse Capture Methods",
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/resources/capture_methods/datatables_browse_capture_methods", name="capture_methods_browse_datatables", methods="POST")
     *
     * Browse Capture Methods
     *
     * Run a query to retreive all capture methods in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_capture_methods(Connection $conn, Request $request)
    {
        $sort = '';
        $search_sql = '';
        $pdo_params = array();
        $data = array();

        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        switch($req['order'][0]['column']) {
            case '1':
                $sort_field = 'label';
                break;
            case '2':
                $sort_field = 'last_modified';
                break;
        }

        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY " . $this->table_name . ".last_modified DESC ";
        }

        if ($search) {
            $pdo_params[] = '%' . $search . '%';
            $search_sql = "
                AND (
                  " . $this->label_field_name . " LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
            " . $this->id_field_name . " AS manage,
            " . $this->label_field_name . ",
            " . $this->table_name . ".active,
            " . $this->table_name . ".last_modified,
            " . $this->id_field_name . " AS DT_RowId
            FROM " . $this->table_name . "
            WHERE " . $this->table_name . ".active = 1
            {$search_sql}
            {$sort}
            {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);
 
        $statement = $conn->prepare("SELECT FOUND_ROWS()");
        $statement->execute();
        $count = $statement->fetch(PDO::FETCH_ASSOC);
        $data["iTotalRecords"] = $count["FOUND_ROWS()"];
        $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

        return $this->json($data);
    }

    /**
     * Matches /admin/resources/capture_methods/manage/*
     *
     * @Route("/admin/resources/capture_methods/manage/{capture_methods_id}", name="capture_methods_manage", methods={"GET","POST"}, defaults={"capture_methods_id" = null})
     *
     * @param   int     $id           The capture_method ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_capture_methods_form( Connection $conn, Request $request, GumpParseErrors $gump_parse_errors )
    {
        $errors = false;
        $data = array();
        $gump = new GUMP();
        $post = $request->request->all();
        $capture_methods_id = !empty($request->attributes->get('capture_methods_id')) ? $request->attributes->get('capture_methods_id') : false;
        $data = !empty($post) ? $post : $this->get_one((int)$capture_methods_id, $conn);
        
        // Validate posted data.
        if(!empty($post)) {
            // "" => "required|numeric",
            // "" => "required|alpha_numeric",
            // "" => "required|date",
            // "" => "numeric|exact_len,5",
            // "" => "required|max_len,255|alpha_numeric",
            $rules = array(
                "label" => "required|max_len,255",
            );
            // $validated = $gump->validate($post, $rules);

            $errors = array();
            if (isset($validated) && ($validated !== true)) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
            $capture_methods_id = $this->insert_update($post, $capture_methods_id, $conn);
            $this->addFlash('message', 'Capture Method successfully updated.');
            return $this->redirectToRoute('capture_methods_browse');
        } else {
            return $this->render('resources/capture_method_form.html.twig', array(
                "page_title" => !empty($capture_methods_id) ? 'Manage Capture Method: ' . $data['label'] : 'Create Capture Method'
                ,"data" => $data
                ,"errors" => $errors
                ,'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
            ));
        }

    }

  /**
   * Get One Record
   *
   * Run a query to retrieve one record.
   *
   * @param       int $id     The id value
   * @return      array|bool  The query result
   */
  public function get_one($id = false, $conn)
  {
      $statement = $conn->prepare("SELECT *
          FROM " . $this->table_name . "
          WHERE " . $this->id_field_name . " = :id");
      $statement->bindValue(":id", $id, PDO::PARAM_INT);
      $statement->execute();
      return $statement->fetch(PDO::FETCH_ASSOC);
  }

  /**
   * Get All Records
   *
   * Run a query to retrieve all records.
   *
   * @return      array|bool  The query result
   */
  public function get_all($conn)
  {
      $statement = $conn->prepare("
        SELECT * FROM " . $this->table_name . "
        ORDER BY " . $this->label_field_name . " ASC
      ");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
  }

  /**
   * Insert/Update
   *
   * Run queries to insert and update records.
   *
   * @param       array $data  The data array
   * @param       int $id      The id value
   * @return      void
   */
  public function insert_update($data, $id = false, $conn) {

    // Update
    if($id) {
      $statement = $conn->prepare("
          UPDATE " . $this->table_name . "
          SET " . $this->label_field_name . " = :" . $this->label_field_name_raw . "
          ,last_modified_user_account_id = :last_modified_user_account_id
          WHERE " . $this->id_field_name . " = :id
      ");
      $statement->bindValue(":" . $this->label_field_name_raw, $data[$this->label_field_name_raw], PDO::PARAM_STR);
      $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
      $statement->bindValue(":id", $id, PDO::PARAM_INT);
      $statement->execute();

      return $id;
    }

    // Insert
    if(!$id) {

      $statement = $conn->prepare("INSERT INTO " . $this->table_name . "
        (" . $this->label_field_name_raw . ", date_created, created_by_user_account_id, last_modified_user_account_id)
      VALUES (:" . $this->label_field_name_raw . ", NOW(), :user_account_id, :user_account_id)");
      $statement->bindValue(":" . $this->label_field_name_raw . "", $data[$this->label_field_name_raw], PDO::PARAM_STR);
      $statement->bindValue(":user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
      $statement->execute();
      $last_inserted_id = $conn->lastInsertId();

      if(!$last_inserted_id) {
        die('INSERT INTO `' . $this->table_name . '` failed.');
      }

      return $last_inserted_id;

    }

  }

  /**
   * Delete Multiple Capture Methods
   *
   * @Route("/admin/resources/capture_methods/delete", name="capture_methods_remove_records", methods={"GET"})
   * Run a query to delete multiple records.
   *
   * @param   int     $ids      The record ids
   * @param   object  $conn     Database connection object
   * @param   object  $request  Request object
   * @return  void
   */
  public function delete_multiple(Connection $conn, Request $request)
  {
      $ids = $request->query->get('ids');

      if(!empty($ids)) {

        $ids_array = explode(',', $ids);

        foreach ($ids_array as $key => $id) {

          $statement = $conn->prepare("
              UPDATE " . $this->table_name . "
              SET active = 0, last_modified_user_account_id = :last_modified_user_account_id
              WHERE " . $this->id_field_name . " = :id
          ");
          $statement->bindValue(":id", $id, PDO::PARAM_INT);
          $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->execute();

        }

        $this->addFlash('message', 'Records successfully removed.');

      } else {
        $this->addFlash('message', 'Missing data. No records removed.');
      }

      return $this->redirectToRoute($this->table_name . '_browse');
  }

  /**
   * Delete Record
   *
   * Run a query to delete a capture method record.
   *
   * @param       int $id           The data value
   * @return      void
   */
  public function delete($id, $conn)
  {
      $statement = $conn->prepare("
        DELETE FROM " . $this->table_name . "
        WHERE " . $this->id_field_name . " = :id");
      $statement->bindValue(":id", $id, PDO::PARAM_INT);
      $statement->execute();
  }
}
