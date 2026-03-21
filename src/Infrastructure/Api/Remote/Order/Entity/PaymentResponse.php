<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class PaymentResponse
{
    private $code;
    private $message;
    private $reference;
    private $rawData;

    /**
     * Get the value of rawData
     */ 
    public function getRawData()
    {
        return $this->rawData;
    }

    /**
     * Set the value of rawData
     *
     * @return  self
     */ 
    public function setRawData(PaymentResponseRawData $rawData)
    {
        $this->rawData = $rawData;

        return $this;
    }

    /**
     * Get the value of reference
     */ 
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the value of reference
     *
     * @return  self
     */ 
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get the value of message
     */ 
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set the value of message
     *
     * @return  self
     */ 
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }

    /**
     * Get the value of code
     */ 
    public function getCode()
    {
        return $this->code;
    }

    /**
     * Set the value of code
     *
     * @return  self
     */ 
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }
}