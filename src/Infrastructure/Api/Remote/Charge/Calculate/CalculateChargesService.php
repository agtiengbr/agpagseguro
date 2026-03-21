<?php
namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate;

use AGTI\PagSeguro\Infrastructure\Api\Remote\BaseService;

class CalculateChargesService extends BaseService
{

    public function getApiEndpoint()
    {
        return "/charges/fees/calculate";
    }

    public function exec(CalculateChargesServiceArgs $args)
    {
        $r = $this->send(
            "GET",
            $this->getSerializer()->normalize($args)
        );
        
        if ($r->getHttpCode() == 200) {
            return $this->getSerializer()->deserialize($r->getResponse(), CalculateChargesServiceResponseSuccess::class, 'json');
        }

        return $this->getSerializer()->deserialize($r->getResponse(), CalculateChargesServiceResponseError::class, 'json');
    }    
    
}