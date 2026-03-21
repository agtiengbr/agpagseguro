<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Error;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;

class  CreateOrderResponseError
{

    /** @var Error[] */
    private $errorMessages;


    public function addError(Error $error)
    {
        $this->errorMessages[] = $error;
    }
    /**
     * Get the value of errors
     */ 
    public function getErrors()
    {
        return $this->errorMessages;
    }

    /**
     * Set the value of errorMessages
     *
     * @return  self
     */ 
    public function setErrorMessages($errorMessages)
    {
        $this->errorMessages = $errorMessages;

        return $this;
    }
}