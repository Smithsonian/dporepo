<?php
// src/AppBundle/Entity/UvMap.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class UvMap
{
    
    /**
     * @var int
     */
    private $parent_capture_dataset_repository_id;

    /**
     * @var string
     */
    private $map_type;

    /**
     * @var string
     */
    private $map_file_type;

    /**
     * @var string
     */
    private $map_size;
    
    /**
     * Get One Record
     *
     * @param   int  $uv_map_repository_id  The ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getOne($uv_map_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
              uv_map.uv_map_repository_id,
              uv_map.parent_capture_dataset_repository_id,
              uv_map.map_type,
              uv_map.map_file_type,
              uv_map.map_size
            FROM uv_map
            WHERE uv_map.active = 1
            AND uv_map.uv_map_repository_id = :uv_map_repository_id");
        $statement->bindValue(":uv_map_repository_id", $uv_map_repository_id, "integer");
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
            SELECT * FROM uv_map
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
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `uv_map` (
            `uv_map_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_dataset_repository_id` int(11),
            `map_type` varchar(255),
            `map_file_type` varchar(255),
            `map_size` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`uv_map_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores uv_map metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `uv_map` failed.');
        } else {
            return TRUE;
        }

    }
}