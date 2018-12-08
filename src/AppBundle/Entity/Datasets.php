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
    public $project_id;

    /**
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $item_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("integer")
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
     * @Assert\Type("integer")
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $item_position_field_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type("integer")
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
     * @Assert\Type("integer")
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

    /**
     * @var string
     */
    public $directory_path;

    /**
     * @var string
     */
    public $api_publication_picker;

    /**
     * @var string
     */
    public $api_access_uv_map_size_id;
    /**
     * @var array
     */
    public $uv_map_size_options;

    /**
     * @var string
     */
    public $api_access_model_face_count_id;
    /**
     * @var array
     */
    public $model_face_count_options;

    /**
     * @var int
     */
    public $model_purpose_picker;

    /**
     * @var array
     */
    public $model_purpose_options;

}