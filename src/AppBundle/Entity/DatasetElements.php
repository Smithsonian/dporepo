<?php
// src/AppBundle/Entity/DatasetElements.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class DatasetElements
{

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $capture_device_configuration_id;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $capture_device_field_id;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $capture_sequence_number;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $cluster_position_field_id;

    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $position_in_cluster_field_id;
    
    /**
     * Get Dataset Element
     *
     * Get one dataset element from the database.
     *
     * @param       int $capture_data_elements_id  The dataset element ID
     * @return      array|bool                     The query result
     */
    public function getDatasetElement($capture_data_elements_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT *
            FROM capture_data_elements
            WHERE capture_data_elements.active = 1
            AND capture_data_elements_id = :capture_data_elements_id");
        $statement->bindValue(":capture_data_elements_id", $capture_data_elements_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }
}