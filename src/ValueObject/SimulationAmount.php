<?php
namespace AGTI\PagSeguro\ValueObject;

class SimulationAmount
{
    /**
     * @var int
     */
    private $value;

    /**
     * @var Fees
     */
    private $fees;

    /**
     * Get the value of value
     *
     * @return  int
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @param  int  $value
     *
     * @return  self
     */ 
    public function setValue( $value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of fees
     *
     * @return  Fees
     */ 
    public function getFees()
    {
        return $this->fees;
    }

    /**
     * Set the value of fees
     *
     * @param  Fees  $fees
     *
     * @return  self
     */ 
    public function setFees(Fees $fees)
    {
        $this->fees = $fees;

        return $this;
    }
}