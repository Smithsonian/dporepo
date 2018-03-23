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

}