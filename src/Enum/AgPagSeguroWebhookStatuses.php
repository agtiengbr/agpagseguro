<?php

namespace AGTI\PagSeguro\Enum;

class AgPagSeguroWebhookStatuses extends AgPagSeguroEnum
{
    protected static $values = [
        'Aguardando Processamento',
        'Erro no Processamento',
        'Processamento Concluído'
    ];

    public static function get($index)
    {
        if (isset(self::$values[$index])) {
            return self::$values[$index];
        }

        throw new \InvalidArgumentException("Valor {$index} inválido.");
    }

    public static function getValues()
    {
        return self::$values;
    }
}