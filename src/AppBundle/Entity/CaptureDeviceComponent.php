<?php
// src/AppBundle/Entity/CaptureDeviceComponent.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDeviceComponent
{
    
    /**
     * @var int
     */
    private $parent_capture_device_repository_id;

    /**
     * @var string
     */
    private $serial_number;

    /**
     * @var string
     */
    private $capture_device_component_type;

    /**
     * @var string
     */
    private $manufacturer;

    /**
     * @var string
     */
    private $model_name;
    
    /**
     * Get One Record
     *
     * @param   int  $capture_device_component_repository_id  The ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getOne($capture_device_component_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
              capture_device_component.capture_device_component_repository_id,
              capture_device_component.parent_capture_device_repository_id,
              capture_device_component.serial_number,
              capture_device_component.capture_device_component_type,
              capture_device_component.manufacturer,
              capture_device_component.model_name
            FROM capture_device_component
            WHERE capture_device_component.active = 1
            AND capture_device_component.capture_device_component_repository_id = :capture_device_component_repository_id");
        $statement->bindValue(":capture_device_component_repository_id", $capture_device_component_repository_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }

    /**
     * Get All Records
     *
     * @param   int  $parent_capture_data_element_repository_id  The parent record ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getAll($parent_capture_data_element_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM capture_device_component
            WHERE capture_device_component.parent_capture_data_element_repository_id = :parent_capture_data_element_repository_id
        ");
        $statement->bindValue(":parent_capture_data_element_repository_id", $parent_capture_data_element_repository_id, "integer");
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
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `capture_device_component` (
            `capture_device_component_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_device_repository_id` int(11),
            `serial_number` varchar(255),
            `capture_device_component_type` varchar(255),
            `manufacturer` varchar(255),
            `model_name` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_device_component_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_device_component metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `capture_device_component` failed.');
        } else {
            return TRUE;
        }

    }
}