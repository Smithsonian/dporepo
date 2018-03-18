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
              capture_datasets.capture_dataset_guid
              ,capture_datasets.capture_dataset_field_id
              ,capture_datasets.capture_method
              ,capture_datasets.capture_dataset_type
              ,capture_datasets.capture_dataset_name
              ,capture_datasets.collected_by
              ,capture_datasets.date_of_capture
              ,capture_datasets.capture_dataset_description
              ,capture_datasets.collection_notes
              ,capture_datasets.support_equipment
              ,capture_datasets.item_position_type
              ,capture_datasets.item_position_field_id
              ,capture_datasets.item_arrangement_field_id
              ,capture_datasets.positionally_matched_capture_datasets
              ,capture_datasets.focus_type
              ,capture_datasets.light_source_type
              ,capture_datasets.background_removal_method
              ,capture_datasets.cluster_type
              ,capture_datasets.cluster_geometry_field_id
              ,capture_datasets.resource_capture_datasets
              ,capture_datasets.calibration_object_used
              ,capture_datasets.date_created
              ,capture_datasets.created_by_user_account_id
              ,capture_datasets.last_modified
              ,capture_datasets.last_modified_user_account_id
              -- ,capture_methods.label AS capture_method
              -- ,dataset_types.label AS capture_dataset_type
              -- ,item_position_types.label_alias AS item_position_type
              -- ,focus_types.label AS focus_type
              -- ,light_source_types.label AS light_source_type
              -- ,background_removal_methods.label AS background_removal_method
              -- ,camera_cluster_types.label AS camera_cluster_type
            FROM capture_datasets
            -- LEFT JOIN capture_methods ON capture_methods.capture_methods_id = capture_datasets.capture_method
            -- LEFT JOIN dataset_types ON dataset_types.dataset_types_id = capture_datasets.capture_dataset_type
            -- LEFT JOIN item_position_types ON item_position_types.item_position_types_id = capture_datasets.item_position_type
            -- LEFT JOIN focus_types ON focus_types.focus_types_id = capture_datasets.focus_type
            -- LEFT JOIN light_source_types ON light_source_types.light_source_types_id = capture_datasets.light_source_type
            -- LEFT JOIN background_removal_methods ON background_removal_methods.background_removal_methods_id = capture_datasets.background_removal_method
            -- LEFT JOIN camera_cluster_types ON camera_cluster_types.camera_cluster_types_id = capture_datasets.cluster_type
            WHERE capture_datasets.active = 1
            AND capture_datasets.capture_dataset_repository_id = :capture_dataset_repository_id");
            $statement->bindValue(":capture_dataset_repository_id", $capture_dataset_repository_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }
}