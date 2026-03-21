<?php

if (!defined('_PS_VERSION_'))
    exit;

function upgrade_module_1_3_1($module)
{
    $id_parent = Tab::getIdFromClassName("AdminParentModulesSf");

    $tabModel             = new \Tab();
    $tabModel->module     ="agpagseguro";
    $tabModel->active     = 1;
    $tabModel->class_name = 'AdminAgPagSeguroWebhook';
    $tabModel->id_parent  = $id_parent;

    foreach (\Language::getLanguages(true) as $lang) {
        $tabModel->name[$lang['id_lang']] = "PagSeguro WebHook";
    }

    return true;
}
