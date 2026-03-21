<?php

namespace AGTI\PagSeguro\Enum;

abstract class AgPagSeguroEnum
{
    /** @var array */
    protected static $values = [];

    abstract public static function get($index);
}