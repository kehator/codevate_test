<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
// use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\AdvancedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_users")
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(fields="email", message="Email already taken")
 * @UniqueEntity(fields="username", message="Username already taken")
 */
class User implements AdvancedUserInterface, \Serializable
{
    /**
     * @ORM\Column(type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @Assert\NotBlank()
     * @Assert\Length(max=4096)
     */
    private $plainPassword;


    /**
     * @ORM\Column(type="string", length=255, unique=true)
     * @Assert\NotBlank()
     * @Assert\Email()
     */
    private $email;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\SMS", mappedBy="user")
     * @ORM\OrderBy({"created" = "DESC"})
     */
    private $messages;

    

    public function __construct()
    {
        $this->isActive = true;
        $this->messages = new ArrayCollection();
        // may not be needed, see section on salt below
        // $this->salt = md5(uniqid('', true));
    }


    public function getID()
    {
        return $this->id;
    }


    public function getUsername()
    {
        return $this->username;
    }

    public function setUsername($username)
    {
        $this->username = $username;
    }


    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }


    public function getPassword()
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }


    public function getRoles()
    {
        return array('ROLE_USER');
    }


    public function eraseCredentials()
    {
    }


    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
            // $this->salt,
        ));
    }


    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->password,
            $this->isActive,
            // $this->salt
        ) = unserialize($serialized);
    }
    

    public function getEmail()
    {
        return $this->email;
    }

    public function setEmail($email)
    {
        $this->email = $email;
    }


    public function getPlainPassword()
    {
        return $this->plainPassword;
    }

    public function setPlainPassword($password)
    {
        $this->plainPassword = $password;
    }


    public function isAccountNonExpired()
    {
        return true;
    }


    public function isAccountNonLocked()
    {
        return true;
    }


    public function isCredentialsNonExpired()
    {
        return true;
    }


    public function isEnabled()
    {
        return $this->isActive;
    }

    /**
     * @return Collection|SMS[]
     */
    public function getMessages()
    {
        return $this->messages;
    }
}