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

use AppBundle\Form\PhotogrammetryScaleBarForm;
use AppBundle\Entity\PhotogrammetryScaleBar;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class PhotogrammetryScaleBarController extends Controller
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
     * @Route("/admin/projects/photogrammetry_scale_bar/datatables_browse", name="photogrammetry_scale_bar_browse_datatables", methods="POST")
     *
     * @param Connection $conn
     * @param Request $request
     * @return JsonResponse The query result in JSON
     */
    public function datatablesBrowse(Connection $conn, Request $request)
    {
        $data = new PhotogrammetryScaleBar();

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
            $params['sort'] = " ORDER BY photogrammetry_scale_bar.last_modified DESC ";
        }

        if ($search) {
            $params['pdo_params'][] = '%' . $search . '%';
            $params['pdo_params'][] = '%' . $search . '%';
            $params['pdo_params'][] = '%' . $search . '%';
            $params['pdo_params'][] = '%' . $search . '%';
            $params['search_sql'] = "
                AND (
                  photogrammetry_scale_bar.scale_bar_id LIKE ?
                  photogrammetry_scale_bar.scale_bar_manufacturer LIKE ?
                  photogrammetry_scale_bar.scale_bar_barcode_type LIKE ?
                  photogrammetry_scale_bar.scale_bar_target_pairs LIKE ?
                ) ";
        }

        // Run the query
        $results = $data->datatablesQuery($params);

        return $this->json($results);
    }

    /**
     * Matches /admin/projects/photogrammetry_scale_bar/manage/*
     *
     * @Route("/admin/projects/photogrammetry_scale_bar/manage/{parent_id}/{id}", name="photogrammetry_scale_bar_manage", methods={"GET","POST"}, defaults={"parent_id" = null, "id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    function formView(Connection $conn, Request $request)
    {
        $data = new PhotogrammetryScaleBar();
        $post = $request->request->all();
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        $this->repo_storage_controller->setContainer($this->container);
        if(!empty($id) && empty($post)) {
          $rec = $this->repo_storage_controller->execute('getRecordById', array(
            'record_type' => 'photogrammetry_scale_bar',
            'record_id' => $id));
          if(isset($rec)) {
            $data = (object)$rec;
          }
        }
        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Add the parent_id to the $data object
        $data->parent_capture_dataset_repository_id = $parent_id;
        
        // Create the form
        $form = $this->createForm(PhotogrammetryScaleBarForm::class, $data);
        
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $id = $data->insertUpdate($data, $id, $this->getUser()->getId(), $conn);

            $this->addFlash('message', 'Record successfully updated.');
            return $this->redirect('/admin/projects/photogrammetry_scale_bar/manage/' . $data->parent_capture_dataset_repository_id . '/' . $id);
        }

        return $this->render('datasets/photogrammetry_scale_bar_form.html.twig', array(
            'page_title' => !empty($id) ? 'Photogrammetry Scale Bar: ' . $data->scale_bar_id : 'Create Photogrammetry Scale Bar',
            'data' => $data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/projects/photogrammetry_scale_bar/delete", name="photogrammetry_scale_bar_remove_records", methods={"GET"})
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
                'record_type' => 'photogrammetry_scale',
                'record_id' => $id,
                'user_id' => $this->getUser()->getId(),
              ));
            }

            $this->addFlash('message', 'Records successfully removed.');

        } else {
            $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('photogrammetry_scale_bar_browse');
    }

    /**
     * /\/\/\/ Route("/admin/projects/photogrammetry_scale_bar/{id}", name="photogrammetry_scale_bar_browse", methods="GET", defaults={"id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     */
    // public function browse(Connection $conn, Request $request)
    // {
    //     $PhotogrammetryScaleBar = new PhotogrammetryScaleBar;

    //     // Database tables are only created if not present.
    //     $PhotogrammetryScaleBar->createTable();

    //     return $this->render('datasetElements/photogrammetry_scale_bar_browse.html.twig', array(
    //         'page_title' => "Browse Photogrammetry Scale Bar",
    //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
    //     ));
    // }
  
}
