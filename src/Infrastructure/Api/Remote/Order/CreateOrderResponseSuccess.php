<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;

class  CreateOrderResponseSuccess
{
    /** @var Order */
    private $order;

    /**
     * Get the value of order
     */ 
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set the value of order
     *
     * @return  self
     */ 
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }
}