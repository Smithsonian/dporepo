<?php
// src/AppBundle/Entity/UvMap.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class UvMap
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    private $parent_capture_dataset_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $map_type;

    /**
     * @var string
     */
    private $map_file_type;

    /**
     * @var string
     */
    private $map_size;


}