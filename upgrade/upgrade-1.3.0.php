<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_3_0($module)
{
    //reinstala os overrides
	$module->registerHook([
        'displayAdminOrderTabOrder'
    ]);

    $object_models = [
        'AgPagseguroTransaction',
        'AgPagSeguroWebhook'
    ];
    
    foreach ($object_models as $class) {
        $modelInstance = new $class;

        if (method_exists($class, 'createDatabase')) {
            $modelInstance->createDatabase();
        }

        if (method_exists($class, 'createMissingColumns')) {
            $modelInstance->createMissingColumns();
        }

        if (method_exists($class, 'createIndexes')) {
            $modelInstance->createIndexes();
        }

        if (method_exists($class, 'createDefaultData')) {
            $class::createDefaultData();
        }
    }

    return true;
}
