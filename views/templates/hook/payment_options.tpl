{if $ticket_active || $credit_card_active || $eft_active}
	<div class="row">
		{if $ticket_active}
			<div class="col-xs-12">
				<p class="payment_module agpagseguro ticket">
					<a href="{$link->getModuleLink('agpagseguro', 'ticket')|escape:'html'}" title="Pague via Boleto Bancário">
						<img src="{$image_boleto}" alt="Pague via Boleto Bancário"/>
						<span>Pague via Boleto Bancário</span>
					</a>
				</p>
			</div>
		{/if}

		{if $credit_card_active}
			<div class="col-xs-12">
				<p class="payment_module agpagseguro credit_card">
					<a href="{$link->getModuleLink('agpagseguro', 'creditCard')|escape:'html'}" title="Pague via Cartão de Crédito">
						<img src="{$image_credit_card}" alt="Pague via Cartão de Crédito"/>
						<span>Pague via Cartão de Crédito</span>
					</a>
				</p>
			</div>
		{/if}

		{if $eft_active}
			<div class="col-xs-12">
				<p class="payment_module agpagseguro eft">
					<a href="{$link->getModuleLink('agpagseguro', 'eft')|escape:'html'}" title="Pague via Débito em Conta">
						<img src="{$image_eft}" alt="Pague via Débito em Conta"/>
						<span>Pague via Débito em Conta</span>
					</a>
				</p>
			</div>
		{/if}
	</div>
{/if}