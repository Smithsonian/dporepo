<?php
// src/AppBundle/Entity/Items.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Items
{

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $item_guid;

    /**
     * @var string
     */
    public $local_item_id;

    /**
     * @var string
     */
    public $item_description;

    /**
     * @var string
     */
    public $item_type;

}