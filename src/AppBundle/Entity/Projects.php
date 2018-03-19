<?php
// src/AppBundle/Entity/Project.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Projects
{

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
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

    public function setProject($project)
    {
        $this->project = $project;
    }
}
