<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order;

use AGTI\PagSeguro\Infrastructure\Api\Remote\BaseService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;

class  GetOrder extends BaseService
{
    private $order;

    public function getApiEndpoint()
    {
        return "/orders/{$this->order->getId()}";
    }
    
    public function exec(Order $order)
    {
        $this->order = $order;

        $this->send("GET");

        $r = $this->getRequest();

        $return = $this->getSerializer()->deserialize($r->getResponse(), Order::class, 'json');
        return $return;
    }
}