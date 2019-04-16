<?php

namespace AppBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\DBAL\Driver\Connection;

use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Controller\RepoStorageHybridController;
use PDO;

// Custom utility bundles
use AppBundle\Utils\AppUtilities;

// Subject methods
use AppBundle\Controller\SubjectController;
// Item methods
use AppBundle\Controller\ItemController;

// Access control methods
use AppBundle\Service\RepoUserAccess;

class AdminController extends Controller
{
    /**
     * @var object $u
     */
    public $u;
    private $repo_storage_controller;
    private $repo_user_access_controller;

    /**
    * Constructor
    * @param object  $u  Utility functions object
    */
    public function __construct(AppUtilities $u, Connection $conn)
    {
        // Usage: $this->u->dumper($variable);
        $this->u = $u;
        $this->repo_storage_controller = new RepoStorageHybridController($conn);
        $this->repo_user_access_controller = new RepoUserAccess($conn);
    }

    /**
     * @Route("/admin/", name="admin_home", methods="GET")
     */
    public function showAdmin(Connection $conn, Request $request)
    {
        $username = $this->getUser()->getUsernameCanonical();
        $access = $this->repo_user_access_controller->get_user_access_any($username, 'view_projects');

        if(!array_key_exists('permission_name', $access) || empty($access['permission_name'])) {
          $response = new Response();
          $response->setStatusCode(403);
          return $response;
        }

        // Database tables are only created if not present.
        $create_favorites_table = $this->createFavoritesTable($conn);
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
                'current_tab' => 'admin'
            ));
        }
    }

    /**
     * @Route("/admin/datatables_browse", name="browse_datatables", methods="POST")
     *
     * @param Request $request
     * @return JsonResponse The query result in JSON
     */
    public function datatablesBrowse(Request $request)
    {
      $req = $request->request->all();
      $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
      $sort_field = isset($req['columns']) && isset($req['order'][0]['column']['data']) ? $req['columns'][ $req['order'][0]['column'] ]['data'] : '';
      $sort_order = isset($req['order'][0]['dir']) ? $req['order'][0]['dir'] : 'asc';
      $start_record = !empty($req['start']) ? $req['start'] : 0;
      $stop_record = !empty($req['length']) ? $req['length'] : 20;
      $parent_id = !empty($req['parent_id']) ? $req['parent_id'] : 0;
      $record_type = isset($req['record_type']) ? $req['record_type'] : NULL;
      $parent_id_field = isset($req['parent_type']) ? $req['parent_type'] : NULL;

      $query_params = array(
        'record_type' => $record_type,
        'sort_field' => $sort_field,
        'sort_order' => $sort_order,
        'start_record' => $start_record,
        'stop_record' => $stop_record,
        'parent_id' => $parent_id,
        'parent_id_field' => $parent_id_field,
      );

      if ($search) {
        $query_params['search_value'] = $search;
      }

      $data = $this->repo_storage_controller->execute('getDatatable', $query_params);

      return $this->json($data);
    }

    /**
     * @Route("/admin/datatables_browse_recent_projects/", name="browse_recent_projects", methods={"GET","POST"})
     *
     * Browse recent projects
     *
     * Run a query to retrieve all recent projects.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatablesBrowseRecentProjects(Request $request)
    {
        $data = array();
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

        $query_params = array(
          'record_type' => 'project',
          'sort_field' => $sort_field,
          'sort_order' => $sort_order,
          'start_record' => $start_record,
          'stop_record' => $stop_record,
          'date_range_start' => $date_today,
          'date_range_end' => $date_limit,
        );
        if ($search) {
          $query_params['search_value'] = $search;
        }

        $data = $this->repo_storage_controller->execute('getDatatableProject', $query_params);

      return $this->json($data);
    }

    /**
     * @Route("/admin/datatables_browse_recent_subjects/", name="browse_recent_subjects", methods="POST")
     *
     * Browse recent subjects
     *
     * Run a query to retrieve recent subjects in the database.
     *
     * @param   object  Request     Request object
     * @return  array|bool          The query result
     */
    public function datatablesBrowseRecentSubjects(Request $request, ItemController $items)
    {
        $req = $request->request->all();
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

        $data = $this->repo_storage_controller->execute('getDatatableSubject', $query_params); //@todo or getDatatableSubjectItem ?

        return $this->json($data);
    }
  
  /**
   * @Route("/admin/datatables_browse_assigned_workflows", name="datatables_browse_assigned_workflows", methods="POST")
   *
   * Browse Workflows
   * @param Request $request
   * @return JsonResponse The query result in JSON
   */
  public function datatablesBrowseAssignedWorkflows(Request $request)
  {
    $username = $this->getUser()->getUsernameCanonical();
    //@todo which permission?
    /*$access = $this->repo_user_access->get_user_access_any($username, 'view_projects');

    $project_ids = array(0);
    if(array_key_exists('project_ids', $access) && isset($access['project_ids'])) {
      $project_ids = $access['project_ids'];
    }
    */

    $req = $request->request->all();
    $item_id = !empty($req['item_id']) ? $req['item_id'] : NULL;

    $search = !empty($req['search']['value']) ? $req['search']['value'] : false;
    $sort_field = $req['columns'][ $req['order'][0]['column'] ]['data'];
    $start_record = !empty($req['start']) ? $req['start'] : 0;
    $stop_record = !empty($req['length']) ? $req['length'] : 20;
  $logged_user = $this->getUser()->getId();

    $query_params = array(
      'sort_field' => $sort_field,
      'sort_order' => $sort_order,
      'start_record' => $start_record,
      'stop_record' => $stop_record,
      'item_id' => $item_id,
    'logged_user'=>$logged_user,
    );
  
    if ($search) {
      $query_params['search_value'] = $search;
    }
    //$query_params['project_ids'] = $project_ids;

    // Look in workflow table for workflows belonging to an item_id.
    $data = $this->repo_storage_controller->execute('getAssignedDatatableWorkflows', $query_params);

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
    public function datatablesGetFavorites(Connection $conn, Request $request)
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
                  page_title LIKE ?
                  OR path LIKE ?
                  OR date_created LIKE ?
                ) ";
        }

        $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS 
            DISTINCT path,
            page_title,
            date_created,
            id AS DT_RowId
            FROM favorite
            WHERE 1 = 1
            AND fos_user_id = {$this->getUser()->getId()}
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
    public function addFavorite(Connection $conn, Request $request)
    {
        $req = $request->request->all();
        $last_inserted_id = false;

        if(!empty($req) && isset($req['favoritePath'])) {
            $statement = $conn->prepare("INSERT INTO favorite
                (fos_user_id, path, page_title, date_created)
                VALUES (:fos_user_id, :path, :page_title, NOW())");
            $statement->bindValue(":fos_user_id", $this->getUser()->getId(), PDO::PARAM_INT);
            $statement->bindValue(":path", $req['favoritePath'], PDO::PARAM_STR);
            $statement->bindValue(":page_title", $req['pageTitle'], PDO::PARAM_STR);
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();
        }

        if(!$last_inserted_id) {
            die('INSERT INTO `favorite` failed.');
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
    public function removeFavorite(Connection $conn, Request $request)
    {
        $req = $request->request->all();

        if(!empty($req) && isset($req['favoritePath'])) {
            $statement = $conn->prepare("
                DELETE FROM favorite
                WHERE fos_user_id = :fos_user_id
                AND path = :path");
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
    public function createFavoritesTable($conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `favorite` (
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
            die('CREATE TABLE `project` failed.');
        } else {
            return TRUE;
        }

    }

}
