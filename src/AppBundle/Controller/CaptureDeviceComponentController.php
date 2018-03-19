<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use PDO;

use AppBundle\Form\CaptureDeviceComponentForm;
use AppBundle\Entity\CaptureDeviceComponent;

// Custom utility bundle
use AppBundle\Utils\AppUtilities;

class CaptureDeviceComponentController extends Controller
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
    }

    /**
     * @Route("/admin/projects/capture_device_component/datatables_browse", name="capture_device_component_browse_datatables", methods="POST")
     *
     * @param Connection $conn
     * @param Request $request
     * @return JsonResponse The query result in JSON
     */
    public function datatablesBrowse(Connection $conn, Request $request)
    {
        $data = new CaptureDeviceComponent();

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
            $params['sort'] = " ORDER BY capture_device_component.last_modified DESC ";
        }

        if ($search) {
            $params['pdo_params'][] = '%' . $search . '%';
            $params['pdo_params'][] = '%' . $search . '%';
            $params['pdo_params'][] = '%' . $search . '%';
            $params['pdo_params'][] = '%' . $search . '%';
            $params['search_sql'] = "
                AND (
                  capture_device_component.serial_number LIKE ?
                  capture_device_component.capture_device_component_type LIKE ?
                  capture_device_component.manufacturer LIKE ?
                  capture_device_component.model_name LIKE ?
                ) ";
        }

        // Run the query
        $results = $data->datatablesQuery($params);

        return $this->json($results);
    }

    /**
     * Matches /admin/projects/capture_device_component/manage/*
     *
     * @Route("/admin/projects/capture_device_component/manage/{parent_id}/{id}", name="capture_device_component_manage", methods={"GET","POST"}, defaults={"parent_id" = null, "id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    function formView(Connection $conn, Request $request)
    {
        $data = new CaptureDeviceComponent();
        $post = $request->request->all();
        $parent_id = !empty($request->attributes->get('parent_id')) ? $request->attributes->get('parent_id') : false;
        $id = !empty($request->attributes->get('id')) ? $request->attributes->get('id') : false;

        // If no parent_id is passed, throw a createNotFoundException (404).
        if(!$parent_id) throw $this->createNotFoundException('The record does not exist');

        // Retrieve data from the database, and if the record doesn't exist, throw a createNotFoundException (404).
        $data = (!empty($id) && empty($post)) ? $data->getOne((int)$id, $conn) : $data;
        if(!$data) throw $this->createNotFoundException('The record does not exist');

        // Add the parent_id to the $data object
        $data->parent_capture_device_repository_id = $parent_id;
        
        // Create the form
        $form = $this->createForm(CaptureDeviceComponentForm::class, $data);
        
        // Handle the request
        $form->handleRequest($request);
        
        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $data = $form->getData();
            $id = $data->insertUpdate($data, $id, $this->getUser()->getId(), $conn);

            $this->addFlash('message', 'Record successfully updated.');
            return $this->redirect('/admin/projects/capture_device_component/manage/' . $data->parent_capture_device_repository_id . '/' . $id);
        }

        return $this->render('datasetElements/capture_device_component_form.html.twig', array(
            'page_title' => !empty($id) ? 'Capture Device Component: ' . $data->serial_number : 'Create Capture Device Component',
            'data' => $data,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/admin/projects/capture_device_component/delete", name="capture_device_component_remove_records", methods={"GET"})
     *
     * @param Connection $conn
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response Redirect or render
     */
    public function deleteMultiple(Connection $conn, Request $request)
    {
        $data = new CaptureDeviceComponent();

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

        return $this->redirectToRoute('capture_device_component_browse');
    }

    /**
     * /\/\/\/ Route("/admin/projects/capture_device_component/{id}", name="capture_device_component_browse", methods="GET", defaults={"id" = null})
     *
     * @param Connection $conn
     * @param Request $request
     */
    // public function browse(Connection $conn, Request $request)
    // {
    //     $CaptureDeviceComponent = new CaptureDeviceComponent;

    //     // Database tables are only created if not present.
    //     $CaptureDeviceComponent->createTable();

    //     return $this->render('datasetElements/capture_device_component_browse.html.twig', array(
    //         'page_title' => "Browse Capture Device Component",
    //         'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
    //     ));
    // }
  
}
