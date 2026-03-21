<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_6_1($module)
{
    $module->registerHook(['actionOrderStatusUpdate']);
    return true;
}
