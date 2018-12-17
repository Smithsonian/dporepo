<?php
// src/AppBundle/Entity/PhotogrammetryScaleBar.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class PhotogrammetryScaleBar
{
    
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
    public $scale_bar_id;

    /**
     * @var string
     */
    public $scale_bar_manufacturer;

    /**
     * @var string
     */
    public $scale_bar_barcode_type;

    /**
     * @var string
     */
    public $scale_bar_target_pairs;

}