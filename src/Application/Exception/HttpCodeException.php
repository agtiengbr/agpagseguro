<?php
namespace AGTI\PagSeguro\Application\Exception;

class HttpCodeException extends \Exception
{
    private $httpCode;
    
    public function __construct(string $message, int $code)
    {
        parent::__construct($message);
        
        $this->code = $code;
    }

    /**
     * Get the value of httpCode
     */ 
    public function getHttpCode()
    {
        return $this->httpCode;
    }
}