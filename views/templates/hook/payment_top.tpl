{if $errors|count}
	<div class='alert alert-danger'>
		<p>Para visualizar mais formas de pagamento por favor corrija os erros abaixo.</p>
		
		<ul>
			{foreach from=$errors item=error}
				<li>{$error}</li>
			{/foreach}
		</ul>
	</div>
{/if}

{if $display_banner}
	<div class='agpagseguro-banner'>
		<img src="//assets.pagseguro.com.br/ps-integration-assets/banners/seguranca/seguranca_728x90.gif" alt="Banner PagSeguro" title="Compre com PagSeguro e fique sossegado">
	</div>
{/if}