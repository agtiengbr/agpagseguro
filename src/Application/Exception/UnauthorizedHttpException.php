<?php
namespace AGTI\PagSeguro\Application\Exception;

class UnauthorizedHttpException extends \Exception
{
    private $httpCode;
    
    public function __construct(string $message)
    {
        parent::__construct($message);
    }
}