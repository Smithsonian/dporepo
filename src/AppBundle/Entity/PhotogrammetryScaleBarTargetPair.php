<?php
// src/AppBundle/Entity/PhotogrammetryScaleBarTargetPair.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class PhotogrammetryScaleBarTargetPair
{

    /**
     * @var int
     */
    private $parent_photogrammetry_scale_bar_repository_id;

    /**
     * @var string
     */
    private $target_type;

    /**
     * @var string
     */
    private $target_pair_1_of_2;

    /**
     * @var string
     */
    private $target_pair_2_of_2;

    /**
     * @var string
     */
    private $distance;

    /**
     * @var string
     */
    private $units;
    
    /**
     * Get One Record
     *
     * @param   int  $photogrammetry_scale_bar_target_pair_repository_id  The ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getOne($photogrammetry_scale_bar_target_pair_repository_id, Connection $conn)
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
        $statement->bindValue(":photogrammetry_scale_bar_target_pair_repository_id", $photogrammetry_scale_bar_target_pair_repository_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }

    /**
     * Get All Records
     *
     * @param   int  $parent_photogrammetry_scale_bar_repository_id  The parent record ID
     * @param   object  $conn  Database connection object
     * @return  array|bool  The query result
     */
    public function getAll($parent_photogrammetry_scale_bar_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM photogrammetry_scale_bar_target_pair
            WHERE photogrammetry_scale_bar_target_pair.parent_photogrammetry_scale_bar_repository_id = :parent_photogrammetry_scale_bar_repository_id
        ");
        $statement->bindValue(":parent_photogrammetry_scale_bar_repository_id", $parent_photogrammetry_scale_bar_repository_id, "integer");
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
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `photogrammetry_scale_bar_target_pair` (
            `photogrammetry_scale_bar_target_pair_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_photogrammetry_scale_bar_repository_id` int(11),
            `target_type` varchar(255),
            `target_pair_1_of_2` varchar(255),
            `target_pair_2_of_2` varchar(255),
            `distance` varchar(255),
            `units` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`photogrammetry_scale_bar_target_pair_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores photogrammetry_scale_bar_target_pair metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `photogrammetry_scale_bar_target_pair` failed.');
        } else {
            return TRUE;
        }

    }
}