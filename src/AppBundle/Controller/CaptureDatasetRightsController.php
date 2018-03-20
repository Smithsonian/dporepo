<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use PDO;

use AppBundle\Form\CaptureDatasetRightsForm;
use AppBundle\Entity\CaptureDatasetRights;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class CaptureDatasetRightsController extends Controller
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
     * @Route("/admin/projects/capture_dataset_rights/datatables_browse", name="capture_dataset_rights_browse_datatables", methods="POST")
     *
     * @param Connection $conn
     * @param Request $request
     * @return JsonResponse The query result in JSON
     */
    public function datatablesBrowse(Connection $conn, Request $request)
    {
        $data = new CaptureDatasetRights();

        $params = array();
        $params['pdo_params'] = array();
        $params['search_sql'] = $params['sort'] = '';

        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $params['limit_sql'] = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $params['sort'] = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $params['sort'] = " ORDER BY capture_dataset_rights.last_modified DESC ";
        }

        if ($search) {
            $params['pdo_params'][] = '%' . $search . '%';
            $params['pdo_params'][] = '%' . $search . '%';
            $params['pdo_params'][] = '%' . $search . '%';
            $params['search_sql'] = "
                AND (
                  capture_dataset_rights.data_rights_restriction LIKE ?
                  capture_dataset_rights.start_date LIKE ?
                  capture_dataset_rights.end_date LIKE ?
                ) ";
        }

        // Run the query
        $results = $data->datatablesQuery($params);

        return $this->json($results);
    }

    /**
     * Matches /admin/projects/capture_dataset_rights/manage/*
     *
     * @Route("/admin/projects/capture_dataset_rights/manage/{parent_id}/{id}", name="capture_dataset_rights_manage", methods={"GET","POST"}, defaults={"parent_id" = null, "id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    function formView(Connection $conn, Request $request)
    {
        $data = new CaptureDatasetRights();
        $post = $request->request->all();
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        //$data = (!empty($id) && empty($post)) ? $data->getOne((int)$id, $conn) : $data;
        $this->repo_storage_controller->setContainer($this->container);

        if(!empty($id) && empty($post)) {
          $data = $this->repo_storage_controller->execute('getRecord', array(
            'base_table' => 'capture_dataset_rights',
            'id_field' => 'capture_dataset_rights_repository_id',
            'id_value' => $id));
        }

        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Add the parent_id to the $data object
        $data->parent_capture_dataset_repository_id = $parent_id;
        
        // Create the form
        $form = $this->createForm(CaptureDatasetRightsForm::class, $data);
        
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $id = $data->insertUpdate($data, $id, $this->getUser()->getId(), $conn);

            $this->addFlash('message', 'Record successfully updated.');
            return $this->redirect('/admin/projects/capture_dataset_rights/manage/' . $data->parent_capture_dataset_repository_id . '/' . $id);
        }

        return $this->render('datasets/capture_dataset_rights_form.html.twig', array(
            'page_title' => !empty($id) ? 'Capture Dataset Rights: ' . $data->data_rights_restriction : 'Create Capture Dataset Rights',
            'data' => $data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/projects/capture_dataset_rights/delete", name="capture_dataset_rights_remove_records", methods={"GET"})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    public function deleteMultiple(Connection $conn, Request $request)
    {
        $data = new CaptureDatasetRights();

        if(!empty($request->query->get('ids'))) {

            // Create the array of ids.
            $ids_array = explode(',', $request->query->get('ids'));

            // Loop thorough the ids.
            foreach ($ids_array as $key => $id) {
                // Run the query against a single record.
                $data->deleteMultiple($id);
            }

            $this->addFlash('message', 'Records successfully removed.');

        } else {
            $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('capture_dataset_rights_browse');
    }

    /**
     * /\/\/\/ Route("/admin/projects/capture_dataset_rights/{id}", name="capture_dataset_rights_browse", methods="GET", defaults={"id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     */
    // public function browse(Connection $conn, Request $request)
    // {
    //     $captureDatasetRights = new CaptureDatasetRights;

    //     // Database tables are only created if not present.
    //     $captureDatasetRights->createTable();

    //     return $this->render('datasetElements/capture_dataset_rights_browse.html.twig', array(
    //         'page_title' => "Browse Capture Dataset Rights",
    //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
    //     ));
    // }
  
}
