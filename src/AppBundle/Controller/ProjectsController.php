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

class ProjectsController extends Controller
{
    /**
     * @var object  $dumper
     */
    public $dumper;

    /**
    * Constructor
    * @param object   AppUtilities  Utility functions object
    */
    public function __construct(AppUtilities $u)
    {
        $this->u = $u;
    }

    /**
     * @Route("/projects/", name="projects_browse", methods="GET")
     */
    public function browse_projects(Connection $conn, Request $request)
    {
        // Database tables are only created if not present.
        $create_projects_table = $this->create_projects_table($conn);

        return $this->render('projects/browse_projects.html.twig', array(
            'page_title' => "Browse Projects",
        ));
    }

    /**
     * @Route("/projects/datatables_browse_projects", name="projects_browse_datatables", methods="POST")
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
                $sort_field = 'stakeholder_guid';
                break;
            case '2':
                $sort_field = 'subjects_count';
                break;
            case '3':
                $sort_field = 'active';
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
            WHERE (
                projects.projects_label LIKE ?
                OR projects.stakeholder_guid LIKE ?
                OR projects.date_created LIKE ?
                OR projects.last_modified LIKE ?
            ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
                projects.projects_label
                ,projects.stakeholder_guid
                ,projects.date_created
                ,projects.last_modified
                ,projects.active
                ,projects.projects_id AS DT_RowId
                ,count(distinct subjects.subjects_id) AS subjects_count
            FROM projects
            LEFT JOIN subjects ON subjects.projects_id = projects.projects_id
            {$search_sql}
            GROUP BY projects.projects_label, projects.stakeholder_guid, projects.date_created, projects.last_modified, projects.active, projects.projects_id
            {$sort}
            {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

        // // Convert status to human readable words.
        // if(!empty($data['aaData'])) {
        //     foreach ($data['aaData'] as $key => $value) {
        //         switch($value['active']) {
        //             case '0':
        //                 $label = 'warning';
        //                 $text = 'In Queue';
        //                 break;
        //             case '1':
        //                 $label = 'primary';
        //                 $text = 'Processing';
        //                 break;
        //             case '2':
        //                 $label = 'success';
        //                 $text = 'Processed';
        //                 break;
        //         }
        //     }
        //     $data['aaData'][$key]['active'] = '<span class="label label-' . $label . '"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' . $text . '</span>';
        // }

        $statement = $conn->prepare("SELECT FOUND_ROWS()");
        $statement->execute();
        $count = $statement->fetch();
        $data["iTotalRecords"] = $count["FOUND_ROWS()"];
        $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];
        
        return $this->json($data);
    }

    /**
     * Matches /projects/manage/*
     *
     * @Route("/projects/manage/{projects_id}", name="projects_manage", methods={"GET","POST"})
     *
     * @param   int     $projects_id  The project ID
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_projects_form( $projects_id, Connection $conn, Request $request, GumpParseErrors $gump_parse_errors )
    {
        // $this->u->dumper($request->request->all());

        $errors = false;
        $gump = new GUMP();
        $post = $request->request->all();
        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;

        // $subject = new \PHPSkeleton\Subjects($db_resource, $final_global_template_vars["session_key"]);

        $current_project_data = $this->get_project((int)$projects_id, $conn);
        // $project_data = $post ? $post : $this->get_project((int)$projects_id, $conn);
        $project_data = $this->get_project((int)$projects_id, $conn);

        // $subject_data = $subject->get_subjects((int)$projects_id);

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
                "stakeholder_guid" => "required|max_len,255|alpha_numeric",
                "project_description" => "required",
            );
            $validated = $gump->validate($post, $rules);

            $errors = array();
            if ($validated !== true) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
            $projects_id = $this->insert_update_project($post, $projects_id, $conn);
            $this->addFlash('message', 'Project successfully updated.');
            return $this->redirectToRoute('projects_browse');
        } else {
            return $this->render('projects/project_form.html.twig', array(
                "page_title" => !empty($projects_id) ? 'Manage Project: ' . $project_data['projects_label'] : 'Create Project'
                // ,"subject_data" => $subject_data
                ,"current_project_data" => $current_project_data
                ,"project_info" => $project_data
                ,"errors" => $errors
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
        $statement = $conn->prepare("SELECT *
            FROM projects
            WHERE projects_id = :projects_id");
        $statement->bindValue(":projects_id", $project_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
    * Get Projects
    *
    * Run a query to retrieve all projects from the database.
    *
    * @return  array|bool  The query result
    */
    public function get_projects()
    {
        $statement = $this->db->prepare("
            SELECT * FROM projects
            -- LEFT JOIN `subjects` ON `subjects`.projects_id = projects.projects_id
            ORDER BY projects.projects_label ASC
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
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
    public function insert_update_project($data, $projects_id = FALSE, $conn)
    {
        // Update
        if($projects_id) {
            $statement = $conn->prepare("
                UPDATE projects
                SET projects_label = :projects_label
                ,stakeholder_guid = :stakeholder_guid
                ,project_description = :project_description
                ,last_modified_user_account_id = :last_modified_user_account_id
                WHERE projects_id = :projects_id
                ");
            $statement->bindValue(":projects_label", $data['projects_label'], PDO::PARAM_STR);
            $statement->bindValue(":stakeholder_guid", $data['stakeholder_guid'], PDO::PARAM_STR);
            $statement->bindValue(":project_description", $data['project_description'], PDO::PARAM_STR);
            // $statement->bindValue(":last_modified_user_account_id", $_SESSION[$this->session_key]['user_account_id'], PDO::PARAM_INT);
            $statement->bindValue(":last_modified_user_account_id", 2, PDO::PARAM_INT);
            $statement->bindValue(":projects_id", $projects_id, PDO::PARAM_INT);
            $statement->execute();

            return $projects_id;
        }

        // Insert
        if(!$projects_id) {

            $statement = $conn->prepare("INSERT INTO projects
              (projects_label, stakeholder_guid, project_description, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:projects_label, :stakeholder_guid, :project_description, NOW(), :user_account_id, :user_account_id )");
            $statement->bindValue(":projects_label", $data['projects_label'], PDO::PARAM_STR);
            $statement->bindValue(":stakeholder_guid", $data['stakeholder_guid'], PDO::PARAM_STR);
            $statement->bindValue(":project_description", $data['project_description'], PDO::PARAM_STR);
            // $statement->bindValue(":user_account_id", $_SESSION[$this->session_key]['user_account_id'], PDO::PARAM_INT);
            $statement->bindValue(":user_account_id", 2, PDO::PARAM_INT);
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `projects` failed.');
            }

            return $last_inserted_id;
        }

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
        $statement = $this->db->prepare("
            DELETE FROM projects
            WHERE projects_id = :projects_id");
        $statement->bindValue(":projects_id", $projects_id, PDO::PARAM_INT);
        $statement->execute();

        // First, delete all subjects.
        $statement = $this->db->prepare("
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
