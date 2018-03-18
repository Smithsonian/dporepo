<?php
// src/AppBundle/Entity/Project.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Projects
{

    /**
     * @var string
     */
    public $project_name;

    /**
     * @var string
     */
    public $stakeholder_guid;

    /**
     * @var string
     */
    public $project_description;

    /**
     * @var int
     */
    public $stakeholder_guid_picker;

    /**
     * @var string
     */
    public $stakeholder_label;

    // public function getProjectsLabel()
    // {
    //     return $this->project_name;
    // }

    // public function setProjectsLabel($project_name)
    // {
    //     $this->project_name = $project_name;
    // }

    // public function getStakeholderGuid()
    // {
    //     return $this->stakeholder_guid;
    // }

    // public function setStakeholderGuid($stakeholder_guid)
    // {
    //     $this->stakeholder_guid = $stakeholder_guid;
    // }

    // public function getStakeholderGuidPicker()
    // {
    //     return $this->stakeholder_guid_picker;
    // }

    // public function setStakeholderGuidPicker($stakeholder_guid_picker)
    // {
    //     $this->stakeholder_guid_picker = $stakeholder_guid_picker;
    // }

    // public function getProjectDescription()
    // {
    //     return $this->project_description;
    // }

    // public function setProjectDescription($project_description)
    // {
    //     $this->project_description = $project_description;
    // }

    /**
     * Get Project
     *
     * Run a query to retrieve one project from the database.
     *
     * @param   int $project_id  The project ID
     * @return  array|bool       The query result
     */
    public function getProject($project_id, Connection $conn)
    {
        $statement = $conn->prepare("SELECT 
            projects.project_repository_id,
            projects.project_name,
            projects.stakeholder_guid,
            projects.project_description,
            isni_data.isni_label AS stakeholder_label,
            unit_stakeholder.unit_stakeholder_id AS stakeholder_si_guid,
            unit_stakeholder.unit_stakeholder_id AS stakeholder_guid_picker
            FROM projects
            LEFT JOIN isni_data ON isni_data.isni_id = projects.stakeholder_guid
            LEFT JOIN unit_stakeholder ON unit_stakeholder.isni_id = projects.stakeholder_guid
            WHERE projects.active = 1
            AND project_repository_id = :project_repository_id");
        $statement->bindValue(":project_repository_id", $project_id, "integer");
        $statement->execute();
        $result = $statement->fetch();

        return (object)$result;
    }

    public function setProject($project)
    {
        $this->project = $project;
    }
}
