<?php

class AgPagSeguroLock
{
    /**
     * Gera uma key SysV "namespaced" para evitar colisões entre instalações.
     * @param string $name
     * @return int
     */
    public static function semaphoreKey($name)
    {
        return self::keyFromString((string) $name);
    }

    /**
     * Serializa uma seção crítica por pedido (id_order) no mesmo host.
     *
     * @param int $orderId PrestaShop order id
     * @param callable $fn Seção crítica
     * @param int $timeoutSeconds Tempo máximo de espera
     * @return mixed
     */
    public static function withOrderLock($orderId, callable $fn, $timeoutSeconds = 10)
    {
        $orderId = (int) $orderId;
        if ($orderId <= 0) {
            return $fn();
        }

        if (!function_exists('sem_get') || !function_exists('sem_acquire') || !function_exists('sem_release')) {
            return $fn();
        }

        $key = self::keyFromString('agpagseguro:order:' . $orderId);
        $sem = @sem_get($key, 1, 0666, true);
        if (!$sem) {
            return $fn();
        }

        $acquired = self::acquireWithTimeout($sem, (int) $timeoutSeconds);
        if (!$acquired) {
            throw new Exception("agpagseguro - Timeout adquirindo lock do pedido {$orderId}");
        }

        try {
            return $fn();
        } finally {
            @sem_release($sem);
        }
    }

    private static function acquireWithTimeout($sem, $timeoutSeconds)
    {
        $timeoutSeconds = (int) $timeoutSeconds;
        if ($timeoutSeconds <= 0) {
            $timeoutSeconds = 1;
        }

        $start = microtime(true);
        while ((microtime(true) - $start) < $timeoutSeconds) {
            if (@sem_acquire($sem, true)) {
                return true;
            }
            usleep(100000);
        }
        return false;
    }

    private static function keyFromString($value)
    {
        $salted = self::namespaceSalt() . '|' . (string) $value;
        $unsigned = sprintf('%u', crc32($salted));
        $key = ((int) $unsigned) & 0x7fffffff;
        if ($key === 0) {
            $key = 1;
        }
        return $key;
    }

    private static function namespaceSalt()
    {
        if (defined('_PS_ROOT_DIR_') && _PS_ROOT_DIR_) {
            return (string) _PS_ROOT_DIR_;
        }
        return __DIR__;
    }
}
