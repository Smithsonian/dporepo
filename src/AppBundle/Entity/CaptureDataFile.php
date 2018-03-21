<?php
// src/AppBundle/Entity/CaptureDataFile.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDataFile
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
    public $capture_data_file_name;

    /**
     * @var string
     */
    public $capture_data_file_type;

    /**
     * @var string
     */
    public $is_compressed_multiple_files;


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
                capture_data_file.capture_data_file_repository_id AS manage,

                capture_data_file.capture_data_file_name,
                capture_data_file.capture_data_file_type,
                capture_data_file.is_compressed_multiple_files,

                capture_data_file.active,
                capture_data_file.last_modified,
                capture_data_file.capture_data_file_repository_id AS DT_RowId
                FROM capture_data_file
                WHERE capture_data_file.active = 1
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
                UPDATE capture_data_file
                SET
                capture_data_file_name = :capture_data_file_name
                ,capture_data_file_type = :capture_data_file_type
                ,is_compressed_multiple_files = :is_compressed_multiple_files

                ,last_modified_user_account_id = :user_account_id
                WHERE capture_data_file_repository_id = :capture_data_file_repository_id
                ");

            $statement->bindValue(":capture_data_file_name", $data->capture_data_file_name, "string");
            $statement->bindValue(":capture_data_file_type", $data->capture_data_file_type, "string");
            $statement->bindValue(":is_compressed_multiple_files", $data->is_compressed_multiple_files, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->bindValue(":capture_data_file_repository_id", $id, "integer");
            $statement->execute();

            return $id;
        }

        // Insert
        if(!$id) {

            $statement = $conn->prepare("INSERT INTO capture_data_file
              (parent_capture_data_element_repository_id, capture_data_file_name, capture_data_file_type, is_compressed_multiple_files, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:parent_capture_data_element_repository_id, :capture_data_file_name, :capture_data_file_type, :is_compressed_multiple_files, NOW(), :user_account_id, :user_account_id )");

            $statement->bindValue(":parent_capture_data_element_repository_id", $data->parent_capture_data_element_repository_id, "string");
            $statement->bindValue(":capture_data_file_name", $data->capture_data_file_name, "string");
            $statement->bindValue(":capture_data_file_type", $data->capture_data_file_type, "string");
            $statement->bindValue(":is_compressed_multiple_files", $data->is_compressed_multiple_files, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `capture_data_file` failed.');
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
            UPDATE capture_data_file
            SET active = 0, last_modified_user_account_id = :last_modified_user_account_id
            WHERE capture_data_file_repository_id = :id
        ");
        $statement->bindValue(":id", $id, "integer");
        $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), "integer");
        $statement->execute();
    }

}