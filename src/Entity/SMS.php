<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="app_sms")
 */
class SMS {

    /** 
     * @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") 
     */
    private $id;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank()
     */
    private $user_id;

    /**
     * @ORM\Column(type="string", length=25)
     * @Assert\NotBlank()
     */
    private $number;
    
    /**
     * @ORM\Column(type="string", length=140)
     * @Assert\NotBlank()
     */
    private $message;

    /**
     * @ORM\Column(type="string", length=10)
     * @Assert\NotBlank()
     */
    private $status;


    /**
     * @ORM\Column(type="datetime")
     * @Assert\NotBlank()
     */
    private $created;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="messages")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    public function getID()
    {
        return $this->id;
    }
    
    public function getUser(): User
    {
        return $this->user;
    }

    public function setUser(User $user)
    {
        $this->user = $user;
    }
    

    public function getUserID()
    {
        return $this->user_id;
    }

    public function setUserID($user_id)
    {
        $this->user_id = $user_id;
    }


    public function getNumber()
    {
        return $this->number;
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }


    public function getMessage()
    {
        return $this->message;
    }

    public function setMessage($message)
    {
        $this->message = $message;
    }


    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
    }


    public function getCreated()
    {
        return $this->created;
    }

    public function setCreated($created)
    {
        $this->created = $created;
    }
    

}