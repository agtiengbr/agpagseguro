<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;

class GetOrderByChargeResponseSuccess
{
    /** @var Order[] */
    private $orders;


    /**
     * Get the value of orders
     */ 
    public function getOrders()
    {
        return $this->orders;
    }

    /**
     * Set the value of orders
     *
     * @return  self
     */ 
    public function setOrders($orders)
    {
        $this->orders = $orders;

        return $this;
    }
}