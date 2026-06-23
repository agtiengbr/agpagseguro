<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_12($module)
{
    return Configuration::updateValue('AGPAGSEGURO_SHOW_MISSING_TRANSACTION_WARNING', 1);
}
