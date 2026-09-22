<?php

namespace AGTI\PagSeguro\Infrastructure\Webhook;

class ClassicNotificationPayloadParser
{
    public function parse($body)
    {
        if (!is_string($body) || $body === '') {
            throw new \InvalidArgumentException('Empty classic notification body.');
        }

        parse_str($body, $parameters);

        if (!isset($parameters['notificationType']) || !is_string($parameters['notificationType'])) {
            throw new \InvalidArgumentException('Missing classic notification type.');
        }

        if (trim($parameters['notificationType']) !== 'transaction') {
            throw new \InvalidArgumentException('Unsupported classic notification type.');
        }

        if (!isset($parameters['notificationCode']) || !is_string($parameters['notificationCode'])) {
            throw new \InvalidArgumentException('Missing classic notification code.');
        }

        $notificationCode = trim($parameters['notificationCode']);
        if (!self::isClassicNotificationCode($notificationCode)) {
            throw new \InvalidArgumentException('Invalid classic notification code.');
        }

        return $notificationCode;
    }

    public static function isClassicNotificationCode($value)
    {
        return is_string($value)
            && strlen($value) === 39
            && preg_match('/\A[A-Za-z0-9-]{39}\z/', $value) === 1;
    }

    public static function isOrderId($value)
    {
        return is_string($value) && strpos($value, 'ORD') === 0;
    }
}
