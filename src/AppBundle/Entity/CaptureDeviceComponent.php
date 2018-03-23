<?php
// src/AppBundle/Entity/CaptureDeviceComponent.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDeviceComponent
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_device_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $serial_number;

    /**
     * @var string
     */
    public $capture_device_component_type;

    /**
     * @var string
     */
    public $manufacturer;

    /**
     * @var string
     */
    public $model_name;

    /**
     * Datatables Query
     *
     * @param array $params Parameters
     * @param Connection $conn
     * @return array
     */
    public function datatablesQuery($params = NULL, Connection $conn)
    {
        $data = array();

        if(!empty($params)) {

            $statement = $conn->prepare("SELECT SQL_CALC_FOUND_ROWS
                capture_device_component.capture_device_component_repository_id AS manage,

                capture_device_component.serial_number,
                capture_device_component.capture_device_component_type,
                capture_device_component.manufacturer,
                capture_device_component.model_name,

                capture_device_component.active,
                capture_device_component.last_modified,
                capture_device_component.capture_device_component_repository_id AS DT_RowId
                FROM capture_device_component
                WHERE capture_device_component.active = 1
                {$params['search_sql']}
                {$params['sort']}
                {$params['limit_sql']}");
            $statement->execute($params['pdo_params']);
            $data['aaData'] = $statement->fetchAll();
     
            $statement = $conn->prepare("SELECT FOUND_ROWS()");
            $statement->execute();
            $count = $statement->fetch();
            $data["iTotalRecords"] = $count["FOUND_ROWS()"];
            $data["iTotalDisplayRecords"] = $count["FOUND_ROWS()"];

        }

        return $data;
    }

}