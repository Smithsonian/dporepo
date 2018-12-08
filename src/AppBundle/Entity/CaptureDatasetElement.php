<?php
// src/AppBundle/Entity/CaptureDatasetElement.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class CaptureDatasetElement
{

    /**
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $capture_device_configuration_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $capture_device_field_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $capture_sequence_number;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $cluster_position_field_id;

    /**
     * @Assert\NotBlank()
     * @Assert\Type(
     *     type="integer",
     *     message="The value {{ value }} is not a valid {{ type }}."
     * )
     * @Assert\Length(min="1", max="10")
     * @var int
     */
    public $position_in_cluster_field_id;

}