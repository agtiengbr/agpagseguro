<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class PaymentResponseRawData
{
    private $authorizationCode;
    private $nsu;
    private $reasonCode;

    /**
     * Get the value of reasonCode
     */ 
    public function getReasonCode()
    {
        return $this->reasonCode;
    }

    /**
     * Set the value of reasonCode
     *
     * @return  self
     */ 
    public function setReasonCode($reasonCode)
    {
        $this->reasonCode = $reasonCode;

        return $this;
    }

    /**
     * Get the value of nsu
     */ 
    public function getNsu()
    {
        return $this->nsu;
    }

    /**
     * Set the value of nsu
     *
     * @return  self
     */ 
    public function setNsu($nsu)
    {
        $this->nsu = $nsu;

        return $this;
    }

    /**
     * Get the value of authorizationCode
     */ 
    public function getAuthorizationCode()
    {
        return $this->authorizationCode;
    }

    /**
     * Set the value of authorizationCode
     *
     * @return  self
     */ 
    public function setAuthorizationCode($authorizationCode)
    {
        $this->authorizationCode = $authorizationCode;

        return $this;
    }
    
}