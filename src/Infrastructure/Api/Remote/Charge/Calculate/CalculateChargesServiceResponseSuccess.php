<?php
namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate;

use AGTI\PagSeguro\ValueObject\SimulationPaymentMethods;

class CalculateChargesServiceResponseSuccess
{
    /**
     * @var SimulationPaymentMethods
     */
    private $paymentMethods;

    /**
     * Get the value of paymentMethods
     *
     * @return  SimulationPaymentMethods
     */ 
    public function getPaymentMethods()
    {
        return $this->paymentMethods;
    }

    /**
     * Set the value of paymentMethods
     *
     * @param  SimulationPaymentMethods  $paymentMethods
     *
     * @return  self
     */ 
    public function setPaymentMethods(SimulationPaymentMethods $paymentMethods)
    {
        $this->paymentMethods = $paymentMethods;

        return $this;
    }
}