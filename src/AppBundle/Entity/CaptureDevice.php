<?php
// src/AppBundle/Entity/CaptureDevice.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDevice
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_data_element_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $calibration_file;

    /**
     * @var string
     */
    public $capture_device_component_ids;

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
                capture_device.capture_device_repository_id AS manage,

                capture_device.calibration_file,
                capture_device.capture_device_component_ids,

                capture_device.active,
                capture_device.last_modified,
                capture_device.capture_device_repository_id AS DT_RowId
                FROM capture_device
                WHERE capture_device.active = 1
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