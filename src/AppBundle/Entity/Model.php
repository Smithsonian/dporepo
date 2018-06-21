<?php
// src/AppBundle/Entity/Model.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Model
{
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_dataset_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $model_guid;

    /**
     * @var string
     */
    public $date_of_creation;

    /**
     * @var string
     */
    public $model_file_type;

    /**
     * @var string
     */
    public $derived_from;

    /**
     * @var string
     */
    public $creation_method;

    /**
     * @var string
     */
    public $model_modality;

    /**
     * @var string
     */
    public $units;

    /**
     * @var string
     */
    public $is_watertight;

    /**
     * @var string
     */
    public $model_purpose;

    /**
     * @var string
     */
    public $point_count;

    /**
     * @var string
     */
    public $has_normals;

    /**
     * @var string
     */
    public $face_count;

    /**
     * @var string
     */
    public $vertices_count;

    /**
     * @var string
     */
    public $has_vertex_color;

    /**
     * @var string
     */
    public $has_uv_space;

    /**
     * @var string
     */
    public $model_maps;

    /**
     * @var string
     */
    public $file_path;

    /**
     * @var string
     */
    public $file_checksum;

}