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
    private $parent_capture_dataset_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $scale_bar_id;

    /**
     * @var string
     */
    private $scale_bar_manufacturer;

    /**
     * @var string
     */
    private $scale_bar_barcode_type;

    /**
     * @var string
     */
    private $scale_bar_target_pairs;
    
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
              photogrammetry_scale_bar.photogrammetry_scale_bar_repository_id,
              photogrammetry_scale_bar.parent_capture_dataset_repository_id,
              photogrammetry_scale_bar.scale_bar_id,
              photogrammetry_scale_bar.scale_bar_manufacturer,
              photogrammetry_scale_bar.scale_bar_barcode_type,
              photogrammetry_scale_bar.scale_bar_target_pairs
            FROM photogrammetry_scale_bar
            WHERE photogrammetry_scale_bar.active = 1
            AND photogrammetry_scale_bar.photogrammetry_scale_bar_repository_id = :photogrammetry_scale_bar_repository_id");
        $statement->bindValue(":photogrammetry_scale_bar_repository_id", $id, "integer");
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
            SELECT * FROM photogrammetry_scale_bar
            WHERE photogrammetry_scale_bar.parent_capture_dataset_repository_id = :parent_capture_dataset_repository_id
        ");
        $statement->bindValue(":parent_capture_dataset_repository_id", $id, "integer");
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
        $statement = $conn->prepare("CREATE TABLE IF NOT EXISTS `photogrammetry_scale_bar` (
            `photogrammetry_scale_bar_repository_id` int(11) NOT NULL AUTO_INCREMENT,
            `parent_capture_dataset_repository_id` int(11),
            `scale_bar_id` varchar(255),
            `scale_bar_manufacturer` varchar(255),
            `scale_bar_barcode_type` varchar(255),
            `scale_bar_target_pairs` varchar(255),
            `date_created` datetime NOT NULL,
            `created_by_user_account_id` int(11) NOT NULL,
            `last_modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            `last_modified_user_account_id` int(11) NOT NULL,
            `active` tinyint(1) NOT NULL DEFAULT '1',
            PRIMARY KEY (`photogrammetry_scale_bar_repository_id`),
            KEY `created_by_user_account_id` (`created_by_user_account_id`),
            KEY `last_modified_user_account_id` (`last_modified_user_account_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='This table stores photogrammetry_scale_bar metadata'");
        $statement->execute();
        $error = $conn->errorInfo();

        if ($error[0] !== '00000') {
            var_dump($conn->errorInfo());
            die('CREATE TABLE `photogrammetry_scale_bar` failed.');
        } else {
            return TRUE;
        }

    }
}