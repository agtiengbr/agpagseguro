<?php
namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate;

class CalculateChargesServiceResponseError
{
    private $errorMessages;

    /**
     * Get the value of errorMessages
     */ 
    public function getErrorMessages()
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