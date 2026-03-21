<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_4_4()
{
    Configuration::updateValue('AGPAGSEGURO_TICKET_TEXT_CHECKOUT','Pague via Boleto Bancário');
    Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_TEXT_CHECKOUT','Pague via Cartão de Crédito');
    Configuration::updateValue('AGPAGSEGURO_EFT_TEXT_CHECKOUT','Pague via Débito em Conta');
}
