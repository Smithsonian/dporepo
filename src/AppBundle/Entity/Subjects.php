<?php
// src/AppBundle/Entity/Subjects.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Subjects
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
     * @var int
     */
    public $local_subject_id;
}