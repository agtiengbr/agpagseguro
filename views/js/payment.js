
// TOKEN

document.addEventListener('DOMContentLoaded', function(){
	var agpagseguro_session;

	var input_document    = $('#agpagseguro_document');
	var input_cvv         = $('#agpagseguro_cvv');
	var input_card_number = $('#agpagseguro_cardnumber');
	var input_expir_month = $('#agpagseguro_month');
	var input_expir_year  = $('#agpagseguro_year');
	var input_card_holder  = $('#agpagseguro_name');

	var cardbanner;
	var btn_submit;

	var spinHandle;

	var error_codes = {
		"INVALID_NUMBER" : "O número do cartão de crédito não é válido.",
		"10001" : "Quantidade de números do cartão inválida.",
		"10002" : "Data de vencimento do cartão inválida.",
		"10003" : "Código de segurança do cartão inválido.",
		"10004" : "Código de segurança do cartão é obrigatório.",
		"10006" : "Quantidade de números do código de segurança inválida.",
		"30405" : "Data de vencimento do cartão inválida.",
		"53005" : "Moeda obrigatória.",
		"53006" : "Moeda inválida.",
		"53007" : "Compriento da referência inválido.",
		"53008" : "URL de notificação inválida.",
		"53009" : "URL de notificação inválida.",
		// "53010" : "E-mail do comprador inválido.",
		// "53011" : "E-mail do comprador inválido.",
		// "53012" : "E-mail do comprador inválido.",
		// "53013" : "Nome do comprador inválido.",
		// "53014" : "Nome do comprador inválido.",
		// "53015" : "Nome do comprador inválido.",
		// "53017" : "CPF do comprador inválido.",
		"53037" : "Token do cartão inválido.",
		"53038" : "Selecione a quantidade de parcelas.",
		"53042" : "Nome do proprietário do cartão inválido.",
		"53045" : "CPF inválido.",
		"53046" : "CPF inválido."

	};

	// //obtém o ID da sessão do pagseguro
	// var agpagseguro_ticket_session = document.getElementById('agpagseguro_ticket_session');
	// var has_ticket_payment = agpagseguro_ticket_session != null;

	// if (has_ticket_payment) {
	// 	agpagseguro_session = agpagseguro_ticket_session.value;
	// }

	// var agpagseguro_credit_card_session = document.getElementById('agpagseguro_credit_card_session');
	// var has_credit_card_payment = agpagseguro_credit_card_session != null;

	if (typeof agpagseguro_credit_card_session !== 'undefined') {
		agpagseguro_session = agpagseguro_credit_card_session.value;

		VMasker(input_cvv).maskPattern("9999");
		VMasker(input_card_number).maskPattern("9999 9999 9999 9999");
		VMasker(input_document).maskPattern("999.999.999-99");
        // }

        //verifica se algum ID foi obtido, seja pelo pagamento via boleto, seja pelo pagamento via cartão
        // if (agpagseguro_session != null) {
        // 	PagSeguroDirectPayment.setSessionId(agpagseguro_session);

        // 	//botão para concluir o pagamento

        // 	//prestashop 1.7
        var payment_confirmation = document.getElementById('payment-confirmation');
        btn_submit = payment_confirmation.getElementsByTagName('button');
        if (btn_submit.length != null) {
            btn_submit = btn_submit[0];
            btn_submit.addEventListener('click', formSubmited);
        }
        // 	} else {
        // 		//prestashop 1.6
        // 		btn_submit = document.querySelector('#agpagseguro_credit_card_form button.button-medium, #agpagseguro_ticket_form button.button-medium, #agpagseguro_eft_form button.button-medium');
        // 		btn_submit.addEventListener('click', formSubmited);
        // 	}
        // }
    }

	function validateCpf(cpf)
    {
        // Removing special characters from value
        var value = cpf.replace( /([~!@#$%^&*()_+=`{}\[\]\-|\\:;'<>,.\/? ])+/g, "" );

        // Checking value to have 11 digits only
        if ( value.length !== 11 ) {
            return false;
        }

        var sum = 0,
            firstCN, secondCN, checkResult, i;

        firstCN = parseInt( value.substring( 9, 10 ), 10 );
        secondCN = parseInt( value.substring( 10, 11 ), 10 );

        checkResult = function( sum, cn ) {
            var result = ( sum * 10 ) % 11;
            if ( ( result === 10 ) || ( result === 11 ) ) {
                result = 0;
            }
            return ( result === cn );
        };

        // Checking for dump data
        if ( value === "" ||
            value === "00000000000" ||
            value === "11111111111" ||
            value === "22222222222" ||
            value === "33333333333" ||
            value === "44444444444" ||
            value === "55555555555" ||
            value === "66666666666" ||
            value === "77777777777" ||
            value === "88888888888" ||
            value === "99999999999"
        ) {
            return false;
        }

        // Step 1 - using first Check Number:
        for ( i = 1; i <= 9; i++ ) {
            sum = sum + parseInt( value.substring( i - 1, i ), 10 ) * ( 11 - i );
        }

        // If first Check Number (CN) is valid, move to Step 2 - using second Check Number:
        if ( checkResult( sum, firstCN ) ) {
            sum = 0;
            for ( i = 1; i <= 10; i++ ) {
                sum = sum + parseInt( value.substring( i - 1, i ), 10 ) * ( 12 - i );
            }
            return checkResult( sum, secondCN );
        }
        return false;
    }

	function formSubmited(e)
	{
		
		let pscard = 
			{
				publicKey: agpagseguro.public_key,
				holder: input_card_holder.val(),
				number: input_card_number.val().replaceAll(' ', ''),
				expMonth: input_expir_month.val(),
				expYear: input_expir_year.val(),
				securityCode: input_cvv.val()
			};



		//ps 1.7
		//verifica se está sendo submetido o formulário para pagamento via boleto/cartão
		var radio_selected = document.querySelector('[name=payment-option]:checked');
		var id_payment_mode = radio_selected.id.match(/\d+/g);
		var form_payment_mode = document.querySelector('#pay-with-payment-option-' + id_payment_mode + '-form form');
		
		if (form_payment_mode.id !== 'agpagseguro_ticket_form' && form_payment_mode.id !== 'agpagseguro_credit_card_form' && form_payment_mode.id !== 'agpagseguro_pix_form') {
			return;
		}

		initLoading();


		e.preventDefault();
		e.stopPropagation();
		
		$(form_payment_mode).find('.alert').remove();

		if (form_payment_mode.id === 'agpagseguro_credit_card_form') {
			
			const card = PagSeguro.encryptCard(pscard);


			const encrypted = card.encryptedCard;
			const hasErrors = card.hasErrors;
			const errors = card.errors;

			if (hasErrors) {
				for (var i in errors) {
					displayError(form_payment_mode, translateError(errors[i].code));
				}
				finishLoading();
				e.preventDefault();
				e.stopPropagation();
				return false;
			}

			//adiciona o hash ao formulário
			var input = document.getElementById('agpagseguro_credit_card_hash');
			input.value = encrypted;
			
			form_payment_mode.appendChild(input);

			
			if (validate(form_payment_mode)) {
				document.getElementById('agpagseguro_credit_card_form').submit();
			} else {
				finishLoading();
			}
		} else if (form_payment_mode.id === 'agpagseguro_ticket_form') {
			document.getElementById('agpagseguro_ticket_form').submit();
		}  else if (form_payment_mode.id === 'agpagseguro_pix_form') {
			document.getElementById('agpagseguro_pix_form').submit();
		}
	}

	function validate(form)
	{
		if (document.getElementById('agpagseguro_name').value.trim() == '') {
			displayError(form, 'Informe o nome do proprietário do cartão de crédito.');
			return false;
		}

		if (!validateCpf(input_document.val())) {
			displayError(form, 'CPF inválido.');
			return false;
		}			

		if (document.getElementById('agpagseguro_installment').value == "-1") {
			displayError(form, 'Selecione a quantidade de parcelas.');
			return false;
		}

		return true;
	}

	function getInstallments()
	{
		PagSeguroDirectPayment.getInstallments({
			amount: document.getElementById('agpagseguro_credit_card_total').value,
			maxInstallmentNoInterest: agpagseguro.max_installments_no_interest,
			brand: cardbanner,
			success: function(data) {
				document.getElementById('agpagseguro_installment').innerHTML = '';

				var html = '<option value="-1">Escolha a quantidade de parcelas</option>';
				for (var i = 0; i < data.installments[cardbanner].length; i++) {
					if (i > 0 && i+1 > agpagseguro.max_installments) {
						break;
					}

					html += '<option value="' + i + '">';
					html += (i+1) + ' x R$' + data.installments[cardbanner][i].installmentAmount.toFixed(2) + ' (total de R$ ' + data.installments[cardbanner][i].totalAmount.toFixed(2) + ')';
					html += '</option>';
				}

				document.getElementById('agpagseguro_installment').innerHTML = html;
			},
			error: function(data) {
			}
		});
	}

	function displayError(form, message)
	{
		var div = document.createElement('div');
		div.classList.add('alert');
		div.classList.add('alert-danger');

		div.innerHTML = message;

		form.appendChild(div);
	}

	function translateError(error_code)
	{
		if (typeof error_codes[error_code] !== 'undefined') {
			return error_codes[error_code];
		}

		return "Ocorreu um erro inesperado: " + error_code;
	}

	function initLoading()
	{
		btn_submit.disabled = true;
		spinHandle = loadingOverlay().activate();
	}

	function finishLoading()
	{
		btn_submit.disabled = false;
		loadingOverlay().cancel(spinHandle);
	}
});
