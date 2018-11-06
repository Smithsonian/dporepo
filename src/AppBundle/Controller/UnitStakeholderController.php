<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use PDO;
use GUMP;

// Custom utility bundles
use AppBundle\Utils\GumpParseErrors;
use AppBundle\Utils\AppUtilities;

use AppBundle\Controller\ProjectsController;

class UnitStakeholderController extends Controller
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

        // Table name and field names.
        $this->table_name = 'unit_stakeholder';
        $this->id_field_name_raw = 'unit_stakeholder_repository_id';
        $this->id_field_name = 'unit_stakeholder.' . $this->id_field_name_raw;
        $this->label_field_name_raw = 'unit_stakeholder_label';
        $this->label_field_name = 'unit_stakeholder.' . $this->label_field_name_raw;
        $this->full_name_field_name_raw = 'unit_stakeholder_full_name';
        $this->full_name_field_name = 'unit_stakeholder.' . $this->full_name_field_name_raw;
    }

    /**
     * @Route("/admin/resources/unit_stakeholder/", name="unit_stakeholder_browse", methods="GET")
     */
    public function browse(Request $request)
    {
        return $this->render('resources/browse_unit_stakeholder.html.twig', array(
            'page_title' => "Browse Unit/Stakeholder",
        ));
    }

    /**
     * @Route("/admin/resources/unit_stakeholder/datatables_browse_unit_stakeholder", name="unit_stakeholder_browse_datatables", methods="POST")
     *
     * Browse Unit Stakeholder
     *
     * Run a query to retrieve all Unit Stakeholder in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_unit_stakeholder(Request $request)
    {
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $sort_field = 'last_modified';
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

        $query_params = array(
          'record_type' => 'unit_stakeholder',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        
        $data = $this->repo_storage_controller->execute('getDatatable', $query_params);

        return $this->json($data);
    }

    /**
     * Matches /admin/resources/unit_stakeholder/manage/*
     *
     * @Route("/admin/resources/unit_stakeholder/manage/{id}", name="unit_stakeholder_manage", methods={"GET","POST"}, defaults={"id" = null})
     *
     * @param   int     $id           The unit_stakeholder ID
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_unit_stakeholder_form(Request $request, GumpParseErrors $gump_parse_errors, ProjectsController $projects, IsniController $isni)
    {
        $errors = false;
        $data = array();
        $gump = new GUMP();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        
        if(empty($post)) {
          $data = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'unit_stakeholder',
            'record_id' => (int)$id));
        }

        // Get data from lookup tables.
        $data['units_stakeholders'] = $projects->get_units_stakeholders();
        if(!array_key_exists('isni_id', $data)) {
          $data['isni_id'] = NULL;
        }

        // Validate posted data.
        if(!empty($post)) {
            // "" => "required|numeric",
            // "" => "required|alpha_numeric",
            // "" => "required|date",
            // "" => "numeric|exact_len,5",
            // "" => "required|max_len,255|alpha_numeric",
            $rules = array(
                "unit_stakeholder_label" => "required|max_len,255",
                "unit_stakeholder_full_name" => "required|max_len,255",
            );
            // $validated = $gump->validate($post, $rules);

            $errors = array();
            if (isset($validated) && ($validated !== true)) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
          $id = $this->insert_update($post, $id);
            $this->addFlash('message', 'Unit/Stakeholder successfully updated.');
            return $this->redirectToRoute('unit_stakeholder_browse');
        } else {
            return $this->render('resources/unit_stakeholder_form.html.twig', array(
                "page_title" => !empty($id) ? 'Manage Unit/Stakeholder: ' . $data['unit_stakeholder_label'] : 'Create Unit/Stakeholder'
                ,"data" => $data
                ,"errors" => $errors
            ));
        }

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
    public function insert_update($data, $id = false)
    {
        // Query the isni_data table to see if there's an entry.
        $isni_data = $this->repo_storage_controller->execute('getIsniRecordById', array (
          'record_id' => $data['stakeholder_guid'])
        );

        // If there is no entry, then perform an insert.
        if(!$isni_data) {
          $isni_inserted = $this->repo_storage_controller->execute('saveIsniRecord', array(
            'user_id' => $this->getUser()->getId(),
            'record_id' => $data['isni_id'],
            'record_label' => $data['stakeholder_label'],
          ));
        }

        $id = $this->repo_storage_controller->execute('saveRecord', array(
          'base_table' => 'unit_stakeholder',
          'record_id' => $id,
          'user_id' => $this->getUser()->getId(),
          'values' => $data
        ));

        return $id;
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
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple(Request $request)
    {
        $ids = $request->query->get('ids');

        if(!empty($ids)) {

          $ids_array = explode(',', $ids);

          

          // Loop thorough the ids.
          foreach ($ids_array as $key => $id) {
            // Run the query against a single record.
            $ret = $this->repo_storage_controller->execute('markRecordInactive', array(
              'record_type' => $this->table_name,
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
            ));
          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('unit_stakeholder_browse');
    }


}
