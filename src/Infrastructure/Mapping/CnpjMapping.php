<?php
namespace AGTI\PagSeguro\Infrastructure\Mapping;

use AgColumnMapping;
use AGTI\PagSeguro\ValueObject\Configuration;

class CnpjMapping extends AgColumnMapping
{
    public function __construct()
    {
        $this->setData(array(
            'table_name' => 'customer',
            'configuration_name' => 'AGPAGSEGURO_CNPJ'
        ));
        $this->addColumn('djtalbrazilianregister', 'Módulo de Cadastro Brasileiro');
        $this->addColumn('ldbrazilianregister', 'Módulo de Cadastro LD');
        $this->addColumn('psmodcpf', 'Módulo CPF/CNPJ SoliSYS');

    }
}