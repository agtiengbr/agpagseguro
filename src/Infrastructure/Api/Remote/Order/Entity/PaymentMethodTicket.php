<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class PaymentMethodTicket extends PaymentMethod
{
    private $boleto;

    public function __construct()
    {
        $this->setType('BOLETO');
    }

    /**
     * Get the value of boleto
     */ 
    public function getBoleto()
    {
        return $this->boleto;
    }

    /**
     * Set the value of boleto
     *
     * @return  self
     */ 
    public function setBoleto($boleto)
    {
        $this->boleto = $boleto;

        return $this;
    }
}