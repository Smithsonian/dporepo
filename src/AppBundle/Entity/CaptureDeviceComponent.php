<?php
// src/AppBundle/Entity/CaptureDeviceComponent.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDeviceComponent
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_device_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $serial_number;

    /**
     * @var string
     */
    public $capture_device_component_type;

    /**
     * @var string
     */
    public $manufacturer;

    /**
     * @var string
     */
    public $model_name;

}