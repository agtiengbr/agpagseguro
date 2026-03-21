<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class Card
{
    private $encrypted;
    private $store;

    /**
     * Get the value of store
     */ 
    public function getStore()
    {
        return $this->store;
    }

    /**
     * Set the value of store
     *
     * @return  self
     */ 
    public function setStore($store)
    {
        $this->store = $store;

        return $this;
    }

    /**
     * Get the value of encrypted
     */ 
    public function getEncrypted()
    {
        return $this->encrypted;
    }

    /**
     * Set the value of encrypted
     *
     * @return  self
     */ 
    public function setEncrypted($encrypted)
    {
        $this->encrypted = $encrypted;

        return $this;
    }
}