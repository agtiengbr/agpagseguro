document.addEventListener('DOMContentLoaded', function(){
	var btn_generate_ticket = document.querySelector('.agpagseguro_generate_ticket ');

	if (btn_generate_ticket == null) {
		return;
	}
	
	var agpagseguro_session = document.getElementById('agpagseguro_session').innerHTML;
	var token = document.getElementById('agpagseguro_transaction_token').innerHTML;

	PagSeguroDirectPayment.setSessionId(agpagseguro_session);
	
	btn_generate_ticket.addEventListener('click', function(e){
		e.preventDefault();
		e.stopPropagation();

		var that = this;
		
		var sender_hash = PagSeguroDirectPayment.getSenderHash();
		if (!sender_hash) {
			displayError("Ocorreu um erro inesperado. Por favor tente novamente em alguns segundos.");
		}

		generating_ticket = true;

		$.ajax({
			'url' : 'index.php',
			'dataType' : 'json',
			'data': {
				'controller' : 'AdminAgPagSeguroTransaction',
				'token' : token,
				'ajax' : true,
				'action' : 'CreateTicket',
				'id_order' : id_order,
				'sender_hash' : sender_hash
			},
			success: function(data) {
				generating_ticket = false;
				if (typeof data.ticket_url !== 'undefined') {
					that.href = data.ticket_url;
					that.innerHTML = "<i class='icon-barcode'/> Imprimir Boleto";

					var win = window.open(data.ticket_url, '_blank');
					if (win) {
						win.focus();
					} else {
						alert('Um bloqueador de popup está impedindo o boleto de ser aberto.');
					}
				} else {
					alert('Ocorreu um erro ao gerar o boleto.');
				}
			}
		})
	});
});