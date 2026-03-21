<form action='{$form_action}' id='agpagseguro_eft_form'>
    <input type='hidden' id='agpagseguro_credit_card_hash' name='agpagseguro_credit_card_hash'/>

	<p>Você pode pagar de maneira prática e segura com débito em conta. Se escolher pagar via débito em conta o total de sua compra será de <strong>{$total_with_discount}</strong>.</p>

	<p>Escolha o banco abaixo:</p>

	<input type='hidden' id='agpagseguro_ticket_session' name='agpagseguro_ticket_session' value="{$session_id}" />
	<input type='hidden' name='payment_mode' value='eft' />

	<div>
		<input type="radio" id='agpagseguro_bank_itau' name="agpagseguro_bank" value="ITAU" />
		<label for='agpagseguro_bank_itau'>Itaú</label>
	</div>

	<div>
		<input type="radio" id='agpagseguro_bank_bradesco' name="agpagseguro_bank" value="BRADESCO" />
		<label for='agpagseguro_bank_bradesco'>Bradesco</label>
	</div>

	<div>
		<input type="radio" id='agpagseguro_bank_brasil' name="agpagseguro_bank" value="BANCO_BRASIL" />
		<label for='agpagseguro_bank_brasil'>Banco do Brasil</label>
	</div>
</form>