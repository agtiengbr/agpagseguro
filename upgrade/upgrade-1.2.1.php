<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_2_1($module)
{
    //reinstala os overrides
	$module->registerHook([
	    'displayHeader',
        'displayBackOfficeHeader',
        'paymentOptions',
        'payment',
        'orderConfirmation',
        'displayOrderDetail',
        'displayAdminOrderContentOrder'
    ]);

    return true;
}
