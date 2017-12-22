<?php
// src/AppBundle/Entity/FOSUser.php
namespace AppBundle\Entity;
 
use FOS\UserBundle\Model\User as BaseUser;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Doctrine\ORM\Mapping as ORM;
 
/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser implements LdapUserInterface{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
 
    /**
     * @ORM\Column(type="string")
     */
    protected $dn;
 
    public function __construct()
    {
        parent::__construct();
        if (empty($this->roles)) {
           $this->roles[] = 'ROLE_USER';
        }
    }
 
    /**
     * {@inheritDoc}
     */
    public function setDn($dn)
    { $this->dn = $dn; }
 
    /**
     * {@inheritDoc}
     */
    public function getDn()
    { return $this->dn; }
}