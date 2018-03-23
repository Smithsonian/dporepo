<?php
// src/AppBundle/Entity/CaptureDatasetRights.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDatasetRights
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_capture_dataset_repository_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $data_rights_restriction;

    /**
     * @var string
     */
    public $start_date;

    /**
     * @var string
     */
    public $end_date;


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
                capture_dataset_rights.capture_dataset_rights_repository_id AS manage,

                capture_dataset_rights.data_rights_restriction,
                capture_dataset_rights.start_date,
                capture_dataset_rights.end_date,

                capture_dataset_rights.active,
                capture_dataset_rights.last_modified,
                capture_dataset_rights.capture_dataset_rights_repository_id AS DT_RowId
                FROM capture_dataset_rights
                WHERE capture_dataset_rights.active = 1
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