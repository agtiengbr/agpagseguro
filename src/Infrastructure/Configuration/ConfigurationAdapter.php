<?php

namespace AGTI\PagSeguro\Infrastructure\Configuration;

use AGTI\PagSeguro\Infrastructure\Mapping\AddressNumberMapping;
use AGTI\PagSeguro\Infrastructure\Mapping\CnpjMapping;
use AGTI\PagSeguro\Infrastructure\Mapping\CPFMapping;
use AGTI\PagSeguro\Infrastructure\Mapping\SocialNameMapping;
use AGTI\PagSeguro\ValueObject\Configuration as VBConfiguration;

abstract class ConfigurationAdapter
{
    public static function buildConfiguration()
    {
        return new VBConfiguration(
            new CPFMapping,
            new CnpjMapping,
            new AddressNumberMapping,
            new SocialNameMapping,
            \Configuration::get('AGPAGSEGURO_SANDBOX'),
            \Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX'),
            \Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION'),
            \Configuration::get('AGPAGSEGURO_EDI_USER'),
            \Configuration::get('AGPAGSEGURO_EDI_TOKEN')
        );
    }

    public function persistConfiguration(VBConfiguration $config)
    {
        \Configuration::updateValue('AGPAGSEGURO_SANDBOX', $config->getIsSandbox());
        \Configuration::updateValue('AGPAGSEGURO_TOKEN_PRODUCTION', $config->getTokenLive());
        \Configuration::updateValue('AGPAGSEGURO_TOKEN_SANDBOX', $config->getTokenSandbox());

        \Configuration::updateValue('AGPAGSEGURO_EDI_USER', $config->getEdiUsername());
        \Configuration::updateValue('AGPAGSEGURO_EDI_TOKEN', $config->getEdiToken());


    }
}