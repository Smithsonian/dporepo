<?php
// src/AppBundle/Entity/CaptureDataFile.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDataFile
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
    public $capture_data_file_name;

    /**
     * @var string
     */
    public $capture_data_file_type;

    /**
     * @var string
     */
    public $is_compressed_multiple_files;


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
                capture_data_file.capture_data_file_repository_id AS manage,

                capture_data_file.capture_data_file_name,
                capture_data_file.capture_data_file_type,
                capture_data_file.is_compressed_multiple_files,

                capture_data_file.active,
                capture_data_file.last_modified,
                capture_data_file.capture_data_file_repository_id AS DT_RowId
                FROM capture_data_file
                WHERE capture_data_file.active = 1
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