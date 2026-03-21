<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_2_12()
{
    if (Configuration::get('AGPAGSEGURO_TICKET_ENABLE') || Configuration::get('AGPAGSEGURO_TICKET_ENABLED')) {
        Configuration::updateValue('AGPAGSEGURO_TICKET_ENABLE', 1);
    }

    if (Configuration::get('AGPAGSEGURO_EFT_ENABLE') || Configuration::get('AGPAGSEGURO_EFT_ENABLED')) {
        Configuration::updateValue('AGPAGSEGURO_EFT_ENABLE', 1);
    }

    return true;
}
