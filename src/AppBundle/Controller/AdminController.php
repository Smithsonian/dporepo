<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Session\Session;
use PDO;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

// Subjects methods
use AppBundle\Controller\SubjectsController;
// Items methods
use AppBundle\Controller\ItemsController;

class AdminController extends Controller
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
     * @Route("/admin/", name="admin_home", methods="GET")
     */
    public function show_admin(Connection $conn, Request $request)
    {
        // Database tables are only created if not present.
        $create_favorites_table = $this->create_favorites_table($conn);
        $roles = $this->getUser()->getRoles();

        // Check to see if the firstLogin session variable is set.
        $session = new Session();
        $firstLogin = $session->get('firstLogin');

        // If this is the first login for a user, redirect to the user profile page.
        // Then, remove the firstLogin session variable.
        if($firstLogin) {
            $session->remove('firstLogin');
            return $this->redirectToRoute('fos_user_profile_show');
        } else {
            return $this->render('admin/admin.html.twig', array(
                'page_title' => 'Dashboard',
                'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
            ));
        }
    }

    /**
     * @Route("/admin/datatables_browse_recent_projects/", name="browse_recent_projects", methods="POST")
     *
     * Browse recent projects
     *
     * Run a query to retreive all recent projects.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_recent_projects(Connection $conn, Request $request, SubjectsController $subjects)
    {
        $sort = $search_sql = '';
        $pdo_params = $data = array();
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        // Today's date
        $date_today = date('Y-m-d H:i:s');
        // Date limit
        $date_limit = date('Y-m-d H:i:s', strtotime('-240 days'));
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
                projects.project_name LIKE ?
                OR projects.stakeholder_label LIKE ?
                OR projects.date_created LIKE ?
                OR projects.last_modified LIKE ?
            ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
                projects.project_repository_id as manage
                ,projects.project_repository_id
                ,projects.project_name
                ,projects.stakeholder_guid
                ,projects.date_created
                ,projects.last_modified
                ,projects.active
                ,projects.project_repository_id AS DT_RowId
                ,isni_data.isni_label AS stakeholder_label
            FROM projects
            LEFT JOIN isni_data ON isni_data.isni_id = projects.stakeholder_guid
            LEFT JOIN subjects ON subjects.project_repository_id = projects.project_repository_id
            WHERE projects.active = 1
            AND projects.last_modified < '{$date_today}'
            AND projects.last_modified > '{$date_limit}'
            {$search_sql}
            GROUP BY projects.project_name, projects.stakeholder_guid, projects.date_created, projects.last_modified, projects.active, projects.project_repository_id
            {$sort}
            {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Get the subjects count
        if(!empty($data['aaData'])) {
            foreach ($data['aaData'] as $key => $value) {
                $project_subjects = $subjects->get_subjects($this->container, $value['project_repository_id']);
                $data['aaData'][$key]['subjects_count'] = count($project_subjects);
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
     * @Route("/admin/datatables_browse_recent_subjects/", name="browse_recent_subjects", methods="POST")
     *
     * Browse recent subjects
     *
     * Run a query to retreive recent subjects in the database.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_browse_recent_subjects(Connection $conn, Request $request, ItemsController $items)
    {
        $sort = $search_sql = '';
        $pdo_params = $data = array();
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        // Today's date
        $date_today = date('Y-m-d H:i:s');
        // Date limit
        $date_limit = date('Y-m-d H:i:s', strtotime('-240 days'));
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
                  OR subjects.holding_entity_guid LIKE ?
                  OR subjects.last_modified LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
              subjects.subject_repository_id AS manage
              ,subjects.subject_repository_id
              ,subjects.holding_entity_guid
              ,subjects.local_subject_id
              ,subjects.subject_guid
              ,subjects.subject_name
              ,subjects.last_modified
              ,subjects.active
              ,subjects.subject_repository_id AS DT_RowId
          FROM subjects
          LEFT JOIN items ON items.subject_repository_id = subjects.subject_repository_id
          WHERE subjects.active = 1
          {$search_sql}
          GROUP BY subjects.holding_entity_guid, subjects.local_subject_id, subjects.subject_guid, subjects.subject_name, subjects.last_modified, subjects.active, subjects.subject_repository_id
          {$sort}
          {$limit_sql}");
        $statement->execute($pdo_params);
        $data['aaData'] = $statement->fetchAll(PDO::FETCH_ASSOC);

        // Get the items count
        if(!empty($data['aaData'])) {
            foreach ($data['aaData'] as $key => $value) {
                $subject_items = $items->get_items($this->controller, $value['subject_repository_id']);
                $data['aaData'][$key]['items_count'] = count($subject_items);
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
     * @Route("/admin/get_favorites/", name="get_favorites", methods="POST")
     *
     * Get a user's favorited pages.
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatables_get_favorites(Connection $conn, Request $request)
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
                $sort_field = 'page_title';
                break;
            case '1':
                $sort_field = 'path';
                break;
            case '2':
                $sort_field = 'date_created';
                break;
        }

        $limit_sql = " LIMIT {$start_record}, {$stop_record} ";

        if (!empty($sort_field) && !empty($sort_order)) {
            $sort = " ORDER BY {$sort_field} {$sort_order}";
        } else {
            $sort = " ORDER BY favorites.date_created DESC ";
        }

        if ($search) {
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $pdo_params[] = '%'.$search.'%';
            $search_sql = "
                AND (
                  favorites.page_title LIKE ?
                  OR favorites.path LIKE ?
                  OR favorites.date_created LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS 
            DISTINCT favorites.path,
            favorites.page_title,
            favorites.date_created,
            favorites.id AS DT_RowId
            FROM favorites
            WHERE 1 = 1
            AND favorites.fos_user_id = {$this->getUser()->getId()}
            {$search_sql}
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
     * @Route("/admin/add_favorite/", name="add_favorite", methods="POST")
     *
     * Tag a page as a favorite
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function add_favorite(Connection $conn, Request $request)
    {
        $req = $request->request->all();
        $last_inserted_id = false;

        if(!empty($req) && isset($req['favoritePath'])) {
            $statement = $conn->prepare("INSERT INTO favorites
                (fos_user_id, path, page_title, date_created)
                VALUES (:fos_user_id, :path, :page_title, NOW())");
            $statement->bindValue(":fos_user_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->bindValue(":path", $req['favoritePath'], PDO::PARAM_STR);
            $statement->bindValue(":page_title", $req['pageTitle'], PDO::PARAM_STR);
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();
        }

        if(!$last_inserted_id) {
            die('INSERT INTO `favorites` failed.');
        }

        return $this->json($last_inserted_id);
    }

    /**
     * @Route("/admin/remove_favorite/", name="remove_favorite", methods="POST")
     *
     * Remove a page from favorites
     *
     * @param   object  Connection  Database connection object
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function remove_favorite(Connection $conn, Request $request)
    {
        $req = $request->request->all();

        if(!empty($req) && isset($req['favoritePath'])) {
            $statement = $conn->prepare("
                DELETE FROM favorites
                WHERE favorites.fos_user_id = :fos_user_id
                AND favorites.path = :path");
            $statement->bindValue(":fos_user_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->bindValue(":path", $req['favoritePath'], PDO::PARAM_STR);
            $statement->execute();

            return $this->json(true);
        }

        return $this->json(false);
    }

    /**
     * Create favorites table
     *
     * @param   object $conn  Database connection object
     * @return  void
     */
    public function create_favorites_table($conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `favorites` (
          `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
          `fos_user_id` int(11) NOT NULL,
          `path` text NOT NULL,
          `page_title` varchar(255) NOT NULL DEFAULT '',
          `date_created` datetime DEFAULT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8");

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
