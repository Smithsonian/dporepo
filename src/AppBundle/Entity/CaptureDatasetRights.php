<?php
// src/AppBundle/Entity/CaptureDatasetRights.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDatasetRights
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_dataset_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $data_rights_restriction;

    /**
     * @var string
     */
    public $start_date;

    /**
     * @var string
     */
    public $end_date;



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
                capture_dataset_rights.capture_dataset_rights_repository_id AS manage,

                capture_dataset_rights.data_rights_restriction,
                capture_dataset_rights.start_date,
                capture_dataset_rights.end_date,

                capture_dataset_rights.active,
                capture_dataset_rights.last_modified,
                capture_dataset_rights.capture_dataset_rights_repository_id AS DT_RowId
                FROM capture_dataset_rights
                WHERE capture_dataset_rights.active = 1
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
                UPDATE capture_dataset_rights
                SET
                data_rights_restriction = :data_rights_restriction
                ,start_date = :start_date
                ,end_date = :end_date

                ,last_modified_user_account_id = :user_account_id
                WHERE capture_dataset_rights_repository_id = :capture_dataset_rights_repository_id
                ");

            $statement->bindValue(":data_rights_restriction", $data->data_rights_restriction, "string");
            $statement->bindValue(":start_date", $data->start_date, "string");
            $statement->bindValue(":end_date", $data->end_date, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->bindValue(":capture_dataset_rights_repository_id", $id, "integer");
            $statement->execute();

            return $id;
        }

        // Insert
        if(!$id) {

            $statement = $conn->prepare("INSERT INTO capture_dataset_rights
              (parent_capture_dataset_repository_id, data_rights_restriction, start_date, end_date, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:parent_capture_dataset_repository_id, :data_rights_restriction, :start_date, :end_date, NOW(), :user_account_id, :user_account_id )");

            $statement->bindValue(":parent_capture_dataset_repository_id", $data->parent_capture_dataset_repository_id, "string");
            $statement->bindValue(":data_rights_restriction", $data->data_rights_restriction, "string");
            $statement->bindValue(":start_date", $data->start_date, "string");
            $statement->bindValue(":end_date", $data->end_date, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `capture_dataset_rights` failed.');
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
            UPDATE capture_dataset_rights
            SET active = 0, last_modified_user_account_id = :last_modified_user_account_id
            WHERE capture_dataset_rights_repository_id = :id
        ");
        $statement->bindValue(":id", $id, "integer");
        $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), "integer");
        $statement->execute();
    }

}