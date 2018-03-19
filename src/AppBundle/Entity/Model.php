<?php
// src/AppBundle/Entity/Model.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Model
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_dataset_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $model_guid;

    /**
     * @var string
     */
    public $date_of_creation;

    /**
     * @var string
     */
    public $model_file_type;

    /**
     * @var string
     */
    public $derived_from;

    /**
     * @var string
     */
    public $creation_method;

    /**
     * @var string
     */
    public $model_modality;

    /**
     * @var string
     */
    public $units;

    /**
     * @var string
     */
    public $is_watertight;

    /**
     * @var string
     */
    public $model_purpose;

    /**
     * @var string
     */
    public $point_count;

    /**
     * @var string
     */
    public $has_normals;

    /**
     * @var string
     */
    public $face_count;

    /**
     * @var string
     */
    public $vertices_count;

    /**
     * @var string
     */
    public $has_vertex_color;

    /**
     * @var string
     */
    public $has_uv_space;

    /**
     * @var string
     */
    public $model_maps;

    /**
     * Get One Record
     *
     * @param int $id
     * @param Connection $conn
     * @return object|bool
     */
    public function getOne($id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
              model.model_repository_id,
              model.parent_capture_dataset_repository_id,
              model.model_guid,
              model.date_of_creation,
              model.model_file_type,
              model.derived_from,
              model.creation_method,
              model.model_modality,
              model.units,
              model.is_watertight,
              model.model_purpose,
              model.point_count,
              model.has_normals,
              model.face_count,
              model.vertices_count,
              model.has_vertex_color,
              model.has_uv_space,
              model.model_maps
            FROM model
            WHERE model.active = 1
            AND model.model_repository_id = :model_repository_id");
        $statement->bindValue(":model_repository_id", $id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }

    /**
     * Get All Records
     *
     * @param int $id The parent record ID
     * @param Connection $conn
     * @return array|bool
     */
    public function getAll($id, Connection $conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM model
            WHERE uv_map.parent_capture_dataset_repository_id = :parent_capture_dataset_repository_id
        ");
        $statement->bindValue(":parent_capture_dataset_repository_id", $id, "integer");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Datatables Query
     *
     * @param array $params Parameters
     * @param Connection $conn
     * @return array
     */
    public function datatablesQuery($params = NULL, Connection $conn)
    {
        $data = array();

        if(!empty($params)) {

            $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
                model.model_repository_id AS manage,

                model.model_guid,
                model.date_of_creation,
                model.model_file_type,
                model.derived_from,
                model.creation_method,
                model.model_modality,
                model.units,
                model.is_watertight,
                model.model_purpose,
                model.point_count,
                model.has_normals,
                model.face_count,
                model.vertices_count,
                model.has_vertex_color,
                model.has_uv_space,
                model.model_maps,

                model.active,
                model.last_modified,
                model.model_repository_id AS DT_RowId
                FROM model
                WHERE model.active = 1
                {$params['search_sql']}
                {$params['sort']}
                {$params['limit_sql']}");
            $statement->execute($params['pdo_params']);
            $data['aaData'] = $statement->fetchAll();
     
            $statement = $conn->prepare("SELECT FOUND_ROWS()");
            $statement->execute();
            $count = $statement->fetch();
            $data["iTotalRecords"] = $count["FOUND_ROWS()"];
            $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

        }

        return $data;
    }

    /**
     * Insert/Update
     *
     * @param array $data The data array
     * @param int $id The id
     * @param Connection $conn
     * @return int
     */
    public function insertUpdate($data, $id = FALSE, $userId = 0, $conn)
    {
        // var_dump($data); die();

        // Update
        if($id) {

            $statement = $conn->prepare("
                UPDATE model
                SET
                model_guid = :model_guid
                ,date_of_creation = :date_of_creation
                ,model_file_type = :model_file_type
                ,derived_from = :derived_from
                ,creation_method = :creation_method
                ,model_modality = :model_modality
                ,units = :units
                ,is_watertight = :is_watertight
                ,model_purpose = :model_purpose
                ,point_count = :point_count
                ,has_normals = :has_normals
                ,face_count = :face_count
                ,vertices_count = :vertices_count
                ,has_vertex_color = :has_vertex_color
                ,has_uv_space = :has_uv_space
                ,model_maps = :model_maps

                ,last_modified_user_account_id = :user_account_id
                WHERE model_repository_id = :model_repository_id
                ");

            $statement->bindValue(":model_guid", $data->model_guid, "string");
            $statement->bindValue(":date_of_creation", $data->date_of_creation, "string");
            $statement->bindValue(":model_file_type", $data->model_file_type, "string");
            $statement->bindValue(":derived_from", $data->derived_from, "string");
            $statement->bindValue(":creation_method", $data->creation_method, "string");
            $statement->bindValue(":model_modality", $data->model_modality, "string");
            $statement->bindValue(":units", $data->units, "string");
            $statement->bindValue(":is_watertight", $data->is_watertight, "string");
            $statement->bindValue(":model_purpose", $data->model_purpose, "string");
            $statement->bindValue(":point_count", $data->point_count, "string");
            $statement->bindValue(":has_normals", $data->has_normals, "string");
            $statement->bindValue(":face_count", $data->face_count, "string");
            $statement->bindValue(":vertices_count", $data->vertices_count, "string");
            $statement->bindValue(":has_vertex_color", $data->has_vertex_color, "string");
            $statement->bindValue(":has_uv_space", $data->has_uv_space, "string");
            $statement->bindValue(":model_maps", $data->model_maps, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->bindValue(":model_repository_id", $id, "integer");
            $statement->execute();

            return $id;
        }

        // Insert
        if(!$id) {

            $statement = $conn->prepare("INSERT INTO model
              (parent_capture_dataset_repository_id, model_guid, date_of_creation, model_file_type, derived_from, creation_method, model_modality, units, is_watertight, model_purpose, point_count, has_normals, face_count, vertices_count, has_vertex_color, has_uv_space, model_maps, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:parent_capture_dataset_repository_id, :model_guid, :date_of_creation, :model_file_type, :derived_from, :creation_method, :model_modality, :units, :is_watertight, :model_purpose, :point_count, :has_normals, :face_count, :vertices_count, :has_vertex_color, :has_uv_space, :model_maps, NOW(), :user_account_id, :user_account_id )");

            $statement->bindValue(":parent_capture_dataset_repository_id", $data->parent_capture_dataset_repository_id, "integer");
            $statement->bindValue(":model_guid", $data->model_guid, "string");
            $statement->bindValue(":date_of_creation", $data->date_of_creation, "string");
            $statement->bindValue(":model_file_type", $data->model_file_type, "string");
            $statement->bindValue(":derived_from", $data->derived_from, "string");
            $statement->bindValue(":creation_method", $data->creation_method, "string");
            $statement->bindValue(":model_modality", $data->model_modality, "string");
            $statement->bindValue(":units", $data->units, "string");
            $statement->bindValue(":is_watertight", $data->is_watertight, "string");
            $statement->bindValue(":model_purpose", $data->model_purpose, "string");
            $statement->bindValue(":point_count", $data->point_count, "string");
            $statement->bindValue(":has_normals", $data->has_normals, "string");
            $statement->bindValue(":face_count", $data->face_count, "string");
            $statement->bindValue(":vertices_count", $data->vertices_count, "string");
            $statement->bindValue(":has_vertex_color", $data->has_vertex_color, "string");
            $statement->bindValue(":has_uv_space", $data->has_uv_space, "string");
            $statement->bindValue(":model_maps", $data->model_maps, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `model` failed.');
            }

            return $last_inserted_id;
        }
    }

    /**
     * Delete Multiple Records
     *
     * @param int $ids
     * @param Connection $conn
     * @return void
     */
    public function deleteMultiple($id = NULL, Connection $conn)
    {
        $statement = $conn->prepare("
            UPDATE model
            SET active = 0, last_modified_user_account_id = :last_modified_user_account_id
            WHERE model_repository_id = :id
        ");
        $statement->bindValue(":id", $id, "integer");
        $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), "integer");
        $statement->execute();
    }

}