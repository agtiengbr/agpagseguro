<?php

namespace AGTI\PagSeguro\ValueObject;

class CartPercentDiscount implements CartDiscount
{
    private $percentValue;

    public function __construct($percentValue)
    {
        $this->percentValue = $percentValue;
    }

    public function calcDiscount($baseValue)
    {
        return $baseValue * (1 - $this->percentValue / 100);
    }
}