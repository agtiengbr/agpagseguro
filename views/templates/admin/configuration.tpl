<div class="row">
    <ul class="nav nav-tabs vertical col-lg-2" aria-orientation="vertical">
        <li class="active"><a data-toggle="tab" href="#tabConfig"><i class="icon-cogs"></i> Configurações</a></li>
        <li class=""><a href="{$url_transactions}"><i class="icon-file"></i> Transações </a></li>
        <li class=""><a href="{$url_webhooks}"><i class="icon-exchange"></i> WebHooks </a></li>
        <li class=""><a href="{$url_requests}"><i class="icon-cloud"></i> Requisições </a></li>
    </ul>



    <div class='tab-content col-lg-10'>
        <div class='tab-pane active' id="tabConfig">
            <ul class="nav nav-tabs" role="tablist">
                <li class="active"><a data-toggle="tab" href="#tabAuth"><i class="icon-lock"></i> Autenticação</a></li>
                <li><a data-toggle="tab" href="#tabMappings"><i class="icon-arrows-h"></i> Mapeamentos</a></li>
                <li><a data-toggle="tab" href="#tabCreditCard"><i class="icon-credit-card"></i> Cartão de Crédito</a></li>
                <li><a data-toggle="tab" href="#tabTicket"><i class="icon-credit-card"></i> Boleto Bancário</a></li>
                <li><a data-toggle="tab" href="#tabPIX"><i class="icon-arrows-h"></i> PIX</a></li>
                <li><a data-toggle="tab" href="#tabHelp"><i class="icon-bank"></i> Ajuda</a></li>
                <li><a data-toggle="tab" href="#tabMaintenance"><i class="icon-bank"></i> Manutenção</a></li>
            </ul>
            <div class='tab-content'>
                <div class='tab-pane active' id="tabAuth">{$tabs['auth']}</div>
                <div class='tab-pane' id="tabMappings">{$tabs['mappings']}</div>
                <div class='tab-pane' id="tabCreditCard">{$tabs['credit_card']}</div>
                <div class='tab-pane' id="tabTicket">{$tabs['ticket']}</div>
                <div class='tab-pane' id="tabPIX">{$tabs['pix']}</div>
                <div class="tab-pane" id="tabHelp"><div class="panel">{include file=$modules_path|cat:"agcliente/views/templates/hook/includes/tab_help.tpl"}</div></div>
                <div class="tab-pane" id="tabMaintenance">{$tabs['maintenance']}</div>
            </div>
        </div>
    </div>
</div>