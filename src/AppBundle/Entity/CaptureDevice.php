<?php
// src/AppBundle/Entity/CaptureDevice.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDevice
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_data_element_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $calibration_file;

    /**
     * @var string
     */
    public $capture_device_component_ids;
    
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
              capture_device.capture_device_repository_id,
              capture_device.parent_capture_data_element_repository_id,
              capture_device.calibration_file,
              capture_device.capture_device_component_ids
            FROM capture_device
            WHERE capture_device.active = 1
            AND capture_device.capture_device_repository_id = :capture_device_repository_id");
        $statement->bindValue(":capture_device_repository_id", $id, "integer");
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
            SELECT * FROM capture_device
            WHERE capture_device.parent_capture_data_element_repository_id = :parent_capture_data_element_repository_id
        ");
        $statement->bindValue(":parent_capture_data_element_repository_id", $id, "integer");
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
                capture_device.capture_device_repository_id AS manage,

                capture_device.calibration_file,
                capture_device.capture_device_component_ids,

                capture_device.active,
                capture_device.last_modified,
                capture_device.capture_device_repository_id AS DT_RowId
                FROM capture_device
                WHERE capture_device.active = 1
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
                UPDATE capture_device
                SET
                calibration_file = :calibration_file
                ,capture_device_component_ids = :capture_device_component_ids

                ,last_modified_user_account_id = :user_account_id
                WHERE capture_device_repository_id = :capture_device_repository_id
                ");

            $statement->bindValue(":calibration_file", $data->calibration_file, "string");
            $statement->bindValue(":capture_device_component_ids", $data->capture_device_component_ids, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->bindValue(":capture_device_repository_id", $id, "integer");
            $statement->execute();

            return $id;
        }

        // Insert
        if(!$id) {

            $statement = $conn->prepare("INSERT INTO capture_device
              (parent_capture_data_element_repository_id, calibration_file, capture_device_component_ids, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:parent_capture_data_element_repository_id, :calibration_file, :capture_device_component_ids, NOW(), :user_account_id, :user_account_id )");

            $statement->bindValue(":parent_capture_data_element_repository_id", $data->parent_capture_data_element_repository_id, "string");
            $statement->bindValue(":calibration_file", $data->calibration_file, "string");
            $statement->bindValue(":capture_device_component_ids", $data->capture_device_component_ids, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `capture_device` failed.');
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
            UPDATE capture_device
            SET active = 0, last_modified_user_account_id = :last_modified_user_account_id
            WHERE capture_device_repository_id = :id
        ");
        $statement->bindValue(":id", $id, "integer");
        $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), "integer");
        $statement->execute();
    }

    /**
     * Create Database Table
     *
     * @return  void
     */
    public function createTable(Connection $conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `capture_device` (
            `capture_device_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_data_element_repository_id` int(11),
            `calibration_file` varchar(255),
            `capture_device_component_ids` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_device_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_device metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `capture_device` failed.');
        } else {
            return TRUE;
        }

    }
}