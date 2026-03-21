<form action='{$form_action}' id='agpagseguro_ticket_form'>
	<p>Você pode pagar o boleto em qualquer banco ou casa lotérica. Se escolher pagar via boleto, o total de sua compra será de <strong>{$total_with_discount}</strong>.</p>

	<input type='hidden' id='agpagseguro_ticket_session' name='agpagseguro_ticket_session' value="{$session_id}" />
	<input type='hidden' id='agpagseguro_credit_card_hash' name='agpagseguro_credit_card_hash'/>

	<input type='hidden' name='payment_mode' value='ticket' />
</form>