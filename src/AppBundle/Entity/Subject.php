<?php
// src/AppBundle/Entity/Subject.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Subject
{

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $subject_name;

    /**
     * @var string
     */
    public $subject_display_name;

    /**
     * @var string
     */
    public $subject_guid;

    /**
     * @var string
     */
    public $holding_entity_guid;

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


    /**
     * @var int
     */
    public $local_subject_id;
}