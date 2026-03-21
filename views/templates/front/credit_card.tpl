{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="Voltar ao Checkout">Checkout</a><span class="navigation-pipe">{$navigationPipe}</span>Pagamento via Boleto Bancário
{/capture}

<h1 class="page-heading">
    Pagamento do Pedido
</h1>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./errors.tpl"}
{include file="$tpl_dir./order-steps.tpl"}


<form action="{$link->getModuleLink('agpagseguro', 'validation', [], true)|escape:'html':'UTF-8'}" method="post" id="agpagseguro_credit_card_form">
    <div class="box cheque-box">
        <div class="row">
            <div id="cardnumber" class="col-xs-12 col-md-6 col-lg-7">
                <label class="col-xs-12">Número do cartão</label>
                <input type="text" name="agpagseguro_cardnumber" id="agpagseguro_cardnumber" class="col-xs-12" tabindex="1" autocomplete="off" maxlength="24" placeholder="digite apenas números"/>
                <div id="agpagseguro_cardbanner"></div>
            </div>
            
            <div class="col-xs-12 col-md-4 col-lg-3">
                <label class="col-xs-12">Cod. Segurança</label>
                <input type="text" name="agpagseguro_cvv" id="agpagseguro_cvv" class="col-xs-12" tabindex="1" autocomplete="off" maxlength="4"/>
            </div>
        </div>

        <div class="row">
            <div id="cardholder" class="col-xs-12 col-md-6 col-lg-7">
                <label class="col-xs-12">Nome do Proprietário</label>
                <input type="text" name="agpagseguro_name" id="agpagseguro_name" class="col-xs-12" tabindex="1" autocomplete="off" maxlength="24" placeholder="Nome conforme exibido no cartão"/>
            </div>
            
            <div class="col-xs-12 col-md-4 col-lg-5">
                <label class="col-xs-12">CPF</label>
                <input type="text" name="agpagseguro_document" id="agpagseguro_document" class="col-xs-12" tabindex="1" autocomplete="off"/>
            </div>
        </div>

        <div class="row">
            <div id="month" class="col-xs-12 col-md-6 col-lg-7">
                    <label class="col-xs-12">Parcelamento</label>
                <select name="agpagseguro_installment" id="agpagseguro_installment" class="col-xs-12  is-not-selected" tabindex="1" autocomplete="off" maxlength="24">
                    <option value="-1">Escolha a quantidade de parcelas</option>

                    {if $installments|count}
                        {foreach from=$installments key=key item=installment}
                            <option value="{$key}">{$key+1} x {$installment.formatted_installment_value} (total de {$installment.formatted_total})</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
            
            <div class="col-xs-12 col-md-4 col-lg-5">
                <label class="col-xs-12">Validade:</label>
                <span>
                    <select name="agpagseguro_month" id="agpagseguro_month" class="col-xs-7  is-not-selected" tabindex="1" autocomplete="off" maxlength="24">
                        <option value="-1">Mês</option>
                        <option value="1">Janeiro</option>
                        <option value="2">Fevereiro</option>
                        <option value="3">Março</option>
                        <option value="4">Abril</option>
                        <option value="5">Maio</option>
                        <option value="6">Junho</option>
                        <option value="7">Julho</option>
                        <option value="8">Agosto</option>
                        <option value="9">Setembro</option>
                        <option value="10">Outubro</option>
                        <option value="11">Novembro</option>
                        <option value="12">Dezembro</option>
                    </select>
                </span>

                <span>
                    <select name="agpagseguro_year" id="agpagseguro_year" class="col-xs-5 is-not-selected" tabindex="1" autocomplete="off" maxlength="24">
                        <option value="-1">Ano</option>
                        {for $year=0 to 12}
                            {$y = date('Y') + $year}
                            <option value="{$y}">{$y}</option>
                        {/for}
                    </select>
                </span>
            </div>
        </div>
    </div>

    <div class="hidden">
        <input type='hidden' id='agpagseguro_credit_card_session' name='agpagseguro_credit_card_session' value="{$session_id}" />
        <input type='hidden' name='payment_mode' value='credit_card' />
        <input type='hidden' id="agpagseguro_credit_card_total" value='{$total}' />
        <input type='hidden' id='agpagseguro_credit_card_token' name='agpagseguro_credit_card_token'/>
        <input type='hidden' id='agpagseguro_credit_card_hash' name='agpagseguro_credit_card_hash'/>
        <input type='hidden' id='agpagseguro_cardbanner_name' name='agpagseguro_cardbanner_name'/>
    </div>

    <p class="cart_navigation clearfix" id="cart_navigation">
        <a class="button-exclusive btn btn-default" href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}">
            <i class="icon-chevron-left"></i>Outras formas de pagamento
        </a>
        <button class="button btn btn-default button-medium" type="submit">
            <span>Confirmar a Compra<i class="icon-chevron-right right"></i></span>
        </button>
    </p>
</form>