<?php

namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

class Projects
{
    /**
     * @Assert\NotBlank()
     */
    public $projects_label;

    /**
     * @Assert\NotBlank()
     */
    public $stakeholder_guid;

    /**
     * @Assert\NotBlank()
     */
    public $project_description;
}