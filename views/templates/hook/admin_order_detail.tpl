<div class="agpagseguro_print_ticket">
	{if !$print_ticket_link|default}
		<span class="hidden sr-only" id='agpagseguro_session'>{$agpagseguro_session}</span>
		<span class="hidden sr-only" id='agpagseguro_transaction_token'>{$agpagseguro_transaction_token}</span>
		<a target="_blank" href="" class="agpagseguro_generate_ticket btn btn-primary"><i class="icon-barcode"></i> Gerar Boleto</a>	
	{/if}
</div>