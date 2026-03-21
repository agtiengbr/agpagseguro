<?php
namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey\GetCardKey as PublicKeyGetCardKey;
use Doctrine\ORM\EntityManagerInterface;
use AGTI\PagSeguro\ValueObject\Configuration;

class GetCardKey 
{
    use ApiApplicationTrait;

    private $em;
    private $getService;
    private $config;

    public function __construct(EntityManagerInterface $em, PublicKeyGetCardKey $getService)
    {
        $this->em = $em;
        $this->getService = $getService;
    }

    public function exec()
    {
        $key = $this->getService->exec();
        $this->postApiRequest($this->getService->getRequest(), $this->em);
        return $key;
    }
}