<?php
// src/AppBundle/Entity/Role.php
namespace AppBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\DBAL\Driver\Connection;

class Role
{

  /**
   * @Assert\NotBlank()
   * @Assert\Length(min="1", max="255")
   * @var string
   */
  public $rolename;

  /**
   * @var string
   */
  public $rolename_canonical;

  /**
   * @var string
   */
  public $role_description;

  public $role_permissions;

  public function setRole($role)
  {
    $this->role = $role;
  }
}
