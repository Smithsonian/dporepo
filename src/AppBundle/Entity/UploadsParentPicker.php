<?php
// src/AppBundle/Entity/UploadsParentPicker.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class UploadsParentPicker
{

    /**
     * @var string
     */
    public $parent_picker;

}