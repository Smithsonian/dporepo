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

// Projects methods
use AppBundle\Controller\ProjectsController;

class SubjectsController extends Controller
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
     * @Route("/admin/projects/subjects/{projects_id}", name="subjects_browse", methods="GET")
     */
    public function browse_subjects(Connection $conn, Request $request, ProjectsController $projects)
    {
        // Database tables are only created if not present.
        $create_db_table = $this->create_subjects_table($conn);

        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;
        $project_data = $projects->get_project((int)$projects_id, $conn);

        return $this->render('subjects/browse_subjects.html.twig', array(
            'page_title' => $project_data['projects_label'],
            'projects_id' => $projects_id,
            'project_data' => $project_data
        ));
    }

    /**
     * @Route("/admin/projects/datatables_browse_subjects/{projects_id}", name="subjects_browse_datatables", methods="POST")
     *
     * Browse subjects
     *
     * Run a query to retreive all subjects in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_subjects(Connection $conn, Request $request)
    {
        $sort = '';
        $search_sql = '';
        $pdo_params = array();
        $data = array();

        $req = $request->request->all();
        $projects_id = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;

        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;

        switch($req['order'][0]['column']) {
            case '0':
                $sort_field = 'projects_label';
                break;
            case '1':
                $sort_field = 'holding_entity_guid';
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
            $sort = " ORDER BY subjects.last_modified DESC ";
        }

        if ($search) {
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $search_sql = "
                AND (
                  subjects.subject_name LIKE ?
                  OR subjects.location_information LIKE ?
                  OR subjects.last_modified LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
              subjects.subjects_id AS manage
              ,subjects.holding_entity_guid
              ,subjects.subject_holder_subject_id
              ,subjects.location_information
              ,subjects.subject_name
              ,subjects.subject_type_lookup_id
              ,subjects.last_modified
              ,subjects.active
              ,subjects.subjects_id AS DT_RowId
          FROM subjects
          WHERE projects_id = " . (int)$projects_id . "
          {$search_sql}
          {$sort}
          {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Convert status to human readable words.
        if(!empty($data['aaData'])) {
            foreach ($data['aaData'] as $key => $value) {
                switch($value['active']) {
                    case '0':
                        $label = 'warning';
                        $text = 'In Queue';
                        break;
                    case '1':
                        $label = 'primary';
                        $text = 'Processing';
                        break;
                    case '2':
                        $label = 'success';
                        $text = 'Processed';
                        break;
                }
                $data['aaData'][$key]['active'] = '<span class="label label-' . $label . '"><span class="glyphicon glyphicon-ok" aria-hidden="true"></span> ' . $text . '</span>';
            }
        }

        $statement = $conn->prepare("SELECT FOUND_ROWS()");
        $statement->execute();
        $count = $statement->fetch();
        $data["iTotalRecords"] = $count["FOUND_ROWS()"];
        $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];
        
        return $this->json($data);
    }

    /**
     * Matches /admin/projects/subject/*
     *
     * @Route("/admin/projects/subject/{projects_id}/{subjects_id}", name="subjects_manage", methods={"GET","POST"}, requirements={"projects_id"="\d+"}, defaults={"subjects_id" = null})
     *
     * @param   object  Connection    Database connection object
     * @param   object  Request       Request object
     * @return  array|bool            The query result
     */
    function show_subjects_form( Connection $conn, Request $request, GumpParseErrors $gump_parse_errors )
    {
        $errors = false;
        $gump = new GUMP();
        $post = $request->request->all();
        $subjects_id = !empty($request->attributes->get('subjects_id')) ? $request->attributes->get('subjects_id') : false;
        $subject_data = !empty($post) ? $post : $this->get_subject((int)$subjects_id, $conn);
        $subject_data['projects_id'] = !empty($request->attributes->get('projects_id')) ? $request->attributes->get('projects_id') : false;

        // Get data from lookup tables.
        $subject_data['subject_types'] = $this->get_subject_types($conn);
        
        // Validate posted data.
        if(!empty($post)) {
            // "" => "required|numeric",
            // "" => "required|alpha_numeric",
            // "" => "required|date",
            // "" => "numeric|exact_len,5",
            // "" => "required|max_len,255|alpha_numeric",
            $rules = array(
                "subject_name" => "required|max_len,255",
                "subject_description" => "required",
                "subject_holder_subject_id" => "required|max_len,255|alpha_numeric",
                "location_information" => "required|max_len,255",
                // "holding_entity_name" => "required|max_len,255",
                "holding_entity_guid" => "required|max_len,255|alpha_numeric",
                "subject_type_lookup_id" => "required|numeric",
            );
            $validated = $gump->validate($post, $rules);

            $errors = array();
            if ($validated !== true) {
                $errors = $gump_parse_errors->gump_parse_errors($validated);
            }
        }

        if (!$errors && !empty($post)) {
            $subjects_id = $this->insert_update_subject($post, $subject_data['projects_id'], $subjects_id, $conn);
            $this->addFlash('message', 'Subject successfully updated.');
            return $this->redirectToRoute('subjects_browse', array('projects_id' => $subject_data['projects_id']));
        } else {
            return $this->render('subjects/subject_form.html.twig', array(
                "page_title" => !empty($subjects_id) ? 'Manage Subject: ' . $subject_data['subject_name'] : 'Create Subject'
                ,"subject_data" => $subject_data
                ,"errors" => $errors
            ));
        }

    }

    /**
    * Get Subject
    *
    * Run a query to retrieve one subject from the database.
    *
    * @param   int $subject_id  The subject ID
    * @param   object  $conn    Database connection object
    * @return  array|bool       The query result
    */
    public function get_subject($subject_id, $conn)
    {
        $statement = $conn->prepare("SELECT *
            FROM subjects
            WHERE subjects_id = :subjects_id");
        $statement->bindValue(":subjects_id", $subject_id, PDO::PARAM_INT);
        $statement->execute();
        return $statement->fetch(PDO::FETCH_ASSOC);
    }

    /**
    * Get Subjects
    *
    * Run a query to retrieve all subjects from the database.
    *
    * @param   object  $conn    Database connection object
    * @return  array|bool  The query result
    */
    public function get_subjects($conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM subjects
            ORDER BY subjects.subjects_label ASC
        ");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Get subject_types
     * @return  array|bool  The query result
     */
    public function get_subject_types($conn)
    {
        $statement = $conn->prepare("SELECT * FROM subject_types ORDER BY label ASC");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Insert/Update subject
     *
     * Run queries to insert and update subjects in the database.
     *
     * @param   array   $data         The data array
     * @param   int     $projects_id  The project ID
     * @param   int     $subjects_id  The subject ID
     * @param   object  $conn         Database connection object
     * @return  int     The subject ID
     */
    public function insert_update_subject($data, $projects_id = false, $subjects_id = FALSE, $conn)
    {
        // Update
        if($subjects_id) {
          $statement = $conn->prepare("
            UPDATE subjects
            SET subject_holder_subject_id = :subject_holder_subject_id
            ,location_information = :location_information
            ,subject_name = :subject_name
            ,subject_description = :subject_description
            ,holding_entity_guid = :holding_entity_guid
            ,subject_type_lookup_id = :subject_type_lookup_id
            ,last_modified_user_account_id = :last_modified_user_account_id
            WHERE subjects_id = :subjects_id
          ");
          $statement->bindValue(":subject_holder_subject_id", $data['subject_holder_subject_id'], PDO::PARAM_STR);
          $statement->bindValue(":location_information", $data['location_information'], PDO::PARAM_STR);
          $statement->bindValue(":subject_name", $data['subject_name'], PDO::PARAM_STR);
          $statement->bindValue(":subject_description", $data['subject_description'], PDO::PARAM_STR);
          $statement->bindValue(":holding_entity_guid", $data['holding_entity_guid'], PDO::PARAM_STR);
          $statement->bindValue(":subject_type_lookup_id", $data['subject_type_lookup_id'], PDO::PARAM_INT);
          $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->bindValue(":subjects_id", $subjects_id, PDO::PARAM_INT);
          $statement->execute();

          return $subjects_id;
        }

        // Insert
        if(!$subjects_id) {

          $statement = $conn->prepare("INSERT INTO subjects
            (subject_guid, projects_id, subject_holder_subject_id, location_information, subject_name, subject_description, holding_entity_guid, subject_type_lookup_id, 
            date_created, created_by_user_account_id, last_modified_user_account_id )
          VALUES ((select md5(UUID())), :projects_id, :subject_holder_subject_id, :location_information, :subject_name, :subject_description, :holding_entity_guid, :subject_type_lookup_id,
             NOW(), :user_account_id, :user_account_id )");
          $statement->bindValue(":projects_id", $projects_id, PDO::PARAM_INT);
          $statement->bindValue(":subject_holder_subject_id", $data['subject_holder_subject_id'], PDO::PARAM_STR);
          $statement->bindValue(":location_information", $data['location_information'], PDO::PARAM_STR);
          $statement->bindValue(":subject_name", $data['subject_name'], PDO::PARAM_STR);
          $statement->bindValue(":subject_description", $data['subject_description'], PDO::PARAM_STR);
          $statement->bindValue(":holding_entity_guid", $data['holding_entity_guid'], PDO::PARAM_STR);
          $statement->bindValue(":subject_type_lookup_id", $data['subject_type_lookup_id'], PDO::PARAM_STR);
          $statement->bindValue(":user_account_id", $this->getUser()->getId(), PDO::PARAM_INT);
          $statement->execute();
          $last_inserted_id = $conn->lastInsertId();

          if(!$last_inserted_id) {
            die('INSERT INTO `subjects` failed.');
          }

          return $last_inserted_id;

        }

    }

    /**
     * Delete Subject
     *
     * Run a query to delete a subject from the database.
     *
     * @param   int     $subjects_id  The subject ID
     * @param   object  $conn         Database connection object
     * @return  void
     */
    public function delete_subject($subjects_id, $conn)
    {
        $statement = $conn->prepare("
            DELETE FROM subjects
            WHERE subjects_id = :subjects_id");
        $statement->bindValue(":subjects_id", $subjects_id, PDO::PARAM_INT);
        $statement->execute();
    }

    /**
     * Create Subjects Table
     *
     * @param   object $conn  Database connection object
     * @return  void
     */
    public function create_subjects_table($conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `subjects` (
            `subjects_id` int(11) NOT NULL AUTO_INCREMENT,
            `projects_id` int(11) NOT NULL,
            `subject_name` varchar(255) NOT NULL DEFAULT '',
            `subject_guid` int(11) NOT NULL,
            `location_information` varchar(255) NOT NULL DEFAULT '',
            `holding_entity_guid` varchar(255) NOT NULL DEFAULT '',
            `subject_type_lookup_id` int(11) NOT NULL,
            `subject_holder_subject_id` varchar(255) NOT NULL DEFAULT '',        
            `subject_description` text NOT NULL,        
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
          PRIMARY KEY (`subjects_id`),
          KEY `created_by_user_account_id` (`created_by_user_account_id`),
          KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores subject metadata'");

        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `subjects` failed.');
        } else {
            return TRUE;
        }

    }
}