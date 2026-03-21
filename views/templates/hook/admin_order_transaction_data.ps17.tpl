<div class="tab-pane d-print-block fade" id="agpagseguroContent" role="tabpanel" aria-labelledby="agpagseguroContent">
    {if isset($transaction)}
        <dl class="dl-horizontal">
            <dt>ID da Transação</dt>
            <dd>{$transaction->getTransactionCode()}</dd>

            <dt>Estado da Transação</dt>
            {assign var='charges' value=$apiOrder->getCharges()}

            {if is_array($charges) && count($charges)}
                <dd>{$charges[0]->getStatus()}</dd>
            {/if}

            <dt>Tipo de Transação</dt>
            <dd>
                {if $transaction->getType() == '0'}Boleto Bancário
                {else if $transaction->getType() == '1'} Cartão de Crédito
                {else if $transaction->getType() == '2'} Débito em Conta
                {else if $transaction->getType() == '3'} PIX
                {/if}
            </dd>
        
            {if $transaction->getTransactionLink() != ''}
                <dt>Link para Pagamento</dt>
                <dd><a href="{$transaction->getTransactionLink()}" target="_blank">Clique aqui</a></dd>
            {/if}

            {if $transaction->getQtyInstallments() != ''}
                <dt># Parcelas</dt>
                <dd>{$transaction->getQtyInstallments()}</dd>
            {/if}

            {if isset($charges) && count($charges)}
                <dt>Valor da Transação</dt>
                <dd>{Tools::displayPrice($charges[0]->getAmount()->getValue() / 100)}</dd>
            {else if is_array($qrcodes) && count($qrcodes)}
                {assign var='qrcodes' value=$apiOrder->getQrCodes()}
                <dt>Valor da Transação</dt>
                <dd>{Tools::displayPrice($qrcodes[0]->getAmount()->getValue() / 100)}</dd>
            {/if}

            {* <dt>Taxa de Intermediação</dt>
            <dd>{Tools::displayPrice($pagseguro_tax)}</dd>

            <dt>Valor Líquido</dt>
            <dd>{Tools::displayPrice($pagseguro_transaction->getNetAmount())}</dd> *}
        </dl>
    {else}
        <div class="alert alert-danger">Esse pedido não possui um código de transação com o PagSeguro. Por favor, informe os dados abaixo.</div>

        <form method="POST">
            <div class="form-horizontal">
                <input type="hidden" name="agpagseguro_id_order" value="{$id_order}"/>
                <div class="form-group">
                    <label class="control-label">Código da Transação</label>
                    <div class="">
                        <input type="text" name="agpagseguro_transaction_code"  class="form-control"/>
                    </div>
                </div>


                <button class="btn btn-primary" name="agpagseguro-transaction-save">Salvar</button>
            </div>
        </form>
    {/if}
</div>
