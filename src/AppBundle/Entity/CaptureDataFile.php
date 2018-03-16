<?php
// src/AppBundle/Entity/CaptureDataFile.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDataFile
{
    
    /**
     * @var int
     */
    private $parent_capture_data_element_repository_id;

    /**
     * @var string
     */
    private $capture_data_file_name;

    /**
     * @var string
     */
    private $capture_data_file_type;

    /**
     * @var string
     */
    private $is_compressed_multiple_files;
    
    /**
     * Get One Record
     *
     * @param   int  $capture_data_file_repository_id  The ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getOne($capture_data_file_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
              capture_data_file.capture_data_file_repository_id,
              capture_data_file.parent_capture_data_element_repository_id,
              capture_data_file.capture_data_file_name,
              capture_data_file.capture_data_file_type,
              capture_data_file.is_compressed_multiple_files
            FROM capture_data_file
            WHERE capture_data_file.active = 1
            AND capture_data_file.capture_data_file_repository_id = :capture_data_file_repository_id");
        $statement->bindValue(":capture_data_file_repository_id", $capture_data_file_repository_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }

    /**
     * Get All Records
     *
     * @param   int  $capture_data_element_repository_id  The parent record ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getAll($capture_data_element_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM capture_data_file
            WHERE capture_data_file.capture_data_element_repository_id = :capture_data_element_repository_id
        ");
        $statement->bindValue(":capture_data_element_repository_id", $capture_data_element_repository_id, "integer");
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
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `capture_data_file` (
            `capture_data_file_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_data_element_repository_id` int(11),
            `capture_data_file_name` varchar(255),
            `capture_data_file_type` varchar(255),
            `is_compressed_multiple_files` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_data_file_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_data_file metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `capture_data_file` failed.');
        } else {
            return TRUE;
        }

    }
}