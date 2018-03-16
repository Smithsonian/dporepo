<?php
// src/AppBundle/Entity/ProcessingAction.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class ProcessingAction
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
     * @param   int  $processing_action_repository_id  The ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getOne($processing_action_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
              processing_action.processing_action_repository_id,
              processing_action.target_model_repository_id,
              processing_action.preceding_processing_action_repository_id,
              processing_action.date_of_action,
              processing_action.action_method,
              processing_action.software_used,
              processing_action.action_description
            FROM processing_action
            WHERE processing_action.active = 1
            AND processing_action.processing_action_repository_id = :processing_action_repository_id");
        $statement->bindValue(":processing_action_repository_id", $processing_action_repository_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }

    /**
     * Get All Records
     *
     * @param   int  $target_model_repository_id  The parent record ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getAll($target_model_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM processing_action
            WHERE processing_action.target_model_repository_id = :target_model_repository_id
        ");
        $statement->bindValue(":target_model_repository_id", $target_model_repository_id, "integer");
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
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `processing_action` (
            `processing_action_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `target_model_repository_id` int(11),
            `preceding_processing_action_repository_id` int(11),
            `date_of_action` datetime,
            `action_method` varchar(255),
            `software_used` varchar(255),
            `action_description` mediumtext,
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`processing_action_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores processing_action metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `processing_action` failed.');
        } else {
            return TRUE;
        }

    }
}