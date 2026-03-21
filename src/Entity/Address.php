<?php

namespace AGTI\PagSeguro\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="ps_address")
 * @ORM\Entity()
 */
class Address
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_address", type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="Customer", cascade={"persist"})
     * @ORM\JoinColumn(name="id_customer", referencedColumnName="id_customer")
     */
    private $customer;

    /**
     * ORM\Column(type="boolean")
     */
    private $active;


    /**
     * @ORM\Column(type="string")
     */
    private $address1;
    
    /**
     * @ORM\Column(type="string")
     */
    private $address2;
    
    /**
     * @ORM\Column(type="string")
     */
    private $postcode;
    
    /**
     * @ORM\Column(type="string")
     */
    private $city;
    
    /**
     * @ORM\Column(type="string")
     */
    private $other;
    
    /**
     * @ORM\Column(type="string")
     */
    private $phone;
    
    /**
     * @ORM\Column(type="string")
     */
    private $phoneMobile;
    


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
     * Get the value of customer
     */ 
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set the value of customer
     *
     * @return  self
     */ 
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get oRM\Column(type="boolean")
     */ 
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set oRM\Column(type="boolean")
     *
     * @return  self
     */ 
    public function setActive($active)
    {
        $this->active = $active;

        return $this;
    }

    /**
     * Get the value of address1
     */ 
    public function getAddress1()
    {
        return $this->address1;
    }

    /**
     * Set the value of address1
     *
     * @return  self
     */ 
    public function setAddress1($address1)
    {
        $this->address1 = $address1;

        return $this;
    }

    /**
     * Get the value of address2
     */ 
    public function getAddress2()
    {
        return $this->address2;
    }

    /**
     * Set the value of address2
     *
     * @return  self
     */ 
    public function setAddress2($address2)
    {
        $this->address2 = $address2;

        return $this;
    }

    /**
     * Get the value of postcode
     */ 
    public function getPostcode()
    {
        return $this->postcode;
    }

    /**
     * Set the value of postcode
     *
     * @return  self
     */ 
    public function setPostcode($postcode)
    {
        $this->postcode = $postcode;

        return $this;
    }

    /**
     * Get the value of city
     */ 
    public function getCity()
    {
        return $this->city;
    }

    /**
     * Set the value of city
     *
     * @return  self
     */ 
    public function setCity($city)
    {
        $this->city = $city;

        return $this;
    }

    /**
     * Get the value of other
     */ 
    public function getOther()
    {
        return $this->other;
    }

    /**
     * Set the value of other
     *
     * @return  self
     */ 
    public function setOther($other)
    {
        $this->other = $other;

        return $this;
    }

    /**
     * Get the value of phone
     */ 
    public function getPhone()
    {
        return $this->phone;
    }

    /**
     * Set the value of phone
     *
     * @return  self
     */ 
    public function setPhone($phone)
    {
        $this->phone = $phone;

        return $this;
    }


    /**
     * Get the value of phoneMobile
     */ 
    public function getPhoneMobile()
    {
        return $this->phoneMobile;
    }

    /**
     * Set the value of phoneMobile
     *
     * @return  self
     */ 
    public function setPhoneMobile($phoneMobile)
    {
        $this->phoneMobile = $phoneMobile;

        return $this;
    }
}