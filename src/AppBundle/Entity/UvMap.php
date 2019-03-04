<?php
// src/AppBundle/Entity/UvMap.php
namespace AppBundle\Entity;
use Symfony\Component\Validator\Constraints as Assert;

class UvMap {

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $capture_dataset_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $map_type;

    /**
     * @var string
     */
    public $map_file_type;

    /**
     * @var string
     */
    public $map_size;

}