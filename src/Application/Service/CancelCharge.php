<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CancelChargeService;
use Doctrine\ORM\EntityManagerInterface;

class CancelCharge
{
    use ApiApplicationTrait;

    private $infraService;
    private $em;
    private $config;

    public function __construct(CancelChargeService $infraService, EntityManagerInterface $em)
    {
        $this->infraService = $infraService;
        $this->em = $em;
    }

    public function exec($chargeId)
    {
        $this->infraService->exec($chargeId);
        $this->postApiRequest($this->infraService->getRequest(), $this->em);
    }
}