<?php
// src/AppBundle/Entity/ProcessingAction.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class ProcessingAction
{
    
    /**
     * @Assert\NotBlank()
     * @var int
     */
    public $parent_model_repository_id;

    /**
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $preceding_processing_action_repository_id;

    /**
     * @var string
     */
    public $date_of_action;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(min="1", max="255")
     * @var string
     */
    public $action_method;

    /**
     * @var string
     */
    public $software_used;

    /**
     * @var string
     */
    public $action_description;

}