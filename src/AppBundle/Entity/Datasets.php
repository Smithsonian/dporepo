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

}