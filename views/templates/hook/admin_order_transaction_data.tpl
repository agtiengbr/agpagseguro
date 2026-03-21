<div class="tab-pane d-print-block fade" id="agpagseguroContent" role="tabpanel" aria-labelledby="agpagseguroContent">
    {if isset($transaction)}
        <dl class="dl-horizontal">
            <dt>ID da Transação</dt>
            <dd>{$transaction->getTransactionCode()}</dd>

            {* <dt>Estado da Transação</dt>
            <dd>{$transaction_status_text}</dd>

            <dt>Tipo de Transação</dt>
            <dd>
                {if $transaction->type == '0'}Boleto Bancário
                {else if $transaction->type == '1'} Cartão de Crédito
                {else if $transaction->type == '2'} Débito em Conta
                {/if}
            </dd>
        
            {if $pagseguro_transaction->getPaymentLink() != ''}
                <dt>Link para Pagamento</dt>
                <dd><a href="{$pagseguro_transaction->getPaymentLink()}" target="_blank">Clique aqui</a></dd>
            {/if}

            {if $pagseguro_transaction->getInstallmentCount() != ''}
                <dt># Parcelas</dt>
                <dd>{$pagseguro_transaction->getInstallmentCount()}</dd>
            {/if}

            <dt>Valor da Transação</dt>
            <dd>{Tools::displayPrice($pagseguro_transaction->getGrossAmount())}</dd>

            <dt>Taxa de Intermediação</dt>
            <dd>{Tools::displayPrice($pagseguro_tax)}</dd>

            <dt>Valor Líquido</dt>
            <dd>{Tools::displayPrice($pagseguro_transaction->getNetAmount())}</dd>
        </dl> *}
    {/if}
    {if !isset($transaction)}
        <div class="alert alert-danger">Esse pedido não possui um código de transação com o PagSeguro. Por favor, informe os dados abaixo.</div>

        <form method="POST">
            <div class="row form-horizontal">
                <input type="hidden" name="agpagseguro_id_order" value="{$id_order}"/>
                <div class="form-group">
                    <label class="control-label col-lg-3">Código da Transação</label>
                    <div class="col-lg-9">
                        <input type="text" name="agpagseguro_transaction_code" />
                    </div>
                </div>


                <button class="btn btn-primary" name="agpagseguro-transaction-save">Salvar</button>
            </div>
        </form>
    {/if}
</div>
