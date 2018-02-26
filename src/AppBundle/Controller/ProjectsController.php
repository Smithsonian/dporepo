<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use PDO;
use GUMP;

// Custom utility bundles
use AppBundle\Utils\GumpParseErrors;
use AppBundle\Utils\AppUtilities;

// Subjects methods
use AppBundle\Controller\SubjectsController;
use AppBundle\Controller\IsniController;

class ProjectsController extends Controller
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
     * @Route("/admin/workspace/", name="projects_browse", methods="GET")
     */
    public function browse_projects(Connection $conn, Request $request, IsniController $isni)
    {
        // Database tables are only created if not present.
        $create_projects_table = $this->create_projects_table($conn);
        $create_isni_table = $isni->create_isni_table($conn);

        return $this->render('projects/browse_projects.html.twig', array(
            'page_title' => 'Browse Projects',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_projects", name="projects_browse_datatables", methods="POST")
     *
     * Browse Projects
     *
     * Run a query to retreive all projects in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_projects(Connection $conn, Request $request)
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
            case '0':
                $sort_field = 'projects_label';
                break;
            case '1':
                $sort_field = 'stakeholder_label';
                break;
            case '2':
                $sort_field = 'subjects_count';
                break;
            case '3':
                $sort_field = 'date_created';
                break;
            case '4':
                $sort_field = 'last_modified';
                break;
        }

        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY projects.last_modified DESC ";
        }

        if ($search) {
            $pdo_params[] = '%' . $search . '%';
            $pdo_params[] = '%' . $search . '%';
            $pdo_params[] = '%' . $search . '%';
            $pdo_params[] = '%' . $search . '%';
            $search_sql = "
            AND (
                projects.projects_label LIKE ?
                OR projects.stakeholder_label LIKE ?
                OR projects.date_created LIKE ?
                OR projects.last_modified LIKE ?
            ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
                projects.projects_id as manage
                ,projects.projects_label
                ,projects.stakeholder_guid
                ,projects.date_created
                ,projects.last_modified
                ,projects.active
                ,projects.projects_id AS DT_RowId
                ,count(distinct subjects.subjects_id) AS subjects_count
                ,isni_data.isni_label AS stakeholder_label
            FROM projects
            LEFT JOIN isni_data ON isni_data.isni_id = projects.stakeholder_guid
            LEFT JOIN subjects ON subjects.projects_id = projects.projects_id
            WHERE projects.active = 1
            {$search_sql}
            GROUP BY projects.projects_label, projects.stakeholder_guid, projects.date_created, projects.last_modified, projects.active, projects.projects_id
            {$sort}
            {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

        $statement = $conn->prepare("SELECT FOUND_ROWS()");
        $statement->execute();
        $count = $statement->fetch();
        $data["iTotalRecords"] = $count["FOUND_ROWS()"];
        $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];
        
        return $this->json($data);
    }

    /**
     * Matches /admin/projects/manage/*
     *
     * @Route("/admin/projects/manage/{projects_id}", name="projects_manage", methods={"GET","POST"}, defaults={"projects_id" = null})
     *
     * @param   int     $projects_id  The project ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_projects_form( $projects_id, Connection $conn, Request $request, GumpParseErrors $gump_parse_errors, IsniController $isni )
    {
        $errors = false;
        $project_data = array();
        $gump = new GUMP();
        $post = $request->request->all();
        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $project_data = !empty($post) ? $post : $this->get_project((int)$projects_id, $conn);

        // $this->u->dumper($project_data);

        // Get data from lookup tables.
        $project_data['units_stakeholders'] = $this->get_units_stakeholders($conn);

        // $this->u->dumper($project_data['units_stakeholders']);

        // Processing statuses.
        if(isset($project_data['active'])) {
            switch($project_data['active']) {
                case '0':
                    $project_data['status_class'] = 'warning';
                    $project_data['active'] = 'In Queue';
                    break;
                case '1':
                    $project_data['status_class'] = 'info';
                    $project_data['active'] = 'Processing';
                    break;
                case '2':
                    $project_data['status_class'] = 'success';
                    $project_data['active'] = 'Processed';
                    break;
            }
        }
        
        // Validate posted data.
        if(!empty($post)) {
            // "" => "required|numeric",
            // "" => "required|alpha_numeric",
            // "" => "required|date",
            // "" => "numeric|exact_len,5",
            // "" => "required|max_len,255|alpha_numeric",
            $rules = array(
                "projects_label" => "required|max_len,255",
                "stakeholder_label" => "required|max_len,255",
                "stakeholder_guid" => "required|max_len,255",
                "stakeholder_si_guid" => "required|max_len,255",
                "project_description" => "required",
            );
            $validated = $gump->validate($post, $rules);

            // <input name="isni_label" id="isni_label" value="National Air and Space Museum" type="hidden">
            // <input name="isni_guid" id="isni_guid" value="0000000122858065" type="hidden">
            // <input name="si_guid" id="si_suid" value="200" type="hidden">

            $errors = array();
            if ($validated !== true) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
            $projects_id = $this->insert_update_project($post, $projects_id, $conn, $isni);
            $this->addFlash('message', 'Project successfully updated.');
            return $this->redirectToRoute('projects_browse');
        } else {
            return $this->render('projects/project_form.html.twig', array(
                "page_title" => !empty($projects_id) ? 'Project: ' . $project_data['projects_label'] : 'Create Project'
                ,"project_info" => $project_data
                ,"errors" => $errors
                ,'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn)
            ));
        }

    }

    /**
     * Get Project
     *
     * Run a query to retrieve one project from the database.
     *
     * @param   int $project_id  The project ID
     * @return  array|bool       The query result
     */
    public function get_project($project_id, $conn)
    {
        $statement = $conn->prepare("SELECT 
            projects.projects_id,
            projects.projects_label,
            projects.stakeholder_guid,
            projects.stakeholder_si_guid,
            projects.project_description,
            projects.date_created,
            projects.created_by_user_account_id,
            projects.last_modified,
            projects.last_modified_user_account_id,
            isni_data.isni_label AS stakeholder_label
            FROM projects
            LEFT JOIN isni_data ON isni_data.isni_id = projects.stakeholder_guid
            WHERE projects.active = 1
            AND projects_id = :projects_id");
        $statement->bindValue(":projects_id", $project_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Get Projects
     *
     * Run a query to retrieve all projects from the database.
     *
     * @param   object  $conn  Database connection object
     * @return  array|bool     The query result
     */
    public function get_projects($conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM projects
            ORDER BY projects.stakeholder_guid ASC
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Projects By Stakeholder GUID
     *
     * Run a query to retrieve all projects by a stakeholder GUID.
     *
     * @param   object  $conn              Database connection object
     * @param   string  $stakeholder_guid  Stakeholder GUID
     * @return  array|bool                 The query result
     */
    public function get_projects_by_stakeholder_guid($conn, $stakeholder_guid)
    {
        $statement = $conn->prepare("
            SELECT * FROM projects
            WHERE projects.stakeholder_guid = :stakeholder_guid
            AND projects.active = 1
            ORDER BY projects.projects_label ASC
        ");
        $statement->bindValue(":stakeholder_guid", $stakeholder_guid, PDO::PARAM_STR);
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Stakeholder GUIDs
     *
     * Run a query to retrieve all Stakeholder GUIDs from the database.
     *
     * @return  array|bool  The query result
     */
    public function get_stakeholder_guids($conn)
    {
        // $statement_fgb = $conn->prepare("
        //     SET GLOBAL sql_mode=(SELECT REPLACE(@@sql_mode,'ONLY_FULL_GROUP_BY',''));
        // ");
        // $statement_fgb->execute();

        $statement = $conn->prepare("
            SELECT projects.projects_id
                ,projects.stakeholder_guid
                ,isni_data.isni_label AS stakeholder_label
            FROM projects
            LEFT JOIN isni_data ON isni_data.isni_id = projects.stakeholder_guid
            WHERE projects.active = 1
            GROUP BY isni_data.isni_label
            ORDER BY isni_data.isni_label ASC
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get Stakeholder GUIDs (route for the tree browser)
     *
     * @Route("/admin/projects/get_stakeholder_guids", name="get_stakeholder_guids_tree_browser", methods="GET")
     */
    public function get_stakeholder_guids_tree_browser(Connection $conn)
    {
      $projects = $this->get_stakeholder_guids($conn);

      foreach ($projects as $key => $value) {
          $data[$key]['id'] = 'stakeholderGuid-' . $value['stakeholder_guid'];
          $data[$key]['text'] = $value['stakeholder_label'];
          $data[$key]['children'] = true;
      }

      $response = new JsonResponse($data);
      return $response;
    }

    /**
     * Get a Stakeholder's Projects' (route for the tree browser)
     *
     * @Route("/admin/projects/get_stakeholder_projects/{stakeholder_guid}", name="get_stakeholder_projects_tree_browser", methods="GET")
     */
    public function get_stakeholder_projects_tree_browser(Connection $conn, Request $request, SubjectsController $subjects)
    {
        $data = array();
        $stakeholder_guid = !empty($request->attributes->get('stakeholder_guid')) ? $request->attributes->get('stakeholder_guid') : false;
        $projects = $this->get_projects_by_stakeholder_guid($conn, $stakeholder_guid);

        foreach ($projects as $key => $value) {

            // Check for child dataset records so the 'children' key can be set accordingly.
            $subject_data = $subjects->get_subjects($conn, (int)$value['projects_id']);

            $data[$key] = array(
                'id' => 'projectId-' . $value['projects_id'],
                'text' => $value['projects_label'],
                'children' => count($subject_data) ? true : false,
                'a_attr' => array('href' => '/admin/projects/subjects/' . $value['projects_id']),
            );
            
        }

        $response = new JsonResponse($data);
        return $response;
    }

    /**
     * Insert/Update Project
     *
     * Run queries to insert and update projects in the database.
     *
     * @param   array   $data        The data array
     * @param   int     $project_id  The project ID
     * @param   object  $conn        Database connection object
     * @return  int     The project ID
     */
    public function insert_update_project($data, $projects_id = FALSE, $conn, $isni)
    {
        // $this->u->dumper($data);
        // stakeholder_guid
        // stakeholder_label
        // stakeholder_si_guid

        // Query the isni_data table to see if there's an entry.
        $isni_data = $isni->get_isni_data_from_database($data['stakeholder_guid'], $conn);

        // If there is no entry, then perform an insert.
        if(!$isni_data) {
          $isni_inserted = $isni->insert_isni_data($data['stakeholder_guid'], $data['stakeholder_label'], $this->getUser()->getId(), $conn);
        }

        // Update
        if($projects_id) {

            $statement = $conn->prepare("
                UPDATE projects
                SET projects_label = :projects_label
                ,stakeholder_guid = :stakeholder_guid
                ,stakeholder_si_guid = :stakeholder_si_guid
                ,project_description = :project_description
                ,last_modified_user_account_id = :last_modified_user_account_id
                WHERE projects_id = :projects_id
                ");
            $statement->bindValue(":projects_label", $data['projects_label'], PDO::PARAM_STR);
            $statement->bindValue(":stakeholder_guid", $data['stakeholder_guid'], PDO::PARAM_STR);
            $statement->bindValue(":stakeholder_si_guid", $data['stakeholder_si_guid'], PDO::PARAM_STR);
            $statement->bindValue(":project_description", $data['project_description'], PDO::PARAM_STR);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->bindValue(":projects_id", $projects_id, PDO::PARAM_INT);
            $statement->execute();

            return $projects_id;
        }

        // Insert
        if(!$projects_id) {

            $statement = $conn->prepare("INSERT INTO projects
              (projects_label, stakeholder_guid, stakeholder_si_guid, project_description, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:projects_label, :stakeholder_guid, :stakeholder_si_guid, :project_description, NOW(), :user_account_id, :user_account_id )");
            $statement->bindValue(":projects_label", $data['projects_label'], PDO::PARAM_STR);
            $statement->bindValue(":stakeholder_guid", $data['stakeholder_guid'], PDO::PARAM_STR);
            $statement->bindValue(":stakeholder_si_guid", $data['stakeholder_si_guid'], PDO::PARAM_STR);
            $statement->bindValue(":project_description", $data['project_description'], PDO::PARAM_STR);
            $statement->bindValue(":user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `projects` failed.');
            }

            return $last_inserted_id;
        }

    }

    /**
     * Get unit_stakeholder
     * @return  array|bool  The query result
     */
    public function get_units_stakeholders($conn)
    {
      $statement = $conn->prepare("SELECT * FROM unit_stakeholder ORDER BY unit_stakeholder_label ASC");
      $statement->execute();
      return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Delete Multiple Projects
     *
     * Matches /admin/workspace/delete
     *
     * @Route("/admin/workspace/delete", name="projects_remove_records", methods={"GET"})
     * Run a query to delete multiple records.
     *
     * @param   int     $ids      The record ids
     * @param   object  $conn     Database connection object
     * @param   object  $request  Request object
     * @return  void
     */
    public function delete_multiple_projects(Connection $conn, Request $request)
    {
        $ids = $request->query->get('ids');

        if(!empty($ids)) {

          $ids_array = explode(',', $ids);

          foreach ($ids_array as $key => $id) {

            $statement = $conn->prepare("
                UPDATE projects
                LEFT JOIN subjects ON subjects.projects_id = projects.projects_id
                LEFT JOIN items ON items.subjects_id = subjects.subjects_id
                LEFT JOIN datasets ON datasets.items_id = items.items_id
                LEFT JOIN dataset_elements ON dataset_elements.datasets_id = datasets.datasets_id
                SET projects.active = 0,
                    projects.last_modified_user_account_id = :last_modified_user_account_id,
                    subjects.active = 0,
                    subjects.last_modified_user_account_id = :last_modified_user_account_id,
                    items.active = 0,
                    items.last_modified_user_account_id = :last_modified_user_account_id,
                    datasets.active = 0,
                    datasets.last_modified_user_account_id = :last_modified_user_account_id,
                    dataset_elements.active = 0,
                    dataset_elements.last_modified_user_account_id = :last_modified_user_account_id
                WHERE projects.projects_id = :id
            ");
            $statement->bindValue(":id", $id, PDO::PARAM_INT);
            $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->execute();

          }

          $this->addFlash('message', 'Records successfully removed.');

        } else {
          $this->addFlash('message', 'Missing data. No records removed.');
        }

        return $this->redirectToRoute('projects_browse');
    }

    /**
     * Delete Project
     *
     * Run a query to delete a project from the database.
     *
     * @param   int     $project_id  The project ID
     * @param   object  $conn        Database connection object
     * @return  void
     */
    public function delete_project($projects_id, $conn)
    {
        $statement = $conn->prepare("
            DELETE FROM projects
            WHERE projects_id = :projects_id");
        $statement->bindValue(":projects_id", $projects_id, PDO::PARAM_INT);
        $statement->execute();

        // First, delete all subjects.
        $statement = $conn->prepare("
            DELETE FROM subjects
            WHERE projects_id = :projects_id");
        $statement->bindValue(":projects_id", $projects_id, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Create Project Table
     *
     * @param   object $conn  Database connection object
     * @return  void
     */
    public function create_projects_table($conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `projects` (
          `projects_id` int(11) NOT NULL AUTO_INCREMENT,
          `projects_label` varchar(255) NOT NULL DEFAULT '',
          `stakeholder_guid` varchar(255) NOT NULL DEFAULT '',
          `project_description` mediumtext NOT NULL,
          `date_created` datetime NOT NULL,
          `created_by_user_account_id` int(11) NOT NULL,
          `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
          `last_modified_user_account_id` int(11) NOT NULL,
          `active` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`projects_id`),
          KEY `created_by_user_account_id` (`created_by_user_account_id`),
          KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores project metadata'");

        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `projects` failed.');
        } else {
            return TRUE;
        }

    }

}
