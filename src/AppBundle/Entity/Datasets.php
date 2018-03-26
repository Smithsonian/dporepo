<?php
// src/AppBundle/Entity/Datasets.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Datasets
{

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $capture_method;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $capture_dataset_type;
    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $capture_dataset_name;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $collected_by;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $date_of_capture;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $capture_dataset_description;

    /**
     * @var string
     */
    public $collection_notes;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $item_position_type;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $positionally_matched_capture_datasets;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $focus_type;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $light_source_type;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $background_removal_method;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $cluster_type;

    /**
     * @var string
     */
    public $cluster_geometry_field_id;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $capture_dataset_guid;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $capture_dataset_field_id;

    /**
     * @var int
     */
    public $support_equipment;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $item_position_field_id;

    /**
     * @Assert\NotBlank()
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