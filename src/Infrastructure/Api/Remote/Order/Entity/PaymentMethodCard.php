<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class PaymentMethodCard extends PaymentMethod
{
    private $card;
    private $capture;
    private $installments;
    
    public function __construct()
    {
        $this->setType("CREDIT_CARD");
    }
    /**
     * Get the value of card
     */ 
    public function getCard()
    {
        return $this->card;
    }

    /**
     * Set the value of card
     *
     * @return  self
     */ 
    public function setCard(Card $card)
    {
        $this->card = $card;

        return $this;
    }

    /**
     * Get the value of installments
     */ 
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * Set the value of installments
     *
     * @return  self
     */ 
    public function setInstallments($installments)
    {
        $this->installments = $installments;

        return $this;
    }

    /**
     * Get the value of capture
     */ 
    public function getCapture()
    {
        return $this->capture;
    }

    /**
     * Set the value of capture
     *
     * @return  self
     */ 
    public function setCapture($capture)
    {
        $this->capture = $capture;

        return $this;
    }
}