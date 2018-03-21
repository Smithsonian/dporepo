<?php
// src/AppBundle/Entity/CaptureDeviceComponent.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDeviceComponent
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_device_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $serial_number;

    /**
     * @var string
     */
    public $capture_device_component_type;

    /**
     * @var string
     */
    public $manufacturer;

    /**
     * @var string
     */
    public $model_name;

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
                capture_device_component.capture_device_component_repository_id AS manage,

                capture_device_component.serial_number,
                capture_device_component.capture_device_component_type,
                capture_device_component.manufacturer,
                capture_device_component.model_name,

                capture_device_component.active,
                capture_device_component.last_modified,
                capture_device_component.capture_device_component_repository_id AS DT_RowId
                FROM capture_device_component
                WHERE capture_device_component.active = 1
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
                UPDATE capture_device_component
                SET
                serial_number = :serial_number
                ,capture_device_component_type = :capture_device_component_type
                ,manufacturer = :manufacturer
                ,model_name = :model_name

                ,last_modified_user_account_id = :user_account_id
                WHERE capture_device_component_repository_id = :capture_device_component_repository_id
                ");

            $statement->bindValue(":serial_number", $data->serial_number, "string");
            $statement->bindValue(":capture_device_component_type", $data->capture_device_component_type, "string");
            $statement->bindValue(":manufacturer", $data->manufacturer, "string");
            $statement->bindValue(":model_name", $data->model_name, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->bindValue(":capture_device_component_repository_id", $id, "integer");
            $statement->execute();

            return $id;
        }

        // Insert
        if(!$id) {

            $statement = $conn->prepare("INSERT INTO capture_device_component
              (parent_capture_device_repository_id, serial_number, capture_device_component_type, manufacturer, model_name, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:parent_capture_device_repository_id, :serial_number, :capture_device_component_type, :manufacturer, :model_name, NOW(), :user_account_id, :user_account_id )");

            $statement->bindValue(":parent_capture_device_repository_id", $data->parent_capture_device_repository_id, "string");
            $statement->bindValue(":serial_number", $data->serial_number, "string");
            $statement->bindValue(":capture_device_component_type", $data->capture_device_component_type, "string");
            $statement->bindValue(":manufacturer", $data->manufacturer, "string");
            $statement->bindValue(":model_name", $data->model_name, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `capture_device_component` failed.');
            }

            return $last_inserted_id;
        }
    }

}