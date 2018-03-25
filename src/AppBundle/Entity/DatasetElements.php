<?php
// src/AppBundle/Entity/DatasetElements.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class DatasetElements
{

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $capture_device_configuration_id;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $capture_device_field_id;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $capture_sequence_number;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $cluster_position_field_id;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $position_in_cluster_field_id;

}