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
    private $parent_capture_data_element_repository_id;

    /**
     * @var string
     */
    private $capture_device_component_ids;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $capture_device_component;
    
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
              capture_device.capture_device_component_ids,
              capture_device.capture_device_component
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
     * Create Database Table
     *
     * @return  void
     */
    public function createTable(Connection $conn)
    {
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `capture_device` (
            `capture_device_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_data_element_repository_id` int(11),
            `capture_device_component_ids` varchar(255),
            `capture_device_component` varchar(255),
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