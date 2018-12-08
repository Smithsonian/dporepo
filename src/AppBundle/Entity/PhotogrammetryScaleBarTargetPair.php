<?php
// src/AppBundle/Entity/PhotogrammetryScaleBarTargetPair.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class PhotogrammetryScaleBarTargetPair
{

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $photogrammetry_scale_bar_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $target_type;

    /**
     * @var string
     */
    public $target_pair_1_of_2;

    /**
     * @var string
     */
    public $target_pair_2_of_2;

    /**
     * @var string
     */
    public $distance;

    /**
     * @var string
     */
    public $units;

}