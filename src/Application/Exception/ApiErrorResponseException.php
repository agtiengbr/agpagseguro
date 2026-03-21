<?php
namespace AGTI\PagSeguro\Application\Exception;

class ApiErrorResponseException extends \Exception{
    private $errors;

    public function addError($error)
    {
        $this->errors[] = $error;
    }

    /**
     * Get the value of errors
     */ 
    public function getErrors()
    {
        return $this->errors;
    }
}