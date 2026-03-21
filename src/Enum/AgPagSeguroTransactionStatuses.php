<?php

namespace AGTI\PagSeguro\Enum;

class AgPagSeguroTransactionStatuses extends AgPagSeguroEnum
{
    public static $values = [
        'WAITING' => 'Aguardando Pagamento',
        'PAID' => 'Pagamento Confirmado',
        'DECLINED' => 'Pagamento Recusado',
        'IN_ANALYSIS' => 'Sob Análise',
        'CANCELED' => 'Transação Cancelada',
        'PAGSEGURO_ERROR' => 'Erro Retornado pelo PagSeguro',
        "CANCELED_AFTER_PAYMENT" => "Cancelado após pagamento"
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