<?php
// src/AppBundle/Entity/Subjects.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Subjects
{

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $subject_name;

    /**
     * @var string
     */
    public $subject_guid;

    /**
     * @var string
     */
    public $location_information;

    /**
     * @var int
     */
    public $holding_entity_guid;

    /**
     * @var string
     */
    public $subject_type_lookup_id;

    /**
     * @var string
     */
    public $subject_holder_subject_id;

    /**
     * @var string
     */
    public $subject_description;
    
    /**
     * Get Subject
     *
     * Run a query to retrieve one subject from the database.
     *
     * @param   int $subject_id  The subject ID
     * @param   object  $conn    Database connection object
     * @return  array|bool       The query result
     */
    public function getSubject($subject_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT *
            FROM subjects
            WHERE subjects.active = 1
            AND subjects_id = :subjects_id");
        $statement->bindValue(":subjects_id", $subject_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }
}