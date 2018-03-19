<?php
// src/AppBundle/Entity/PhotogrammetryScaleBarTargetPair.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class PhotogrammetryScaleBarTargetPair
{

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_photogrammetry_scale_bar_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $target_type;

    /**
     * @var string
     */
    public $target_pair_1_of_2;

    /**
     * @var string
     */
    public $target_pair_2_of_2;

    /**
     * @var string
     */
    public $distance;

    /**
     * @var string
     */
    public $units;
    
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
              photogrammetry_scale_bar_target_pair.photogrammetry_scale_bar_target_pair_repository_id,
              photogrammetry_scale_bar_target_pair.parent_photogrammetry_scale_bar_repository_id,
              photogrammetry_scale_bar_target_pair.target_type,
              photogrammetry_scale_bar_target_pair.target_pair_1_of_2,
              photogrammetry_scale_bar_target_pair.target_pair_2_of_2,
              photogrammetry_scale_bar_target_pair.distance,
              photogrammetry_scale_bar_target_pair.units
            FROM photogrammetry_scale_bar_target_pair
            WHERE photogrammetry_scale_bar_target_pair.active = 1
            AND photogrammetry_scale_bar_target_pair.photogrammetry_scale_bar_target_pair_repository_id = :photogrammetry_scale_bar_target_pair_repository_id");
        $statement->bindValue(":photogrammetry_scale_bar_target_pair_repository_id", $id, "integer");
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
            SELECT * FROM photogrammetry_scale_bar_target_pair
            WHERE photogrammetry_scale_bar_target_pair.parent_photogrammetry_scale_bar_repository_id = :parent_photogrammetry_scale_bar_repository_id
        ");
        $statement->bindValue(":parent_photogrammetry_scale_bar_repository_id", $id, "integer");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }

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
                photogrammetry_scale_bar_target_pair.photogrammetry_scale_bar_target_pair_repository_id AS manage,

                photogrammetry_scale_bar_target_pair.target_type,
                photogrammetry_scale_bar_target_pair.target_pair_1_of_2,
                photogrammetry_scale_bar_target_pair.target_pair_2_of_2,
                photogrammetry_scale_bar_target_pair.distance,
                photogrammetry_scale_bar_target_pair.units,

                photogrammetry_scale_bar_target_pair.active,
                photogrammetry_scale_bar_target_pair.last_modified,
                photogrammetry_scale_bar_target_pair.photogrammetry_scale_bar_target_pair_repository_id AS DT_RowId
                FROM photogrammetry_scale_bar_target_pair
                WHERE photogrammetry_scale_bar_target_pair.active = 1
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
                UPDATE photogrammetry_scale_bar_target_pair
                SET
                target_type = :target_type
                ,target_pair_1_of_2 = :target_pair_1_of_2
                ,target_pair_2_of_2 = :target_pair_2_of_2
                ,distance = :distance
                ,units = :units

                ,last_modified_user_account_id = :user_account_id
                WHERE photogrammetry_scale_bar_target_pair_repository_id = :photogrammetry_scale_bar_target_pair_repository_id
                ");

            $statement->bindValue(":target_type", $data->target_type, "string");
            $statement->bindValue(":target_pair_1_of_2", $data->target_pair_1_of_2, "string");
            $statement->bindValue(":target_pair_2_of_2", $data->target_pair_2_of_2, "string");
            $statement->bindValue(":distance", $data->distance, "string");
            $statement->bindValue(":units", $data->units, "string");

            $statement->bindValue(":user_account_id", $userId, "integer");
            $statement->bindValue(":photogrammetry_scale_bar_target_pair_repository_id", $id, "integer");
            $statement->execute();

            return $id;
        }

        // Insert
        if(!$id) {

            $statement = $conn->prepare("INSERT INTO photogrammetry_scale_bar_target_pair
              (parent_photogrammetry_scale_bar_repository_id, target_type, target_pair_1_of_2, target_pair_2_of_2, distance, units,  date_created, created_by_user_account_id, last_modified_user_account_id )
              VALUES (:parent_photogrammetry_scale_bar_repository_id, :target_type, :target_pair_1_of_2, :target_pair_2_of_2, :distance, :units, NOW(), :user_account_id, :user_account_id )");

            $statement->bindValue(":parent_photogrammetry_scale_bar_repository_id", $data->parent_photogrammetry_scale_bar_repository_id, "integer");
            $statement->bindValue(":target_type", $data->target_type, "string");
            $statement->bindValue(":target_pair_1_of_2", $data->target_pair_1_of_2, "string");
            $statement->bindValue(":target_pair_2_of_2", $data->target_pair_2_of_2, "string");
            $statement->bindValue(":distance", $data->distance, "string");
            $statement->bindValue(":units", $data->units, "string");

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
            UPDATE photogrammetry_scale_bar_target_pair
            SET active = 0, last_modified_user_account_id = :last_modified_user_account_id
            WHERE photogrammetry_scale_bar_target_pair_repository_id = :id
        ");
        $statement->bindValue(":id", $id, "integer");
        $statement->bindValue(":last_modified_user_account_id", $this->getUser()->getId(), "integer");
        $statement->execute();
    }

}