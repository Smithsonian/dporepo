<?php
// src/AppBundle/Entity/PhotogrammetryScaleBar.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class PhotogrammetryScaleBar
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
    public $scale_bar_id;

    /**
     * @var string
     */
    public $scale_bar_manufacturer;

    /**
     * @var string
     */
    public $scale_bar_barcode_type;

    /**
     * @var string
     */
    public $scale_bar_target_pairs;

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
                photogrammetry_scale_bar.photogrammetry_scale_bar_repository_id AS manage,

                photogrammetry_scale_bar.scale_bar_id,
                photogrammetry_scale_bar.scale_bar_manufacturer,
                photogrammetry_scale_bar.scale_bar_barcode_type,
                photogrammetry_scale_bar.scale_bar_target_pairs,

                photogrammetry_scale_bar.active,
                photogrammetry_scale_bar.last_modified,
                photogrammetry_scale_bar.photogrammetry_scale_bar_repository_id AS DT_RowId
                FROM photogrammetry_scale_bar
                WHERE photogrammetry_scale_bar.active = 1
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
                UPDATE photogrammetry_scale_bar
                SET
                scale_bar_id = :scale_bar_id
                ,scale_bar_manufacturer = :scale_bar_manufacturer
                ,scale_bar_barcode_type = :scale_bar_barcode_type
                ,scale_bar_target_pairs = :scale_bar_target_pairs

                ,last_modified_user_account_id = :user_account_id
                WHERE photogrammetry_scale_bar_repository_id = :photogrammetry_scale_bar_repository_id
                ");

            $statement->bindValue(":scale_bar_id", $data->scale_bar_id, "string");
            $statement->bindValue(":scale_bar_manufacturer", $data->scale_bar_manufacturer, "string");
            $statement->bindValue(":scale_bar_barcode_type", $data->scale_bar_barcode_type, "string");
            $statement->bindValue(":scale_bar_target_pairs", $data->scale_bar_target_pairs, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->bindValue(":photogrammetry_scale_bar_repository_id", $id, "integer");
            $statement->execute();

            return $id;
        }

        // Insert
        if(!$id) {

            $statement = $conn->prepare("INSERT INTO photogrammetry_scale_bar
              (parent_capture_dataset_repository_id, scale_bar_id, scale_bar_manufacturer, scale_bar_barcode_type, scale_bar_target_pairs, date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:parent_capture_dataset_repository_id, :scale_bar_id, :scale_bar_manufacturer, :scale_bar_barcode_type, :scale_bar_target_pairs, NOW(), :user_account_id, :user_account_id )");

            $statement->bindValue(":parent_capture_dataset_repository_id", $data->parent_capture_dataset_repository_id, "integer");
            $statement->bindValue(":scale_bar_id", $data->scale_bar_id, "string");
            $statement->bindValue(":scale_bar_manufacturer", $data->scale_bar_manufacturer, "string");
            $statement->bindValue(":scale_bar_barcode_type", $data->scale_bar_barcode_type, "string");
            $statement->bindValue(":scale_bar_target_pairs", $data->scale_bar_target_pairs, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->execute();
            $last_inserted_id = $conn->lastInsertId();

            if(!$last_inserted_id) {
              die('INSERT INTO `photogrammetry_scale_bar` failed.');
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
            UPDATE photogrammetry_scale_bar
            SET active = 0, last_modified_user_account_id = :last_modified_user_account_id
            WHERE photogrammetry_scale_bar_repository_id = :id
        ");
        $statement->bindValue(":id", $id, "integer");
        $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), "integer");
        $statement->execute();
    }

}