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

class ItemPositionTypesController extends Controller
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
        $this->table_name = 'item_position_type';
        $this->id_field_name_raw = 'item_position_type_repository_id';
        $this->id_field_name = 'item_position_type.' . $this->id_field_name_raw;
        $this->label_field_name_raw = 'label';
        $this->label_field_name = 'item_position_type.' . $this->label_field_name_raw;
    }

    /**
     * @Route("/admin/resources/item_position_types/", name="item_position_types_browse", methods="GET")
     */
    public function browse(Connection $conn, Request $request)
    {
        // Database tables are only created if not present.
        $this->repo_storage_controller->setContainer($this->container);
        $ret = $this->repo_storage_controller->build('createTable', array('table_name' => $this->table_name));

        return $this->render('resources/browse_item_position_types.html.twig', array(
            'page_title' => "Browse Item Position Types",
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));
    }

    /**
     * @Route("/admin/resources/item_position_types/datatables_browse_item_position_types", name="item_position_types_browse_datatables", methods="POST")
     *
     * Browse Item Position Types
     *
     * Run a query to retreive all Item Position Types in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_item_position_types(Connection $conn, Request $request)
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
                $sort_field = 'label_alias';
                break;
            case '3':
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
            " . $this->label_field_name . "_alias,
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
     * Matches /admin/resources/item_position_types/manage/*
     *
     * @Route("/admin/resources/item_position_types/manage/{id}", name="item_position_types_manage", methods={"GET","POST"}, defaults={"id" = null})
     *
     * @param   int     $id           The item_position_type ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_item_position_types_form(Connection $conn, Request $request, GumpParseErrors $gump_parse_errors)
    {
        $errors = false;
        $data = array();
        $gump = new GUMP();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        $this->repo_storage_controller->setContainer($this->container);
        if(empty($post)) {
          $data = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'item_position_type',
            'record_id' => (int)$id));
        }

        // Validate posted data.
        if(!empty($post)) {
            // "" => "required|numeric",
            // "" => "required|alpha_numeric",
            // "" => "required|date",
            // "" => "numeric|exact_len,5",
            // "" => "required|max_len,255|alpha_numeric",
            $rules = array(
                "label" => "required|max_len,255",
                "label_alias" => "required|max_len,255",
            );
            // $validated = $gump->validate($post, $rules);

            $errors = array();
            if (isset($validated) && ($validated !== true)) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => $this->table_name,
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => $post
            ));
            $this->addFlash('message', 'Item Position Type successfully updated.');
            return $this->redirectToRoute('item_position_types_browse');
        } else {
            return $this->render('resources/item_position_types_form.html.twig', array(
                "page_title" => !empty($id) ? 'Manage Item Position Type: ' . $data['label'] : 'Create Item Position Type'
                ,"data" => $data
                ,"errors" => $errors
                ,'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
            ));
        }

    }

    /**
     * Delete Multiple Item Position Types
     *
     * @Route("/admin/resources/item_position_types/delete", name="item_position_types_remove_records", methods={"GET"})
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

        $this->repo_storage_controller->setContainer($this->container);

        // Loop thorough the ids.
        foreach ($ids_array as $key => $id) {
          // Run the query against a single record.
          $ret = $this->repo_storage_controller->execute('markRecordsInactive', array(
            'record_type' => $this->table_name,
            'record_id' => $id,
            'user_id' => $this->getUser()->getId(),
          ));
        }

        $this->addFlash('message', 'Records successfully removed.');

      } else {
        $this->addFlash('message', 'Missing data. No records removed.');
      }

      return $this->redirectToRoute($this->table_name . '_browse');
    }


}
