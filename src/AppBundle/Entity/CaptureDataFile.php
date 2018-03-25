<?php
// src/AppBundle/Entity/CaptureDataFile.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDataFile
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_data_element_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $capture_data_file_name;

    /**
     * @var string
     */
    public $capture_data_file_type;

    /**
     * @var string
     */
    public $is_compressed_multiple_files;

}