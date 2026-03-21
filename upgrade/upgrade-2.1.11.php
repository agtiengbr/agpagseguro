<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_1_11($module)
{
    $webhookUrl = '';

    if (
        isset($module->context)
        && isset($module->context->link)
        && is_object($module->context->link)
    ) {
        $webhookUrl = $module->context->link->getModuleLink($module->name, 'webhook');
    }

    if (empty($webhookUrl)) {
        $domain = Tools::getShopDomainSsl(true, true);
        $baseUri = __PS_BASE_URI__;
        $webhookUrl = $domain . $baseUri . 'module/agpagseguro/webhook';
    }

    return Configuration::updateValue('AGPAGSEGURO_WEBHOOK_NOTIFICATION_URL', $webhookUrl);
}
