<?php

namespace AGTI\PagSeguro\Infrastructure\Webhook;

class ClassicTransactionStatusMapper
{
    // AVAILABLE is equivalent to PAID for the store's existing order-state mapping.
    // Dispute (5) and temporary hold (9) have no equivalent module state; callers
    // retain their raw code and leave the order state untouched.
    private const MODULE_STATUS_BY_CLASSIC_CODE = [
        '1' => 'WAITING',
        '2' => 'IN_ANALYSIS',
        '3' => 'PAID',
        '4' => 'PAID',
        '6' => 'CANCELED_AFTER_PAYMENT',
        '7' => 'CANCELED',
        '8' => 'CANCELED_AFTER_PAYMENT',
    ];

    public static function toModuleStatus($classicStatus)
    {
        if (!is_string($classicStatus) && !is_int($classicStatus)) {
            return null;
        }

        $classicStatus = (string) $classicStatus;

        return self::MODULE_STATUS_BY_CLASSIC_CODE[$classicStatus] ?? null;
    }
}
