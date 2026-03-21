<?php

use AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate\CalculateChargesService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate\CalculateChargesServiceArgs;
use AGTI\PagSeguro\Infrastructure\Serializer\Serializer;

class agpagsegurosimulateInstallmentsModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();

        try {
            $s = $this->get(CalculateChargesService::class);
            $serializer = $this->get('agti.pagseguro.infrastructure.serializer.serializer');

            $args = new CalculateChargesServiceArgs;
            $args->setPaymentMethods("CREDIT_CARD,PIX,BOLETO")
                ->setValue(Tools::getValue('value'))
                ->setMaxInstallments(Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_MAX'))
                ->setMaxInstallmentsNoInterest(Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_NO_INTEREST'))
                ->setCreditCardBin(Tools::getValue('cardbin'));

            $r = $s->exec($args);
            
            echo $serializer->serialize($r, 'json');
        } catch (\Exception $e) {
            http_response_code(403);
            echo $serializer->serialize($e, 'json');
        }

        exit();
    }
}
