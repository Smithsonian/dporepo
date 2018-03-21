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

}