<?php

namespace AGTI\PagSeguro\Application\Service;

final class CardholderDocument
{
    public static function normalize($value): string
    {
        $digits = is_string($value) ? preg_replace('~[./\s-]+~', '', $value) : '';
        if (!preg_match('/^(?:[0-9]{11}|[0-9]{14})$/D', $digits)
            || count(array_unique(str_split($digits))) === 1
        ) {
            throw new \InvalidArgumentException('Informe um CPF ou CNPJ válido para o titular do cartão.');
        }

        $length = strlen($digits);
        for ($index = $length - 2; $index < $length; ++$index) {
            $sum = 0;
            for ($offset = 0; $offset < $index; ++$offset) {
                $distance = $index - $offset - 1;
                $weight = 2 + ($length === 11 ? $distance : $distance % 8);
                $sum += (int) $digits[$offset] * $weight;
            }
            $remainder = $sum % 11;
            $expected = $remainder < 2 ? 0 : 11 - $remainder;
            if ($expected !== (int) $digits[$index]) {
                throw new \InvalidArgumentException('Informe um CPF ou CNPJ válido para o titular do cartão.');
            }
        }

        return $digits;
    }
}
