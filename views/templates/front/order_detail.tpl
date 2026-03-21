{if $transaction->getType() == 0}
	<div class="box">
        <p>
            <center>
                O código de barras do seu boleto é <strong class="barcode">{$transaction->getCopyText()}</strong>
            </center>
            <br>
            {if $transaction->getTransactionLink()}
            	<center class="copyBarcode-center">
                    <a class="btn btn-primary copyBarcode" href='#'><i class="icon-barcode"></i>Copiar código de barras</a>
					<a class="btn btn-primary" href="{$transaction->getTransactionLink()}" target="_blank"><i class="icon-barcode"></i>Imprimir Boleto</a>
				</center>
			{/if}
        </p>
    </div>

{else if $transaction->getType() == 3}
    <div class="box">
        <p>
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
