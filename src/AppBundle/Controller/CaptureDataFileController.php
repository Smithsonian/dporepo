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

use AppBundle\Form\CaptureDataFileForm;
use AppBundle\Entity\CaptureDataFile;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class CaptureDataFileController extends Controller
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
     * @Route("/admin/projects/capture_data_files/datatables_browse", name="capture_data_files_browse_datatables", methods="POST")
     *
     * @param Connection $conn
     * @param Request $request
     * @return JsonResponse The query result in JSON
     */
    public function datatablesBrowse(Connection $conn, Request $request)
    {

        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $this->repo_storage_controller->setContainer($this->container);

        $query_params = array(
          'fields' => array(),
          'base_table' => 'capture_data_file',
          'search_params' => array(
            0 => array('field_names' => array('capture_data_file.active'), 'search_values' => array(1), 'comparison' => '='),
          ),
          'search_type' => 'AND',
        );

        if ($search) {
          $query_params['search_params'][1] = array(
            'field_names' => array(
              'capture_data_file.capture_data_file_name',
              'capture_data_file.capture_data_file_type',
              'capture_data_file.is_compressed_multiple_files'
            ),
            'search_values' => array($search),
            'comparison' => 'LIKE',
          );
        }

        // Fields.
        $query_params['fields'][] = array(
          'table_name' => 'capture_data_file',
          'field_name' => 'capture_data_file_repository_id',
          'field_alias' => 'manage',
        );
        $query_params['fields'][] = array(
          'table_name' => 'capture_data_file',
          'field_name' => 'capture_data_file_repository_id',
          'field_alias' => 'DT_RowId',
        );
        $query_params['fields'][] = array(
          'field_name' => 'capture_data_file_name',
        );
        $query_params['fields'][] = array(
          'field_name' => 'capture_data_file_type',
        );
        $query_params['fields'][] = array(
          'field_name' => 'is_compressed_multiple_files',
        );
        $query_params['fields'][] = array(
          'field_name' => 'active',
        );
        $query_params['fields'][] = array(
          'table_name' => 'capture_data_file',
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => 'projects',
          'field_name' => 'last_modified',
        );
        $query_params['fields'][] = array(
          'table_name' => 'projects',
          'field_name' => 'last_modified_user_account_id',
        );
        $query_params['fields'][] = array(
          'table_name' => 'isni_data',
          'field_name' => 'isni_label',
          'field_alias' => 'stakeholder_label',
        );
        $query_params['fields'][] = array(
          'table_name' => 'unit_stakeholder',
          'field_name' => 'unit_stakeholder_id',
          'field_alias' => 'stakeholder_si_guid',
        );

        $query_params['records_values'] = array();

        $query_params['limit'] = array(
          'limit_start' => $start_record,
          'limit_stop' => $stop_record,
        );

        if (!empty($sort_field) && !empty($sort_order)) {
          $query_params['sort_fields'][] = array(
            'field_name' => $sort_field,
            'sort_order' => $sort_order,
          );
        } else {
          $query_params['sort_fields'][] = array(
            'field_name' => 'capture_data_file.last_modified',
            'sort_order' => 'DESC',
          );
        }

        $data = $this->repo_storage_controller->execute('getRecordsDatatable', $query_params);

        return $this->json($data);
    }

    /**
     * Matches /admin/projects/capture_data_files/manage/*
     *
     * @Route("/admin/projects/capture_data_files/manage/{parent_id}/{id}", name="capture_data_files_manage", methods={"GET","POST"}, defaults={"parent_id" = null, "id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    function formView(Connection $conn, Request $request)
    {
        $data = new CaptureDataFile();
        $post = $request->request->all();
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        $this->repo_storage_controller->setContainer($this->container);
        if(!empty($id) && empty($post)) {
            $rec = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'capture_data_file',
            'record_id' => $id));
            if(isset($rec)) {
              $data = (object)$rec;
            }
        }
        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Add the parent_id to the $data object
        $data->parent_capture_data_element_repository_id = $parent_id;
        
        // Create the form
        $form = $this->createForm(CaptureDataFileForm::class, $data);
        
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $id = $data->insertUpdate($data, $id, $this->getUser()->getId(), $conn);

            $this->addFlash('message', 'Record successfully updated.');
            return $this->redirect('/admin/projects/capture_data_files/manage/' . $data->parent_capture_data_element_repository_id . '/' . $id);
        }

        return $this->render('datasetElements/capture_data_file_form.html.twig', array(
            'page_title' => !empty($id) ? 'Capture Data File: ' . $data->capture_data_file_name : 'Create Capture Data File',
            'data' => $data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/projects/capture_data_files/delete", name="capture_data_files_remove_records", methods={"GET"})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    public function deleteMultiple(Connection $conn, Request $request)
    {
        if(!empty($request->query->get('ids'))) {

            // Create the array of ids.
            $ids_array = explode(',', $request->query->get('ids'));

            $this->repo_storage_controller->setContainer($this->container);

            // Loop thorough the ids.
            foreach ($ids_array as $key => $id) {
              // Run the query against a single record.
              $ret = $this->repo_storage_controller->execute('markRecordsInactive', array(
                'record_type' => 'capture_data_file',
                'record_id' => $id,
                'user_id' => $this->getUser()->getId(),
              ));
            }

            $this->addFlash('message', 'Records successfully removed.');

        } else {
            $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('capture_data_files_browse');
    }

    /**
     * /\/\/\/ Route("/admin/projects/capture_data_files/{id}", name="capture_data_files_browse", methods="GET", defaults={"id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     */
    // public function browse(Connection $conn, Request $request)
    // {
    //     $captureDataFile = new CaptureDataFile;

    //     // Database tables are only created if not present.
    //     $captureDataFile->createTable();

    //     return $this->render('datasetElements/capture_data_files_browse.html.twig', array(
    //         'page_title' => "Browse Capture Data Files",
    //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
    //     ));
    // }
  
}
