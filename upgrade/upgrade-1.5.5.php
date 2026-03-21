<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_5_5($module)
{
    $module->RemakeWorkers();
    return true;
}
