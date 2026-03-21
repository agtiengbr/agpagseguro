<?php
namespace AGTI\PagSeguro\ValueObject;

class InstallmentPlan
{
    /**
     * @var int
     */
    private $installments;

    /**
     * @var int
     */
    private $installmentValue;

    /**
     * @var bool
     */
    private $interestFree;

    /**
     * @var SimulationAmount
     */
    private $amount;


    /**
     * Get the value of interestFree
     *
     * @return  bool
     */ 
    public function getInterestFree()
    {
        return $this->interestFree;
    }

    /**
     * Set the value of interestFree
     *
     * @param  bool  $interestFree
     *
     * @return  self
     */ 
    public function setInterestFree(bool $interestFree)
    {
        $this->interestFree = $interestFree;

        return $this;
    }

    /**
     * Get the value of installmentValue
     *
     * @return  int
     */ 
    public function getInstallmentValue()
    {
        return $this->installmentValue;
    }

    /**
     * Set the value of installmentValue
     *
     * @param  int  $installmentValue
     *
     * @return  self
     */ 
    public function setInstallmentValue(int $installmentValue)
    {
        $this->installmentValue = $installmentValue;

        return $this;
    }

    /**
     * Get the value of installments
     *
     * @return  int
     */ 
    public function getInstallments()
    {
        return $this->installments;
    }

    /**
     * Set the value of installments
     *
     * @param  int  $installments
     *
     * @return  self
     */ 
    public function setInstallments(int $installments)
    {
        $this->installments = $installments;

        return $this;
    }

    /**
     * Get the value of amount
     *
     * @return  SimulationAmount
     */ 
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set the value of amount
     *
     * @param  SimulationAmount  $amount
     *
     * @return  self
     */ 
    public function setAmount(SimulationAmount $amount)
    {
        $this->amount = $amount;

        return $this;
    }
}