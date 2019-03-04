<?php
// src/AppBundle/Entity/UserRole.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class UserRole
{

  /**
   * @var string
   */
  public $username_canonical;

  /**
   * @var int
   */
  public $role_id;

  /**
   * @var int
   */
  public $stakeholder_id;

  /**
   * @var int
   */
  public $project_id;

}
