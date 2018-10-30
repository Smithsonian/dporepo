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
use AppBundle\Service\RepoUserAccess;

class DatasetTypesController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;
    private $repo_user_access;

    /**
     * Constructor
     * @param object  $u  Utility functions object
     */
    public function __construct(AppUtilities $u, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->repo_user_access = new RepoUserAccess($conn);

        // Table name and field names.
        $this->table_name = 'dataset_type';
        $this->id_field_name_raw = 'dataset_type_repository_id';
        $this->id_field_name = 'dataset_type.' . $this->id_field_name_raw;
        $this->label_field_name_raw = 'label';
        $this->label_field_name = 'dataset_type.' . $this->label_field_name_raw;
    }

    /**
     * @Route("/admin/resources/dataset_types/", name="dataset_types_browse", methods="GET")
     */
    public function browse(Connection $conn, Request $request)
    {
        // Database tables are only created if not present.
        
        $ret = $this->repo_storage_controller->build('createTable', array('table_name' => $this->table_name));

        return $this->render('resources/browse_dataset_types.html.twig', array(
            'page_title' => "Browse Dataset Types",
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
    }

    /**
     * @Route("/admin/resources/dataset_types/datatables_browse_dataset_types", name="dataset_types_browse_datatables", methods="POST")
     *
     * Browse Dataset Types
     *
     * Run a query to retrieve all Dataset Types in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_dataset_types(Request $request)
    {
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

        $query_params = array(
          'record_type' => 'dataset_type',
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
     * Matches /admin/resources/dataset_types/manage/*
     *
     * @Route("/admin/resources/dataset_types/manage/{id}", name="dataset_types_manage", methods={"GET","POST"}, defaults={"id" = null})
     *
     * @param   int     $id           The dataset_type ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_dataset_types_form(Connection $conn, Request $request, GumpParseErrors $gump_parse_errors)
    {
      $username = $this->getUser()->getUsernameCanonical();
      $access = $this->repo_user_access->get_user_access_any($username, 'create_edit_lookups');

      if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
      }

        $errors = false;
        $data = array();
        $gump = new GUMP();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        
        if(empty($post)) {
          $data = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'dataset_type',
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

            $this->addFlash('message', 'Dataset Type successfully updated.');
            return $this->redirectToRoute('dataset_types_browse');
        } else {
            return $this->render('resources/dataset_types_form.html.twig', array(
                "page_title" => !empty($id) ? 'Manage Dataset Type: ' . $data['label'] : 'Create Dataset Type'
                ,"data" => $data
                ,"errors" => $errors
                ,'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
            ));
        }

    }

    /**
     * Delete Multiple Dataset Types
     *
     * @Route("/admin/resources/dataset_types/delete", name="dataset_types_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple(Request $request)
    {
      $username = $this->getUser()->getUsernameCanonical();
      $access = $this->repo_user_access->get_user_access_any($username, 'create_edit_lookups');

      if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
      }

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

      return $this->redirectToRoute('dataset_types_browse');
    }

}
