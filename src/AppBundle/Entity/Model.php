<?php
// src/AppBundle/Entity/Model.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Model
{
    
    /**
     * @var int
     */
    private $parent_capture_dataset_repository_id;

    /**
     * @var string
     */
    private $model_guid;

    /**
     * @var string
     */
    private $date_of_creation;

    /**
     * @var string
     */
    private $model_file_type;

    /**
     * @var string
     */
    private $derived_from;

    /**
     * @var string
     */
    private $creation_method;

    /**
     * @var string
     */
    private $model_modality;

    /**
     * @var string
     */
    private $units;

    /**
     * @var string
     */
    private $is_watertight;

    /**
     * @var string
     */
    private $model_purpose;

    /**
     * @var string
     */
    private $point_count;

    /**
     * @var string
     */
    private $has_normals;

    /**
     * @var string
     */
    private $face_count;

    /**
     * @var string
     */
    private $vertices_count;

    /**
     * @var string
     */
    private $has_vertex_color;

    /**
     * @var string
     */
    private $has_uv_space;

    /**
     * @var string
     */
    private $model_maps;

    /**
     * Get One Record
     *
     * @param   int  $model_repository_id  The ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getOne($model_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
              model.model_repository_id
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
        $statement->bindValue(":model_repository_id", $model_repository_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }

    /**
     * Get All Records
     *
     * @param   int  $parent_capture_dataset_repository_id  The parent record ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getAll($parent_capture_dataset_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM model
            WHERE uv_map.parent_capture_dataset_repository_id = :parent_capture_dataset_repository_id
        ");
        $statement->bindValue(":parent_capture_dataset_repository_id", $parent_capture_dataset_repository_id, "integer");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Create Database Table
     *
     * @return  void
     */
    public function createTable(Connection $conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `model` (
            `model_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_dataset_repository_id` int(11),
            `model_guid` varchar(255),
            `date_of_creation` datetime,
            `model_file_type` varchar(255),
            `derived_from` varchar(255),
            `creation_method` varchar(255),
            `model_modality` varchar(255),
            `units` varchar(255),
            `is_watertight` varchar(255),
            `model_purpose` varchar(255),
            `point_count` varchar(255),
            `has_normals` varchar(255),
            `face_count` varchar(255),
            `vertices_count` varchar(255),
            `has_vertex_color` varchar(255),
            `has_uv_space` varchar(255),
            `model_maps` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`model_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores model metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `model` failed.');
        } else {
            return TRUE;
        }

    }
}