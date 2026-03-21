<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order;

use AGTI\PagSeguro\Infrastructure\Api\Remote\BaseService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;

class GetOrderByCharge extends BaseService
{
    public function getApiEndpoint()
    {
        return "/orders";
    }
    
    public function exec($chargeId)
    {
        $this->send("GET", ['charge_id' => $chargeId]);

        $r = $this->getRequest();

        $return = $this->getSerializer()->deserialize($r->getResponse(), GetOrderByChargeResponseSuccess::class, 'json');
        return $return;
    }
}