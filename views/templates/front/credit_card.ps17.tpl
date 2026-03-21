<div class='card-wrapper mb-2 mt-2'></div>

<form action="{$form_action}" method="post" id="agpagseguro_credit_card_form" class="mb-2">
    <div class="box cheque-box">
        <div class="row">
            <div id="cardnumber" class="col-xs-12 col-md-6 col-lg-7">
                <input type="text" name="agpagseguro_cardnumber" id="agpagseguro_cardnumber" class="col-xs-12" tabindex="1" autocomplete="off" maxlength="24" placeholder="Número do cartão"/>
                <div id="agpagseguro_cardbanner"></div>
            </div>
            
            <div class="col-xs-12 col-md-4 col-lg-3">
                <input type="text" name="agpagseguro_cvv" id="agpagseguro_cvv" class="col-xs-12" tabindex="1" autocomplete="off" maxlength="4" placeholder="CVV"/>
            </div>
        </div>

        <div class="row">
            <div id="cardholder" class="col-xs-12 col-md-6 col-lg-7">
                <input type="text" name="agpagseguro_name" id="agpagseguro_name" class="col-xs-12" tabindex="1" autocomplete="off" maxlength="24" placeholder="Nome conforme exibido no cartão"/>
            </div>
            
            <div class="col-xs-12 col-md-4 col-lg-5">
                <input type="text" name="agpagseguro_document" id="agpagseguro_document" class="col-xs-12" tabindex="1" autocomplete="off" placeholder="CPF"/>
            </div>
        </div>

        <div class="row">
            <div id="month" class="col-xs-12 col-md-6 col-lg-7">
                <select name="agpagseguro_installment" id="agpagseguro_installment" class="col-xs-12  is-not-selected" tabindex="1" autocomplete="off" maxlength="24" placeholder="Escolha a quantidade de parcelas">
                    <option value="-1">Escolha a quantidade de parcelas</option>

                    {if $agpagseguro_installments|count}
                        {foreach from=$agpagseguro_installments key=key item=installment}
                            <option value="{$key}">{$key+1} x {$installment.formatted_installment_value} (total de {$installment.formatted_total})</option>
                        {/foreach}
                    {/if}
                </select>
            </div>
            
            <div class="col-xs-12 col-md-4 col-lg-5">
                <input type="text" name="agpagseguro_month" id="agpagseguro_month" class="col-xs-5" tabindex="1" autocomplete="off" placeholder="Mês"/>
                <input type="text" name="agpagseguro_year" id="agpagseguro_year" class="col-xs-7" tabindex="1" autocomplete="off" placeholder="Ano"/>
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
</form>
