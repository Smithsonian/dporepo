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
        $this->table_name = 'capture_method';
        $this->id_field_name_raw = 'capture_method_repository_id';
        $this->id_field_name = 'capture_method.' . $this->id_field_name_raw;
        $this->label_field_name_raw = 'label';
        $this->label_field_name = 'capture_method.' . $this->label_field_name_raw;
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
     * Run a query to retrieve all capture methods in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_capture_methods(Request $request)
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
          'record_type' => 'capture_method',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $this->repo_storage_controller->setContainer($this->container);
        $data = $this->repo_storage_controller->execute('getDatatable', $query_params);

        return $this->json($data);
    }

    /**
     * Matches /admin/resources/capture_methods/manage/*
     *
     * @Route("/admin/resources/capture_methods/manage/{id}", name="capture_methods_manage", methods={"GET","POST"}, defaults={"id" = null})
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
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        $this->repo_storage_controller->setContainer($this->container);
        if(empty($post)) {
          $data = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'capture_method',
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
   * Delete Multiple Capture Methods
   *
   * @Route("/admin/resources/capture_methods/delete", name="capture_methods_remove_records", methods={"GET"})
   * Run a query to delete multiple records.
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

        $this->repo_storage_controller->setContainer($this->container);

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

      return $this->redirectToRoute('capture_methods_browse');
  }


}
