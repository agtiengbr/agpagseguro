<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_6_3($module)
{
    $module->RemakeWorkers($module);

    Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "agpagseguro_request add column analyzed boolean default 0;");
    Db::getInstance()->execute("ALTER TABLE " . _DB_PREFIX_ . "agpagseguro_request add INDEX idx_analyzed on analyzed;");

    return true;
}
