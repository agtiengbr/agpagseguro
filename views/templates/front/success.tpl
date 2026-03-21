{if $error}
    <p class="alert alert-warning payment-error-message">{$error}</p>
{else}
    {if $transaction->getType() == 0}
        {if $order->hasBeenPaid()}
            <p class="alert alert-success agpagseguro-payment-approved">
                {l s='Pagamento via Boleto aprovado! Seu pedido já está sendo processado.' mod='agpagseguro'}
            </p>

            <div class="box">
                <p>
                    {l s='Obrigado por concluir o pagamento do seu boleto. Assim que o envio for liberado, você receberá todas as atualizações por e-mail.' mod='agpagseguro'}
                    <br />{l s='Referência do pedido:' mod='agpagseguro'} <span class="reference"><strong>{$order->reference|escape:'html':'UTF-8'}</strong></span>
                </p>
            </div>
        {else}
            <p class="alert alert-success">Seu pedido foi concluído com sucesso!</p>

            <div class="box">
                <p>
                    Você escolheu pagar via Boleto Bancário!
                    <br />Referência do seu pedido: <span class="reference"><strong>{$order->reference|escape:'html':'UTF-8'}</strong></span>

                    <p>
                        {if $payment_link}
                            O boleto foi enviado para você por e-mail, mas você também pode imprimí-lo no botão abaixo.
                        {else}
                            O boleto foi enviado para você por e-mail.
                        {/if}
                        <br />Se você tem alguma dúvida ou comentário sobre a compra, por favor entre em contato com a nossa <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">equipe especializada de atendimento ao cliente!</a>
                        <br /> O código de barras do seu boleto é <strong class="barcode">{$transaction->getCopyText()}</strong>
                    </p>

                    {if $transaction->getTransactionLink()}
                        <center>
                            <a class="btn btn-primary copyBarcode" href='#'><i class="icon-barcode"></i>Copiar código de barras</a>
                            <a class="btn btn-primary" href="{$transaction->getTransactionLink()}" target="_blank"><i class="icon-barcode"></i>Imprimir Boleto</a>
                        </center>
                    {/if}
                </p>
            </div>
        {/if}
    {else if $transaction->getType() == 1}
        {if $transaction->getTransactionStatus() == 'DECLINED'}
            <p class="alert alert-danger payment-error-message">Desculpe, ocorreu um erro no seu pagamento.</p>
        {else}
            <p class="alert alert-success">Seu pedido foi concluído com sucesso!</p>
        {/if}

        {if !$order->hasBeenPaid()}
            <div id="agpagseguro-waiting-alert" class="alert alert-info" role="alert">
                {l s='Estamos aguardando a confirmação do pagamento. Assim que o PagSeguro aprovar, atualizaremos esta página automaticamente.' mod='agpagseguro'}
            </div>
        {/if}
        <div class="box">
            <p>
                Você escolheu pagar via Cartão de Crédito!
                <br />Referência do seu pedido: <span class="reference"><strong>{$order->reference|escape:'html':'UTF-8'}</strong></span>

                <p>
                    {if $transaction->getTransactionStatus() !== 'DECLINED'}
                    Seu pedido será enviado após a confirmação do pagamento pela operadora de cartão de crédito.
                    <br />
                    {/if}
                    Se você tem alguma dúvida ou comentário sobre a compra, por favor entre em contato com a nossa <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">equipe especializada de atendimento ao cliente!</a>
                </p>

                O estado atual do seu pedido é: <strong id="agpagseguro-order-current-state">{$order_state->name}</strong>
            </p>
        </div>
    {* {else if $transaction->getType() == 2}
        <p class="alert alert-success">Seu pedido foi concluído com sucesso!</p>
        <div class="box">
            <p>
                Você escolheu pagar via Débito em Conta!
                <br />Referência do seu pedido: <span class="reference"><strong>{$order->reference|escape:'html':'UTF-8'}</strong></span>

                <p>
                    <br />Se você tem alguma dúvida ou comentário sobre a compra, por favor entre em contato com a nossa <a href="{$link->getPageLink('contact', true)|escape:'html':'UTF-8'}">equipe especializada de atendimento ao cliente!</a>
                </p>

                <p>
                    Para realizar o pagamento, clique no botão baixo:
                    <br /> <center> <a href="{$payment_link}" class="btn btn-primary" target="_blank">Realizar Pagamento</a> </center>
                </p>

                O estado atual do seu pedido é: {$order_state->name}
            </p>
        </div>
    {/if} *}
    {else if $transaction->getType() == 3}
        {if $order->hasBeenPaid()}
            <p class="alert alert-success agpagseguro-payment-approved">
                {l s='Pagamento via PIX aprovado! Já estamos preparando o envio do seu pedido.' mod='agpagseguro'}
            </p>

            <div class="box">
                <p>
                    {l s='Você receberá as atualizações de entrega por e-mail. Obrigado por comprar conosco!' mod='agpagseguro'}
                    <br />{l s='Referência do pedido:' mod='agpagseguro'} <span class="reference"><strong>{$order->reference|escape:'html':'UTF-8'}</strong></span>
                </p>
            </div>
        {else}
            <p class="alert alert-success">Seu pedido foi concluído com sucesso!</p>

            {if !$order->hasBeenPaid()}
                <div id="agpagseguro-waiting-alert" class="alert alert-info" role="alert">
                    {l s='Estamos aguardando a confirmação do pagamento via PIX. Assim que for aprovado, atualizaremos esta página automaticamente.' mod='agpagseguro'}
                </div>
            {/if}
            <div class="box">
                <p>
                    Você escolheu pagar via PIX!
                    <br />Referência do seu pedido: <span class="reference"><strong>{$order->reference|escape:'html':'UTF-8'}</strong></span>

                    <br>O estado atual do seu pedido é: <strong id="agpagseguro-order-current-state">{$order_state->name}</strong>

                    <img id="pix-qrcode" data-pix-data="{$transaction->getCopyText()}" src="{$transaction->getTransactionLink()}" />

                    <center>
                        <a class="btn btn-primary copyPix" href="#"><i class="icon-barcode"></i>Copiar código PIX</a>
                    </center>

                    <a id="pix-message">
                        <small>Ou copie o texto abaixo e utilize a opção de PIX Copia e Cola do seu banco.</small>
                    </a>

                    <p id="pix-message-code">
                        <small>
                            <b>Copie o seguinte código para efetuar o pagamento:</b>
                            <br>
                            {$transaction->getCopyText()}
                        </small>
                    </p>

                </p>
            </div>
        {/if}
    {/if}
{/if}
