<?php
namespace AGTI\PagSeguro\Infrastructure\Mapping;

use AgColumnMapping;
use AGTI\PagSeguro\ValueObject\Configuration as VBConfiguration;

class SocialNameMapping extends AgColumnMapping
{
    public function __construct()
    {
        $this->setData(array(
            'table_name' => 'customer',
            'configuration_name' => 'AGPAGSEGURO_SOCIAL_NAME'
        ));
    }
}