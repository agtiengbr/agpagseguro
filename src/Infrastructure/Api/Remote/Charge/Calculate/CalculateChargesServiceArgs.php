<?php
namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate;

use AGTI\PagSeguro\Infrastructure\Api\Remote\BaseService;

class CalculateChargesServiceArgs
{
    private $paymentMethods;
    private $value;
    private $maxInstallments;
    private $maxInstallmentsNoInterest;
    private $creditCardBin;
    private $showSellerFees;


    /**
     * Get the value of paymentMethods
     */ 
    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    /**
     * Set the value of paymentMethods
     *
     * @return  self
     */ 
    public function setPaymentMethods($paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;

        return $this;
    }

    /**
     * Get the value of value
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @return  self
     */ 
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of maxInstallments
     */ 
    public function getMaxInstallments()
    {
        return $this->maxInstallments;
    }

    /**
     * Set the value of maxInstallments
     *
     * @return  self
     */ 
    public function setMaxInstallments($maxInstallments)
    {
        $this->maxInstallments = $maxInstallments;

        return $this;
    }

    /**
     * Get the value of maxInstallmentsNoInterest
     */ 
    public function getMaxInstallmentsNoInterest()
    {
        return $this->maxInstallmentsNoInterest;
    }

    /**
     * Set the value of maxInstallmentsNoInterest
     *
     * @return  self
     */ 
    public function setMaxInstallmentsNoInterest($maxInstallmentsNoInterest)
    {
        $this->maxInstallmentsNoInterest = $maxInstallmentsNoInterest;

        return $this;
    }

    /**
     * Get the value of creditCardBin
     */ 
    public function getCreditCardBin()
    {
        return $this->creditCardBin;
    }

    /**
     * Set the value of creditCardBin
     *
     * @return  self
     */ 
    public function setCreditCardBin($creditCardBin)
    {
        $this->creditCardBin = $creditCardBin;

        return $this;
    }

    /**
     * Get the value of showSellerFees
     */ 
    public function getShowSellerFees()
    {
        return $this->showSellerFees;
    }

    /**
     * Set the value of showSellerFees
     *
     * @return  self
     */ 
    public function setShowSellerFees($showSellerFees)
    {
        $this->showSellerFees = $showSellerFees;

        return $this;
    }
}