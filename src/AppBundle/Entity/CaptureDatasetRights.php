<?php
// src/AppBundle/Entity/CaptureDatasetRights.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDatasetRights
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
    public $data_rights_restriction;

    /**
     * @var string
     */
    public $start_date;

    /**
     * @var string
     */
    public $end_date;

}