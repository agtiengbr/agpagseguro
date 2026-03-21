<?php

namespace AGTI\PagSeguro\ValueObject;

class SimulationPaymentMethods
{
    /**
     * @var InstallmentPlan
     */
    private $pix;

    /**
     * @var InstallmentPlan
     */
    private $boleto;

    /**
     * @var InstallmentPlanCardBanner
     */
    private $creditCard;

    /**
     * Get the value of creditCard
     *
     * @return  InstallmentPlanCardBanner
     */ 
    public function getCreditCard()
    {
        return $this->creditCard;
    }

    /**
     * Set the value of creditCard
     *
     * @param  InstallmentPlanCardBanner  $creditCard
     *
     * @return  self
     */ 
    public function setCreditCard(InstallmentPlanCardBanner $creditCard)
    {
        $this->creditCard = $creditCard;

        return $this;
    }

    /**
     * Get the value of boleto
     *
     * @return  InstallmentPlan
     */ 
    public function getBoleto()
    {
        return $this->boleto;
    }

    /**
     * Set the value of boleto
     *
     * @param  InstallmentPlan  $boleto
     *
     * @return  self
     */ 
    public function setBoleto(InstallmentPlan $boleto)
    {
        $this->boleto = $boleto;

        return $this;
    }

    /**
     * Get the value of pix
     *
     * @return  InstallmentPlan
     */ 
    public function getPix()
    {
        return $this->pix;
    }

    /**
     * Set the value of pix
     *
     * @param  InstallmentPlan  $pix
     *
     * @return  self
     */ 
    public function setPix(InstallmentPlan $pix)
    {
        $this->pix = $pix;

        return $this;
    }
}