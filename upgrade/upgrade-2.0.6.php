<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_2_0_6($module)
{
    require_once _PS_MODULE_DIR_ . 'agpagseguro/classes/AgPagseguroMovement.php';
    $module->RemakeWorkers($module);

    $modelInstance = new AgPagseguroMovement;
    $modelInstance->createDatabase();
    $modelInstance->createMissingColumns();

    return true;
}
