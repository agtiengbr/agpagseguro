<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order;

use AGTI\PagSeguro\Infrastructure\Api\Remote\BaseService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;

class CancelChargeService extends BaseService
{
    private $chargeId;
    
    public function getApiEndpoint()
    {
        return "/charges/{$this->chargeId}/cancel";
    }
    
    public function exec($chargeId)
    {
        $this->chargeId = $chargeId;

        $this->send("POST");
        $r = $this->getRequest();
    }
}