<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order;

use AGTI\PagSeguro\Infrastructure\Api\Remote\BaseService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;

class  CreateOrder extends BaseService
{

    public function getApiEndpoint()
    {
        return "/orders";
    }
    
    public function exec(Order $order)
    {
        $payload = $this->getSerializer()->normalize($order, null, ['skip_null_values' => true]);
        $this->send("POST", [], $payload);

        $r = $this->getRequest();

        if ($r->getHttpCode() == 201) {
            $return = new CreateOrderResponseSuccess;
            $return->setOrder(
                        $this->getSerializer()->deserialize($r->getResponse(), Order::class, 'json')
                    );
            return $return;
        }
        
        if ($r->getHttpCode() == 400) {
            $return = $this->getSerializer()->deserialize(
                $r->getResponse(),
                CreateOrderResponseError::class,
                'json'
            );
            return $return;
        }

    }
}