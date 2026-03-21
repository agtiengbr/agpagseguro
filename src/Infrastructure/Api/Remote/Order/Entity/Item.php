<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class Item
{
    private $referenceId;
    private $name;
    private $quantity;
    private $unitAmount;

    /**
     * Get the value of referenceId
     */ 
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * Set the value of referenceId
     *
     * @return  self
     */ 
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

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

    /**
     * Get the value of quantity
     */ 
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Set the value of quantity
     *
     * @return  self
     */ 
    public function setQuantity($quantity)
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * Get the value of unitAmount
     */ 
    public function getUnitAmount()
    {
        return $this->unitAmount;
    }

    /**
     * Set the value of unitAmount
     *
     * @return  self
     */ 
    public function setUnitAmount($unitAmount)
    {
        $this->unitAmount = $unitAmount;

        return $this;
    }
}