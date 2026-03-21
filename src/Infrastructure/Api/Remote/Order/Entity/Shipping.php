<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class Shipping
{
    private $address;

    /**
     * Get the value of address
     */ 
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * Set the value of address
     *
     * @return  self
     */ 
    public function setAddress(Address $address)
    {
        $this->address = $address;

        return $this;
    }
}