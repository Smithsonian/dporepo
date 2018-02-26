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

class UnitStakeholderController extends Controller
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
        // Usage: $this->u->dumper($variable);
        $this->u = $u;

        // Table name and field names.
        $this->table_name = 'unit_stakeholder';
        $this->id_field_name_raw = 'unit_stakeholder_id';
        $this->id_field_name = 'unit_stakeholder.' . $this->id_field_name_raw;
        $this->label_field_name_raw = 'unit_stakeholder_label';
        $this->label_field_name = 'unit_stakeholder.' . $this->label_field_name_raw;
        $this->full_name_field_name_raw = 'unit_stakeholder_full_name';
        $this->full_name_field_name = 'unit_stakeholder.' . $this->full_name_field_name_raw;
    }

    /**
     * @Route("/admin/resources/unit_stakeholder/", name="unit_stakeholder_browse", methods="GET")
     */
    public function browse(Connection $conn, Request $request)
    {
        // Database tables are only created if not present.
        $create_table = $this->create_table($conn);

        return $this->render('resources/browse_unit_stakeholder.html.twig', array(
            'page_title' => "Browse Unit/Stakeholder",
        ));
    }

    /**
     * @Route("/admin/resources/unit_stakeholder/datatables_browse_unit_stakeholder", name="unit_stakeholder_browse_datatables", methods="POST")
     *
     * Browse Unit Stakeholder
     *
     * Run a query to retreive all Unit Stakeholder in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_unit_stakeholder(Connection $conn, Request $request)
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
                $sort_field = 'unit_stakeholder_label';
                break;
            case '2':
                $sort_field = 'unit_stakeholder_full_name';
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
            $pdo_params[] = '%' . $search . '%';
            $pdo_params[] = '%' . $search . '%';
            $search_sql = "
                AND (
                  " . $this->label_field_name . " LIKE ?
                  OR " . $this->full_name_field_name . " LIKE ?
                  OR " . $this->table_name . ".last_modified LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
            " . $this->id_field_name . " AS manage,
            " . $this->label_field_name . ",
            " . $this->full_name_field_name . ",
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
     * Matches /admin/resources/unit_stakeholder/manage/*
     *
     * @Route("/admin/resources/unit_stakeholder/manage/{unit_stakeholder_id}", name="unit_stakeholder_manage", methods={"GET","POST"}, defaults={"unit_stakeholder_id" = null})
     *
     * @param   int     $id           The unit_stakeholder ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_unit_stakeholder_form(Connection $conn, Request $request, GumpParseErrors $gump_parse_errors)
    {
        $errors = false;
        $data = array();
        $gump = new GUMP();
        $post = $request->request->all();
        $unit_stakeholder_id = !empty($request->attributes->get('unit_stakeholder_id')) ? $request->attributes->get('unit_stakeholder_id') : false;
        $data = !empty($post) ? $post : $this->get_one((int)$unit_stakeholder_id, $conn);
        
        // Validate posted data.
        if(!empty($post)) {
            // "" => "required|numeric",
            // "" => "required|alpha_numeric",
            // "" => "required|date",
            // "" => "numeric|exact_len,5",
            // "" => "required|max_len,255|alpha_numeric",
            $rules = array(
                "label" => "required|max_len,255",
                "full_name" => "required|max_len,255",
            );
            $validated = $gump->validate($post, $rules);

            $errors = array();
            if ($validated !== true) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
            $unit_stakeholder_id = $this->insert_update($post, $unit_stakeholder_id, $conn);
            $this->addFlash('message', 'Unit/Stakeholder successfully updated.');
            return $this->redirectToRoute('unit_stakeholder_browse');
        } else {
            return $this->render('resources/unit_stakeholder_form.html.twig', array(
                "page_title" => !empty($unit_stakeholder_id) ? 'Manage Unit/Stakeholder: ' . $data['label'] : 'Create Unit/Stakeholder'
                ,"data" => $data
                ,"errors" => $errors
            ));
        }

    }

    /**
     * Get One Record
     *
     * Run a query to retrieve one record.
     *
     * @param   int $id     The id value
     * @return  array|bool  The query result
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
    * @return  array|bool  The query result
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
     * @param   array $data  The data array
     * @param   int $id      The id value
     * @return  void
     */
    public function insert_update($data, $id = false, $conn)
    {
        // Update
        if($id) {
            $statement = $conn->prepare("
                UPDATE " . $this->table_name . "
                SET " . $this->label_field_name . " = :" . $this->label_field_name_raw . ", 
                    " . $this->full_name_field_name . " = :" . $this->full_name_field_name_raw . "
                ,last_modified_user_account_id = :last_modified_user_account_id
                WHERE " . $this->id_field_name . " = :id
            ");
          $statement->bindValue(":" . $this->label_field_name_raw, $data[$this->label_field_name_raw], PDO::PARAM_STR);
          $statement->bindValue(":" . $this->full_name_field_name_raw, $data[$this->full_name_field_name_raw], PDO::PARAM_STR);
          $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->bindValue(":id", $id, PDO::PARAM_INT);
          $statement->execute();

          return $id;
        }

        // Insert
        if(!$id) {
            $statement = $conn->prepare("INSERT INTO " . $this->table_name . "
                (" . $this->label_field_name_raw . ", " . $this->full_name_field_name_raw . ", date_created, created_by_user_account_id, last_modified_user_account_id)
                VALUES (:" . $this->label_field_name_raw . ", :" . $this->full_name_field_name_raw . ", NOW(), :user_account_id, :user_account_id)");
            $statement->bindValue(":" . $this->label_field_name_raw, $data[$this->label_field_name_raw], PDO::PARAM_STR);
            $statement->bindValue(":" . $this->full_name_field_name_raw, $data[$this->full_name_field_name_raw], PDO::PARAM_STR);
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
     * Delete Records
     *
     * Matches /admin/resources/unit_stakeholder/delete
     *
     * @Route("/admin/resources/unit_stakeholder/delete", name="unit_stakeholder_remove_records", methods={"GET"})
     * Run a query to delete multiple Unit Stakeholde records.
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

        return $this->redirectToRoute('unit_stakeholder_browse');
    }

    /**
     * Delete Record
     *
     * Run a query to delete a Unit Stakeholde record.
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

    /**
     * Create the Database Table
     *
     * @return      void
     */
    public function create_table($conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `" . $this->table_name . "` (
            `" . $this->id_field_name_raw . "` int(11) NOT NULL AUTO_INCREMENT,
            `" . $this->label_field_name_raw . "` varchar(255) NOT NULL DEFAULT '',
            `" . $this->full_name_field_name_raw . "` varchar(255) NOT NULL DEFAULT '',
            `unit_stakeholder_guid` varchar(255) NOT NULL DEFAULT '',
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`" . $this->id_field_name_raw . "`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores " . $this->table_name . " metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `' . $this->table_name . '` failed.');
        } else {
            return TRUE;
        }
    }
  
}
