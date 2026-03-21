<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrderByCharge as OrderGetOrderByCharge;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\InfraGetOrderByCharge;
use Doctrine\ORM\EntityManagerInterface;
use Order;
    
class GetOrderByCharge
{
    use ApiApplicationTrait;

    private $infraService;
    private $em;

    public function __construct(OrderGetOrderByCharge $infraService, EntityManagerInterface $em)
    {
        $this->infraService = $infraService;
        $this->em = $em;
    }

    public function exec($chargeId)
    {
        $r = $this->infraService->exec($chargeId);
        $this->postApiRequest($this->infraService->getRequest(), $this->em);

        return $r;
    }
}