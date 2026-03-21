<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class Error
{
    private $code;
    private $error;
    private $description;
    private $parameterName;

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

    /**
     * Get the value of error
     */ 
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */ 
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get the value of description
     */ 
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set the value of description
     *
     * @return  self
     */ 
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get the value of parameterName
     */ 
    public function getParameterName()
    {
        return $this->parameterName;
    }

    /**
     * Set the value of parameterName
     *
     * @return  self
     */ 
    public function setParameterName($parameterName)
    {
        $this->parameterName = $parameterName;

        return $this;
    }
}