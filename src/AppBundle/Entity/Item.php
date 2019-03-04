<?php
// src/AppBundle/Entity/Item.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Item
{
    
    /**
     * @var int
     */
    public $project_id;

    /**
     * @var string
     */
    public $subject_picker;

    /**
     * @var int
     */
    public $subject_id;

    /**
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $item_guid;

    /**
     * @var string
     */
    public $local_item_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $item_description;

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

    /**
     * @var string
     */
    public $item_type;

}