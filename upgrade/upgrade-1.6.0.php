<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_6_0($module)
{
    require_once _PS_MODULE_DIR_ . 'agcliente/agcliente.php';
    $agcliente = new agcliente;
    $agcliente->updateModuleTables($module);
    $module->RemakeWorkers($module);

    return true;
}
