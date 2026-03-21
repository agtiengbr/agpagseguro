<?php

namespace AGTI\PagSeguro\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Customer
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_customer", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $email;
    
    /**
     * @ORM\Column(type="string")
     */
    private $firstname;
    
    /**
     * @ORM\Column(type="string")
     */
    private $lastname;
    
    /**
     * @ORM\OneToMany(targetEntity="Address", mappedBy="customer")
     * @ORM\JoinColumn(name="id_customer", referencedColumnName="id_customer")
     */
    private $addresses;


    /**
     * Get the value of email
     */ 
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set the value of email
     *
     * @return  self
     */ 
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get the value of firstname
     */ 
    public function getFirstname()
    {
        return $this->firstname;
    }

    /**
     * Set the value of firstname
     *
     * @return  self
     */ 
    public function setFirstname($firstname)
    {
        $this->firstname = $firstname;

        return $this;
    }

    /**
     * Get the value of lastname
     */ 
    public function getLastname()
    {
        return $this->lastname;
    }

    /**
     * Set the value of lastname
     *
     * @return  self
     */ 
    public function setLastname($lastname)
    {
        $this->lastname = $lastname;

        return $this;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of addresses
     */ 
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * Set the value of addresses
     *
     * @return  self
     */ 
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;

        return $this;
    }
}