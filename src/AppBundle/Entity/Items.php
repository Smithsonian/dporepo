<?php
// src/AppBundle/Entity/Items.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Items
{

    /**
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
            ,items.items_id
            FROM items
            WHERE items.active = 1
            AND items_id = :items_id");
        $statement->bindValue(":items_id", $item_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }
    
}