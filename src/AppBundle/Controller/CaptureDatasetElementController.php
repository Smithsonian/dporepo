<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use AppBundle\Controller\RepoStorageHybridController;
use PDO;

use AppBundle\Form\CaptureDatasetElementForm;
use AppBundle\Entity\CaptureDatasetElement;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;
use AppBundle\Service\RepoUserAccess;

class CaptureDatasetElementController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;
    private $repo_user_access;

    /**
     * @var string $uploads_path
     */
    private $uploads_path;

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
        $this->uploads_path = '/uploads/repository';
    }

    /**
     * @Route("/admin/datatables_browse_dataset_elements/{capture_dataset_id}", name="dataset_elements_browse_datatables", methods="POST")
     *
     * Browse dataset_elements
     *
     * Run a query to retrieve all dataset elements in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatablesBrowseDatasetElements(Request $request)
    {
        $req = $request->request->all();
        $id = !empty($request->attributes->get('capture_dataset_id')) ? $request->attributes->get('capture_dataset_id') : false;

        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        $query_params = array(
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }
        if ($id) {
          $query_params['capture_dataset_id'] = $id;
        }

        
        $data = $this->repo_storage_controller->execute('getDatatableCaptureDataElement', $query_params);

        return $this->json($data);
    }

    /**
     * Matches /admin/dataset_element/*
     *
     * @Route("/admin/dataset_element/add/{capture_dataset_id}", name="dataset_elements_add", methods={"GET","POST"}, defaults={"capture_data_element_id" = null})
     * @Route("/admin/dataset_element/manage/{capture_data_element_id}", name="dataset_elements_manage", methods={"GET","POST"})
     *
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function showDatasetElementsForm( Connection $conn, Request $request, SubjectController $subjects, ItemController $items, CaptureDatasetController $datasets )
    {

        $dataset_element = new CaptureDatasetElement();
        $post = $request->request->all();
        $id = !empty($request->attributes->get('capture_data_element_id')) ? $request->attributes->get('capture_data_element_id') : false;

        // Retrieve data from the database.
        if (!empty($id) && empty($post)) {

          $dataset_element_array = $this->repo_storage_controller->execute('getCaptureDataElement', array(
            'capture_data_element_id' => $id,
          ));
          if(is_array($dataset_element_array) && !empty($dataset_element_array)) {
            $dataset_element = (object)$dataset_element_array;
          }
        }

        // Get the parent project ID.
        $parent_records = $this->repo_storage_controller->execute('getParentRecords', array(
            'base_record_id' => $id,
            'record_type' => 'capture_data_element',
        ));

        if (!empty($id)) {
            // Check user's permissions.
            $dataset_element->user_can_create = $dataset_element->user_can_edit = false;
            if(is_array($parent_records) && array_key_exists('project_id', $parent_records)) {
                $username = $this->getUser()->getUsernameCanonical();
                // Check if user has permission to access this page.
                $access = $this->repo_user_access->get_user_access($username, 'view_project_details', $parent_records['project_id']);
                if(!array_key_exists('project_ids', $access) || !isset($access['project_ids'])) {
                  $response = new Response();
                  $response->setStatusCode(403);
                  return $response;
                }
                // Check if user has permission to create content.
                $access = $this->repo_user_access->get_user_access($username, 'create_project_details', $parent_records['project_id']);
                if(array_key_exists('project_ids', $access) && in_array($parent_records['project_id'], $access['project_ids'])) {
                  $dataset_element->user_can_create = true;
                }
                // Check if user has permission to edit content.
                $access = $this->repo_user_access->get_user_access($username, 'edit_project_details', $parent_records['project_id']);
                if(array_key_exists('project_ids', $access) && in_array($parent_records['project_id'], $access['project_ids'])) {
                  $dataset_element->user_can_edit = true;
                }
            }
        }

        $dataset_element->capture_dataset_id = !empty($request->attributes->get('capture_dataset_id')) ? $request->attributes->get('capture_dataset_id') : $dataset_element->capture_dataset_id;
        
        // Get data for the breadcumbs.
        // TODO: find a better way?
        $dataset_data = $datasets->getDataset((int)$dataset_element->capture_dataset_id);
        $item_data = $items->getItem((int)$dataset_data['item_id']);
        $subject_data = $subjects->getSubject((int)$item_data['subject_id']);
        $project_data = $this->repo_storage_controller->execute('getProject', array('project_id' => (int)$item_data['project_id']));

        // Truncate the item_description so the breadcrumb don't blow up.
        $more_indicator = (strlen($item_data['item_description']) > 50) ? '...' : '';
        $item_data['item_description_truncated'] = substr($item_data['item_description'], 0, 50) . $more_indicator;

        // Create the form
        $form = $this->createForm(CaptureDatasetElementForm::class, $dataset_element);
        // Handle the request
        $form->handleRequest($request);

        // If form is submitted and passes validation, insert/update the database record.
        if ($form->isSubmitted() && $form->isValid()) {

            $dataset_element = $form->getData();
            $dataset_array = (array)$dataset_element;

            $id = $this->repo_storage_controller->execute('saveRecord', array(
              'base_table' => 'capture_data_element',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
              'values' => $dataset_array
            ));

            $this->addFlash('message', 'Capture Data Element successfully updated.');
            return $this->redirectToRoute('dataset_elements_browse', array('project_id' => $dataset_element->project_id,
              'subject_id' => $dataset_element->subject_id, 'item_id' => $dataset_element->item_id,
              'capture_dataset_id' => $dataset_element->capture_dataset_id));

        }

        return $this->render('datasetElements/dataset_element_form.html.twig', array(
            // 'page_title' => ((int)$id && isset($dataset_element->capture_sequence_number)) ? 'Capture Data Element: ' . $dataset_element->capture_sequence_number : 'Add a Capture Data Element',
            'page_title' => 'Capture Data Element',
            'project_id' => $item_data['project_id'],
            'subject_id' => $item_data['subject_id'],
            'item_id' => $item_data['item_id'],
            'capture_dataset_id' => $dataset_element->capture_dataset_id,
            'project_data' => $project_data,
            'subject_data' => $subject_data,
            'item_data' => $item_data,
            'dataset_data' => $dataset_data,
            'dataset_element_data' => $dataset_element,
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            'form' => $form->createView(),
            'user_can_create' => $dataset_element->user_can_create,
            'user_can_edit' => $dataset_element->user_can_edit,
        ));

    }

    /**
     * Get Dataset Elements
     *
     * Get dataset elements from the database.
     *
     * @param       int $capture_dataset_id  The dataset ID
     * @return      array|bool        The query result
     */
    public function getDatasetElements($capture_dataset_id = false)
    {
        $query_params = array(
          'capture_dataset_id' => $capture_dataset_id,
        );
        $data = $this->repo_storage_controller->execute('getElementsForCaptureDataset', $query_params);
        return $data;
    }

    /**
     * Get Dataset Elements (for the tree browser)
     *
     * @Route("/admin/projects/get_dataset_elements/{capture_dataset_id}", name="get_dataset_elements_tree_browser", methods="GET")
     */
    public function getDatasetElementsTreeBrowser(Request $request)
    {
        $capture_dataset_id = !empty($request->attributes->get('capture_dataset_id')) ? $request->attributes->get('capture_dataset_id') : false;
        $capture_data_element = $this->getDatasetElements($capture_dataset_id);

        foreach ($capture_data_element as $key => $value) {
            $data[$key] = array(
                'id' => 'datasetElementId-' . $value['capture_data_element_id'],
                'children' => false,
                'text' => $value['capture_sequence_number'],
                'a_attr' => array('href' => '/admin/projects/dataset_element/' . $value['project_id'] . '/' . $value['subject_id'] . '/' . $value['item_id'] . '/' . $value['capture_dataset_id'] . '/' . $value['capture_data_element_id']),
            );
        }

        $response = new JsonResponse($data);
        return $response;
    }

    /**
     * Get Dataset Element
     *
     * Get one dataset element from the database.
     *
     * @param       int $capture_data_element_id  The dataset element ID
     * @return      array|bool                The query result
     */
    public function getDatasetElement($capture_data_element_id = false)
    {
        $record = $this->repo_storage_controller->execute('getRecordById',
          array(
            'base_table' => 'capture_data_element',
            'id_field' => 'capture_data_element_id',
            'id_value' => $capture_data_element_id
          )
        );
        return $record;
    }

    /**
     * Delete Multiple Capture Data Elements
     *
     * @Route("/admin/capture_dataset_element/delete", name="dataset_elements_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $request  Request object
     * @return  void
     */
    public function deleteMultipleCaptureDataElement(Request $request)
    {
      $username = $this->getUser()->getUsernameCanonical();
      $access = $this->repo_user_access->get_user_access_any($username, 'create_edit_lookups');

      if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
        $response = new Response();
        $response->setStatusCode(403);
        return $response;
      }

        $ids = $request->query->get('ids');
        //$capture_dataset_id = !empty($request->attributes->get('capture_dataset_id')) ? $request->attributes->get('capture_dataset_id') : false;


        if(!empty($ids)) {

          $ids_array = explode(',', $ids);

          // Loop thorough the ids.
          foreach ($ids_array as $key => $id) {
            // Run the query against a single record.
            $ret = $this->repo_storage_controller->execute('markRecordInactive', array(
              'record_type' => 'capture_data_element',
              'record_id' => $id,
              'user_id' => $this->getUser()->getId(),
            ));
          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

      $referer = $request->headers->get('referer');
      return $this->redirect($referer);
     // return $this->redirectToRoute('dataset_elements_browse', array('capture_dataset_id' => $capture_dataset_id));
    }

}
