<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate\CalculateChargesService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate\CalculateChargesServiceArgs;
use Doctrine\ORM\EntityManagerInterface;

class SimulateFees
{
    use ApiApplicationTrait;

    private $em;
    private $apiService;

    public function __construct(EntityManagerInterface $em, CalculateChargesService $apiService)
    {
        $this->em = $em;
        $this->apiService = $apiService;
    }

    public function exec($cardbin, $value, $installments_no_interest, $max_installments, $seller_fees)
    {
        $args = new CalculateChargesServiceArgs;
        $args->setPaymentMethods("CREDIT_CARD,PIX,BOLETO")
            ->setValue($value)
            ->setMaxInstallments($max_installments)
            ->setMaxInstallmentsNoInterest($installments_no_interest)
            ->setCreditCardBin($cardbin)
            ->setShowSellerFees($seller_fees);
 
        $r = $this->apiService->exec($args);
        
        $this->postApiRequest($this->apiService->getRequest(), $this->em);

        return $r;
    }
}