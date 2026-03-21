<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order as EntityOrder;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrderByCharge;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrderByChargeResponseSuccess;
use Doctrine\ORM\EntityManagerInterface;

class UpdateChargeFromApi
{
    use ApiApplicationTrait;

    private $infraService;
    private $em;

    public function __construct(GetOrderByCharge $infraService, EntityManagerInterface $em)
    {
        $this->infraService = $infraService;
        $this->em = $em;
    }

    public function exec($chargeId)
    {
        $r = $this->infraService->exec($chargeId);
        $this->postApiRequest($this->infraService->getRequest(), $this->em);

        if ($r instanceof GetOrderByChargeResponseSuccess and count($r->getOrders())) {
            //verifica se essa transação já está no banco de dados

            /** @var AgpagseguroTransaction|null */
            $transaction = $this->em->getRepository(AgpagseguroTransaction::class)->findOneBy(['transactionCode' => $chargeId]);
            if (!$transaction) {
                $transaction = new AgpagseguroTransaction;
                $transaction->setTransactionCode($chargeId);

                $this->em->persist($transaction);
            }

            $order = $r->getOrders()[0];
            $transaction->setTransactionStatus($order->getCharges()[0]->getStatus());

            $this->em->flush();

            return $transaction;
        }

        throw new \Exception("A transação não foi localizada junto à API do PagBank.");
        
    }
}