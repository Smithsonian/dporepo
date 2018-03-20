<?php
// src/AppBundle/Entity/UvMap.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class UvMap
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    private $parent_capture_dataset_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    private $map_type;

    /**
     * @var string
     */
    private $map_file_type;

    /**
     * @var string
     */
    private $map_size;


    /**
     * Get All Records
     *
     * @param int $id The parent record ID
     * @param Connection $conn
     * @return array|bool
     */
    public function getAll($id, Connection $conn)
    {
        $statement = $conn->prepare("
            SELECT * FROM uv_map
            WHERE uv_map.parent_capture_dataset_repository_id = :parent_capture_dataset_repository_id
        ");
        $statement->bindValue(":parent_capture_dataset_repository_id", $id, "integer");
        $statement->execute();
        return $statement->fetchAll(PDO::FETCH_ASSOC);
    }


}