<?php
// src/AppBundle/Entity/Datasets.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Datasets
{

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $capture_dataset_guid;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $parent_project_repository_id;

    /**
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $parent_item_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $capture_dataset_field_id;

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
     * @Assert\Length(min="1", max="255")
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
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $support_equipment;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $item_position_type;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $item_position_field_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $item_arrangement_field_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $positionally_matched_capture_datasets;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $focus_type;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $light_source_type;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $background_removal_method;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $cluster_type;

    /**
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $cluster_geometry_field_id;

    /**
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $resource_capture_datasets;

    /**
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $calibration_object_used;

}