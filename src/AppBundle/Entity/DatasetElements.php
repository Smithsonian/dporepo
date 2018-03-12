<?php
// src/AppBundle/Entity/DatasetElements.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

use Doctrine\DBAL\Driver\Connection;
use PDO;

class DatasetElements
{

    /**
     * @var string
     */
    public $dataset_element_guid;

    /**
     * @Assert\NotBlank()
     * @var string
     */
    public $camera_id;

    /**
     * @var string
     */
    public $camera_capture_position_id;

    /**
     * @var string
     */
    public $cluster_position_id;

    /**
     * @var int
     */
    public $calibration_object_type_id;

    /**
     * @var string
     */
    public $exif_data_placeholder;

    /**
     * @var string
     */
    public $camera_body;

    /**
     * @var string
     */
    public $lens;
    
    /**
     * Get Dataset Element
     *
     * Get one dataset element from the database.
     *
     * @param       int $dataset_elements_id  The dataset element ID
     * @return      array|bool                The query result
     */
    public function getDatasetElement($dataset_elements_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT *
            FROM dataset_elements
            WHERE dataset_elements.active = 1
            AND dataset_elements_id = :dataset_elements_id");
        $statement->bindValue(":dataset_elements_id", $dataset_elements_id, PDO::PARAM_INT);
        $statement->execute();
        $result = $statement->fetch(PDO::FETCH_ASSOC);

        return (object)$result;
    }
}