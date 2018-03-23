<?php
// src/AppBundle/Entity/PhotogrammetryScaleBar.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class PhotogrammetryScaleBar
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
    public $scale_bar_id;

    /**
     * @var string
     */
    public $scale_bar_manufacturer;

    /**
     * @var string
     */
    public $scale_bar_barcode_type;

    /**
     * @var string
     */
    public $scale_bar_target_pairs;

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
                photogrammetry_scale_bar.photogrammetry_scale_bar_repository_id AS manage,

                photogrammetry_scale_bar.scale_bar_id,
                photogrammetry_scale_bar.scale_bar_manufacturer,
                photogrammetry_scale_bar.scale_bar_barcode_type,
                photogrammetry_scale_bar.scale_bar_target_pairs,

                photogrammetry_scale_bar.active,
                photogrammetry_scale_bar.last_modified,
                photogrammetry_scale_bar.photogrammetry_scale_bar_repository_id AS DT_RowId
                FROM photogrammetry_scale_bar
                WHERE photogrammetry_scale_bar.active = 1
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