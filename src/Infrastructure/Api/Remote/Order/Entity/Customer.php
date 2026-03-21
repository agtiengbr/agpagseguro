<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class Customer
{
    private $email;
    private $taxId;
    private $phones;
    private $name;
    
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
     * Get the value of taxId
     */ 
    public function getTaxId()
    {
        return $this->taxId;
    }

    /**
     * Set the value of taxId
     *
     * @return  self
     */ 
    public function setTaxId($taxId)
    {
        $this->taxId = $taxId;

        return $this;
    }

    /**
     * Get the value of phones
     */ 
    public function getPhones()
    {
        return $this->phones;
    }

    /**
     * Set the value of phones
     *
     * @return  self
     */ 
    public function setPhones($phones)
    {
        $this->phones = $phones;

        return $this;
    }


    /**
     * Get the value of name
     */ 
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the value of name
     *
     * @return  self
     */ 
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }
}