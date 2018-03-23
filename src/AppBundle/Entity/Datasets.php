<?php
// src/AppBundle/Entity/Datasets.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Datasets
{
    
    /**
     * @var string
     */
    public $capture_method;

    /**
     * @var int
     */
    public $capture_dataset_type;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var int
     */
    public $capture_dataset_name;

    /**
     * @var string
     */
    public $collected_by;

    /**
     * @var string
     */
    public $date_of_capture;

    /**
     * @var string
     */
    public $capture_dataset_description;

    /**
     * @var string
     */
    public $collection_notes;

    /**
     * @var string
     */
    public $item_position_type;

    /**
     * @var string
     */
    public $positionally_matched_capture_datasets;

    /**
     * @var int
     */
    public $focus_type;

    /**
     * @var int
     */
    public $light_source_type;

    /**
     * @var string
     */
    public $background_removal_method;

    /**
     * @var int
     */
    public $cluster_type;

    /**
     * @var string
     */
    public $cluster_geometry_field_id;

    /**
     * @var int
     */
    public $capture_dataset_guid;

    /**
     * @var string
     */
    public $capture_dataset_field_id;

    /**
     * @var int
     */
    public $support_equipment;

    /**
     * @var int
     */
    public $item_position_field_id;

    /**
     * @var int
     */
    public $item_arrangement_field_id;

    /**
     * @var int
     */
    public $resource_capture_datasets;

    /**
     * @var int
     */
    public $calibration_object_used;
    
    /**
     * Get Dataset
     *
     * Get one dataset from the database.
     *
     * @param   int     $capture_dataset_repository_id  The data value
     * @param   object  $conn         Database connection object
     * @return  array|bool            The query result
     */
    public function getDataset($capture_dataset_repository_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
              capture_dataset.capture_dataset_guid
              ,capture_dataset.capture_dataset_field_id
              ,capture_dataset.capture_method
              ,capture_dataset.capture_dataset_type
              ,capture_dataset.capture_dataset_name
              ,capture_dataset.collected_by
              ,capture_dataset.date_of_capture
              ,capture_dataset.capture_dataset_description
              ,capture_dataset.collection_notes
              ,capture_dataset.support_equipment
              ,capture_dataset.item_position_type
              ,capture_dataset.item_position_field_id
              ,capture_dataset.item_arrangement_field_id
              ,capture_dataset.positionally_matched_capture_datasets
              ,capture_dataset.focus_type
              ,capture_dataset.light_source_type
              ,capture_dataset.background_removal_method
              ,capture_dataset.cluster_type
              ,capture_dataset.cluster_geometry_field_id
              ,capture_dataset.resource_capture_datasets
              ,capture_dataset.calibration_object_used
              ,capture_dataset.date_created
              ,capture_dataset.created_by_user_account_id
              ,capture_dataset.last_modified
              ,capture_dataset.last_modified_user_account_id
              -- ,capture_method.label AS capture_method
              -- ,dataset_type.label AS capture_dataset_type
              -- ,item_position_type.label_alias AS item_position_type
              -- ,focus_type.label AS focus_type
              -- ,light_source_type.label AS light_source_type
              -- ,background_removal_method.label AS background_removal_method
              -- ,camera_cluster_type.label AS camera_cluster_type
            FROM capture_dataset
            -- LEFT JOIN capture_method ON capture_method.capture_method_repository_id = capture_dataset.capture_method
            -- LEFT JOIN dataset_type ON dataset_type.dataset_type_repository_id = capture_dataset.capture_dataset_type
            -- LEFT JOIN item_position_type ON item_position_type.item_position_type_repository_id = capture_dataset.item_position_type
            -- LEFT JOIN focus_type ON focus_type.focus_type_repository_id = capture_dataset.focus_type
            -- LEFT JOIN light_source_type ON light_source_type.light_source_type_repository_id = capture_dataset.light_source_type
            -- LEFT JOIN background_removal_method ON background_removal_method.background_removal_method_repository_id = capture_dataset.background_removal_method
            -- LEFT JOIN camera_cluster_type ON camera_cluster_type.camera_cluster_type_id = capture_dataset.cluster_type
            WHERE capture_dataset.active = 1
            AND capture_dataset.capture_dataset_repository_id = :capture_dataset_repository_id");
            $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }
}