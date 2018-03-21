<?php
// src/AppBundle/Entity/ProcessingAction.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class ProcessingAction
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $target_model_repository_id;

    /**
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $preceding_processing_action_repository_id;

    /**
     * @var string
     */
    public $date_of_action;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $action_method;

    /**
     * @var string
     */
    public $software_used;

    /**
     * @var string
     */
    public $action_description;

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
                UPDATE processing_action
                SET
                preceding_processing_action_repository_id = :preceding_processing_action_repository_id
                ,date_of_action = :date_of_action
                ,action_method = :action_method
                ,software_used = :software_used
                ,action_description = :action_description

                ,last_modified_user_account_id = :user_account_id
                WHERE processing_action_repository_id = :processing_action_repository_id
                ");

            $statement->bindValue(":preceding_processing_action_repository_id", $data->preceding_processing_action_repository_id, "integer");
            $statement->bindValue(":date_of_action", $data->date_of_action, "string");
            $statement->bindValue(":action_method", $data->action_method, "string");
            $statement->bindValue(":software_used", $data->software_used, "string");
            $statement->bindValue(":action_description", $data->action_description, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->bindValue(":processing_action_repository_id", $id, "integer");
            $statement->execute();

            return $id;
        }

        // Insert
        if(!$id) {

            $statement = $conn->prepare("INSERT INTO processing_action
              (target_model_repository_id, preceding_processing_action_repository_id, date_of_action, action_method, software_used, action_description, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:target_model_repository_id, :preceding_processing_action_repository_id, :date_of_action, :action_method, :software_used, :action_description,  NOW(), :user_account_id, :user_account_id )");

            $statement->bindValue(":target_model_repository_id", $data->target_model_repository_id, "integer");
            $statement->bindValue(":preceding_processing_action_repository_id", $data->preceding_processing_action_repository_id, "integer");
            $statement->bindValue(":date_of_action", $data->date_of_action, "string");
            $statement->bindValue(":action_method", $data->action_method, "string");
            $statement->bindValue(":software_used", $data->software_used, "string");
            $statement->bindValue(":action_description", $data->action_description, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `processing_action` failed.');
            }

            return $last_inserted_id;
        }
    }

}