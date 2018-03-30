<?php
// src/AppBundle/Entity/FOSUser.php
namespace AppBundle\Entity;
 
use FOS\UserBundle\Model\User as BaseUser;
use FR3D\LdapBundle\Model\LdapUserInterface;
use Doctrine\ORM\Mapping as ORM;

use Symfony\Component\HttpFoundation\Session\Session;
 
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
            $this->roles = array('ROLE_USER', 'ROLE_ADMIN');
            $session = new Session();
            $session->set('firstLogin', true);
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getUsername()
    {
        return $this->username;
    }

    public function getEmail()
    {
        return $this->email;
    }

    public function getRoles()
    {
        return $this->roles;
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

    /**
     * Favorites
     *
     * @param   obj  $req  The request object
     * @return  
     */
    public function favorites($request, $u, $conn){

        $data = array();
        $req = $request->request->all();

        $statement = $conn->prepare("
            SELECT path FROM favorite
            WHERE fos_user_id = {$this->getId()}
        ");
        $statement->execute();
        $results = $statement->fetchAll();

        foreach ($results as $key => $value) {
            if ($value['path'] === $request->getRequestUri()) {
                return true;
            }
        }

        return false;
    }
}