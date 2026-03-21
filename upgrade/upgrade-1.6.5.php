<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_6_5($module)
{
    $module->RemakeWorkers($module);

    unlink(_PS_MODULE_DIR_ . 'agpagseguro/controllers/front/updatetrasactionstatus.php');
    return true;
}
