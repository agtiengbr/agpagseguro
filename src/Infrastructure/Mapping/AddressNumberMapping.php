<?php
namespace AGTI\PagSeguro\Infrastructure\Mapping;

use AgColumnMapping;
use AGTI\PagSeguro\ValueObject\Configuration;

class AddressNumberMapping extends AgColumnMapping
{
    public function __construct()
    {
        $this->setData(array(
            'table_name' => 'address',
            'configuration_name' => 'AGPAGSEGURO_ADDRESS_NUMBER'
        ));
    }
}