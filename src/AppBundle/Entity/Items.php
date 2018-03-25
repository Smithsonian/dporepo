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

    /**
     * Constructor
     */
    public function __construct()
    {
        // Establish paths.
        if(!defined('BASE_ROOT')) {
            if($_SERVER['SERVER_SOFTWARE'] === 'Microsoft-IIS/8.5') {
                define('BASE_ROOT', 'C:\\');
            } else {
                define('BASE_ROOT', getcwd() . '/');
            }
        }

        if(!defined('JOBBOX_PATH')) {
            define('JOBBOX_PATH', BASE_ROOT . 'JobBox');
        }

        if(!defined('JOBBOXPROCESS_PATH')) {
            define('JOBBOXPROCESS_PATH', BASE_ROOT . 'JobBoxProcess');
        }
    }

}