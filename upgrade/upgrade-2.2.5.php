<?php
if (!defined('_PS_VERSION_')) {
    exit;
}

function upgrade_module_2_2_5($module)
{
    // Por padrão, um 404 na consulta de uma Order não deve cancelar o pedido.
    // O administrador pode escolher outro estado no mapeamento do módulo.
    return Configuration::updateValue('AGPAGSEGURO_STATUS_NOT_FOUND', -1)
        && Configuration::updateValue('AGPAGSEGURO_STATUS_NOT_FOUND_FINISH_ORDER', 0);
}
