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
    public $item_name;

    /**
     * @var string
     */
    public $item_guid;

    /**
     * @var string
     */
    public $subject_holder_item_id;

    /**
     * @var string
     */
    public $item_description;

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
            ,items.subject_holder_item_id
            ,items.item_name
            ,items.item_description
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