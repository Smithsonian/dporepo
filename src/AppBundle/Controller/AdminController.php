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

class AdminController extends Controller
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
     * @Route("/admin/", name="admin_home", methods="GET")
     */
    public function show_admin(Connection $conn, Request $request)
    {
        $data = array();

        return $this->render('admin/admin.html.twig', array(
            'page_title' => 'Dashboard',
            'is_favorite' => $this->getUser()->favorites($request, $this->u, $conn),
        ));
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
    public function datatables_browse_recent_projects(Connection $conn, Request $request)
    {
        $sort = $search_sql = '';
        $pdo_params = $data = array();
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        // Today's date
        $date_today = date('Y-m-d 00:00:00');
        // Date limit
        $date_limit = date('Y-m-d 00:00:00', strtotime('-60 days'));

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
            AND (
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
            WHERE 1 = 1
            AND projects.last_modified < '{$date_today}'
            AND projects.last_modified > '{$date_limit}'
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
    public function datatables_browse_recent_subjects(Connection $conn, Request $request)
    {
        $sort = $search_sql = '';
        $pdo_params = $data = array();
        $req = $request->request->all();
        $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
        $sort_order = $req['order'][0]['dir'];
        $start_record = !empty($req['start']) ? $req['start'] : 0;
        $stop_record = !empty($req['length']) ? $req['length'] : 20;
        // Today's date
        $date_today = date('Y-m-d 00:00:00');
        // Date limit
        $date_limit = date('Y-m-d 00:00:00', strtotime('-60 days'));

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
              ,subjects.projects_id
              ,subjects.holding_entity_guid
              ,subjects.subject_holder_subject_id
              ,subjects.location_information
              ,subjects.subject_name
              ,subjects.subject_type_lookup_id
              ,subjects.last_modified
              ,subjects.active
              ,subjects.subjects_id AS DT_RowId
          FROM subjects
          WHERE 1 = 1
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
                $sort_field = 'page_path';
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
                  OR favorites.page_path LIKE ?
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

}
