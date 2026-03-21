<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey;

use AGTI\PagSeguro\Infrastructure\Api\Remote\BaseService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey\Entity\PublicKey;

class CreateCardKey extends BaseService
{
    // Use BaseService constructor (logger handled there)

    public function getApiEndpoint()
    {
        return "/public-keys";
    }
    
    public function exec()
    {
        $this->send(
            "POST",
            [],
            [
                'type' => 'card'
            ]
        );

        if ($this->getRequest()->getHttpCode() == 200) {
            return $this->getSerializer()->deserialize(
                $this->getRequest()->getResponse(),
                PublicKey::class,
                'json'
            );
        }

        // Delegate to BaseService for logging and exception
        $this->handleErrorResponse();
    }
}