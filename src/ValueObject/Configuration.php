<?php

namespace AGTI\PagSeguro\ValueObject;

use AGTI\PagSeguro\Infrastructure\Mapping\AddressNumberMapping;
use AGTI\PagSeguro\Infrastructure\Mapping\CnpjMapping;
use AGTI\PagSeguro\Infrastructure\Mapping\CPFMapping;
use AGTI\PagSeguro\Infrastructure\Mapping\SocialNameMapping;

class Configuration
{
    private $cpfMapping;
    private $cnpjMapping;
    private $socialNameMapping;
    private $addressNumberMapping;
    private $isSandbox;
    private $tokenSandbox;
    private $tokenLive;
    private $ediUsername;
    private $ediToken;

    public function __construct(
        CPFMapping $cpfMapping,
        CnpjMapping $cnpjMapping,
        AddressNumberMapping $addressNumberMapping,
        SocialNameMapping $socialNameMapping,
        $isSandbox,
        $tokenSandbox,
        $tokenLive,
        $ediUsername,
        $ediToken
    ){
        $this->cpfMapping = $cpfMapping;
        $this->cnpjMapping = $cnpjMapping;
        $this->addressNumberMapping = $addressNumberMapping;
        $this->socialNameMapping = $socialNameMapping;

        $this->isSandbox = $isSandbox;
        $this->tokenSandbox = $tokenSandbox;
        $this->tokenLive = $tokenLive;

        $this->ediUsername = $ediUsername;
        $this->ediToken = $ediToken;
    }

    /**
     * Get the value of tokenLive
     */ 
    public function getTokenLive()
    {
        return $this->tokenLive;
    }

    /**
     * Get the value of tokenSandbox
     */ 
    public function getTokenSandbox()
    {
        return $this->tokenSandbox;
    }

    /**
     * Get the value of isSandbox
     */ 
    public function getIsSandbox()
    {
        return $this->isSandbox;
    }

    /**
     * Get the value of addressNumberMapping
     */ 
    public function getAddressNumberMapping()
    {
        return $this->addressNumberMapping;
    }

    /**
     * Get the value of socialNameMapping
     */ 
    public function getSocialNameMapping()
    {
        return $this->socialNameMapping;
    }

    /**
     * Get the value of cnpjMapping
     */ 
    public function getCnpjMapping()
    {
        return $this->cnpjMapping;
    }

    /**
     * Get the value of cpfMapping
     */ 
    public function getCpfMapping()
    {
        return $this->cpfMapping;
    }

    public function getCurrentToken()
    {
        if ($this->getIsSandbox()) {
            return $this->getTokenSandbox();
        }

        return $this->getTokenLive();
    }

    /**
     * Get the value of ediUsername
     */ 
    public function getEdiUsername()
    {
        return $this->ediUsername;
    }

    /**
     * Get the value of ediToken
     */ 
    public function getEdiToken()
    {
        return $this->ediToken;
    }
}