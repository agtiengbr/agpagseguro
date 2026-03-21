<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey;

use AGTI\PagSeguro\Infrastructure\Api\Remote\BaseService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey\Entity\PublicKey;

class GetCardKey extends BaseService
{

    // No constructor needed: use BaseService constructor (logger injected there)

    public function getApiEndpoint()
    {
        return "/public-keys/card";
    }
    
    public function exec()
    {
        $this->send(
            "GET"
        );

        if ($this->getRequest()->getHttpCode() == 200) {
            return $this->getSerializer()->deserialize(
                $this->getRequest()->getResponse(),
                PublicKey::class,
                'json'
            );
        }
        // Delegate error handling to BaseService which will log and throw
        $this->handleErrorResponse();
    }
}