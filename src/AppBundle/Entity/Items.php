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

    /**
     * Get Item
     *
     * Run a query to retrieve one subject from the database.
     *
     * @param   int $item_id   The subject ID
     * @param   object  $conn  Database connection object
     * @return  array|bool     The query result
     */
    public function getItem($item_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT
            items.item_guid
            ,items.local_item_id
            ,items.item_description
            ,items.item_type
            ,items.status_types_id
            ,items.last_modified
            ,items.item_repository_id
            ,item_types.label as item_type_label
            FROM items
            LEFT JOIN item_types ON item_types.item_types_id = items.item_type
            WHERE items.active = 1
            AND item_repository_id = :item_repository_id");
        $statement->bindValue(":item_repository_id", $item_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }
    
}