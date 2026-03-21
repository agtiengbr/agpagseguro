<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_0($module)
{
    $db = Db::getInstance();
    $table = _DB_PREFIX_ . 'agpagseguro_transaction';
    $columns = $db->executeS("SHOW COLUMNS FROM `{$table}` LIKE 'card_brand'");
    if (empty($columns)) {
        $db->execute("ALTER TABLE `{$table}` ADD `card_brand` VARCHAR(50) NULL DEFAULT NULL AFTER `value`");
    }
    return true;
}
