<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

abstract class PaymentMethod
{
    private $type;
    private $holder;
        

    /**
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    protected function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get the value of holder
     */ 
    public function getHolder()
    {
        return $this->holder;
    }

    /**
     * Set the value of holder
     *
     * @return  self
     */ 
    public function setHolder(PaymentHolder $holder)
    {
        $this->holder = $holder;

        return $this;
    }
}