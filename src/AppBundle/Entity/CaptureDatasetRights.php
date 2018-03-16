<?php
// src/AppBundle/Entity/CaptureDatasetRights.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDatasetRights
{
    
    /**
     * @var int
     */
    private $parent_capture_dataset_repository_id;

    /**
     * @var string
     */
    private $data_rights_restriction;

    /**
     * @var string
     */
    private $start_date;

    /**
     * @var string
     */
    private $end_date;
    
    /**
     * Get One Record
     *
     * @param   int  $capture_dataset_rights_repository_id  The ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getOne($capture_dataset_rights_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
              capture_dataset_rights.capture_dataset_rights_repository_id,
              capture_dataset_rights.parent_capture_dataset_repository_id,
              capture_dataset_rights.data_rights_restriction,
              capture_dataset_rights.start_date,
              capture_dataset_rights.end_date
            FROM capture_dataset_rights
            WHERE capture_dataset_rights.active = 1
            AND capture_dataset_rights.capture_dataset_rights_repository_id = :capture_dataset_rights_repository_id");
        $statement->bindValue(":capture_dataset_rights_repository_id", $capture_dataset_rights_repository_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }

    /**
    * Get All Records
    *
    * @param   int  $capture_datasets_id  The parent record ID
    * @param   object  $conn  Database connection object
    * @return  array|bool  The query result
    */
    public function getAll($capture_datasets_id, Connection $conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM capture_dataset_rights
            WHERE capture_dataset_rights.capture_datasets_id = :capture_datasets_id
        ");
        $statement->bindValue(":capture_datasets_id", $capture_datasets_id, "integer");
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
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `capture_dataset_rights` (
            `capture_dataset_rights_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_dataset_repository_id` int(11),
            `data_rights_restriction` varchar(255),
            `start_date` datetime,
            `end_date` datetime,
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`capture_dataset_rights_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores capture_dataset_rights metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `capture_dataset_rights` failed.');
        } else {
            return TRUE;
        }

    }
}