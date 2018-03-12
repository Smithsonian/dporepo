<?php
// src/AppBundle/Entity/Datasets.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\DBAL\Driver\Connection;
use PDO;

class Datasets
{
    
    /**
     * @var string
     */
    public $dataset_guid;

    /**
     * @var int
     */
    public $capture_method_lookup_id;

    /**
     * @var int
     */
    public $dataset_type_lookup_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $dataset_name;

    /**
     * @var string
     */
    public $collected_by;

    /**
     * @var string
     */
    public $collected_by_guid;

    /**
     * @var string
     */
    public $date_of_capture;

    /**
     * @var string
     */
    public $dataset_description;

    /**
     * @var string
     */
    public $dataset_collection_notes;

    /**
     * @var int
     */
    public $item_position_type_lookup_id;

    /**
     * @var int
     */
    public $positionally_matched_sets_id;

    /**
     * @var string
     */
    public $motion_control;

    /**
     * @var int
     */
    public $focus_lookup_id;

    /**
     * @var string
     */
    public $light_source;

    /**
     * @var int
     */
    public $light_source_type_lookup_id;

    /**
     * @var string
     */
    public $scale_bars_used;

    /**
     * @var int
     */
    public $background_removal_method_lookup_id;

    /**
     * @var int
     */
    public $camera_cluster_type_lookup_id;

    /**
     * @var int
     */
    public $array_geometry_id;
    
    /**
     * Get Dataset
     *
     * Get one dataset from the database.
     *
     * @param   int     $datasets_id  The data value
     * @param   object  $conn         Database connection object
     * @return  array|bool            The query result
     */
    public function getDataset($datasets_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
                datasets.datasets_id
                ,datasets.dataset_guid
                ,datasets.dataset_type_lookup_id
                ,datasets.dataset_name
                ,datasets.collected_by
                ,datasets.collected_by_guid
                ,datasets.date_of_capture
                ,datasets.dataset_description
                ,datasets.dataset_collection_notes
                ,datasets.positionally_matched_sets_id
                ,datasets.motion_control
                ,datasets.light_source
                ,datasets.scale_bars_used
                ,datasets.array_geometry_id
                ,datasets.date_created
                ,datasets.last_modified
                ,datasets.capture_method_lookup_id
                ,datasets.item_position_type_lookup_id
                ,datasets.focus_lookup_id
                ,datasets.light_source_type_lookup_id
                ,datasets.background_removal_method_lookup_id
                ,datasets.camera_cluster_type_lookup_id
                ,capture_methods.label AS capture_method
                ,dataset_types.label AS dataset_type
                ,item_position_types.label_alias AS item_position_type
                ,focus_types.label AS focus_type
                ,light_source_types.label AS light_source_type
                ,background_removal_methods.label AS background_removal_method
                ,camera_cluster_types.label AS camera_cluster_type
            FROM datasets
            LEFT JOIN capture_methods ON capture_methods.capture_methods_id = datasets.capture_method_lookup_id
            LEFT JOIN dataset_types ON dataset_types.dataset_types_id = datasets.dataset_type_lookup_id
            LEFT JOIN item_position_types ON item_position_types.item_position_types_id = datasets.item_position_type_lookup_id
            LEFT JOIN focus_types ON focus_types.focus_types_id = datasets.focus_lookup_id
            LEFT JOIN light_source_types ON light_source_types.light_source_types_id = datasets.light_source_type_lookup_id
            LEFT JOIN background_removal_methods ON background_removal_methods.background_removal_methods_id = datasets.background_removal_method_lookup_id
            LEFT JOIN camera_cluster_types ON camera_cluster_types.camera_cluster_types_id = datasets.camera_cluster_type_lookup_id
            WHERE datasets.active = 1
            AND datasets.datasets_id = :datasets_id");
        $statement->bindValue(":datasets_id", $datasets_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return (object)$result;
    }
}