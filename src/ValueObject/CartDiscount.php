<?php
namespace AGTI\PagSeguro\ValueObject;

interface CartDiscount
{
    public function calcDiscount($baseValue);
}