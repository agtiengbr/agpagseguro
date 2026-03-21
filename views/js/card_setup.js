document.addEventListener('DOMContentLoaded', function(){
    var card = new Card({
        form: '#agpagseguro_credit_card_form',
        container: '.card-wrapper',
    
        formSelectors: {
            numberInput: 'input#agpagseguro_cardnumber', 
            expiryInput: 'input#agpagseguro_month, input#agpagseguro_year', 
            cvcInput: 'input#agpagseguro_cvv', 
            nameInput: 'input#agpagseguro_name' 
        },
    
        formatting: true,
    
        messages: {
            validDate: 'valid\ndate',
            monthYear: 'mm/yyyy',
        },
        placeholders: {
            number: '•••• •••• •••• ••••',
            name: 'Nome Completo',
            expiry: '••/••••',
            cvc: 'CVV'
        },
    
        debug: false
    });

    // Define card types with their properties
    const cardTypes = [
        { type: "amex", pattern: /^3[47]/, format: /(\d{1,4})(\d{1,6})?(\d{1,5})?/, length: [15], cvcLength: [4], luhn: true },
        { type: "dankort", pattern: /^5019/, format: /(\d{1,4})/g, length: [16], cvcLength: [3], luhn: true },
        { type: "dinersclub", pattern: /^(36|38|30[0-5])/, format: /(\d{1,4})(\d{1,6})?(\d{1,4})?/, length: [14], cvcLength: [3], luhn: true },
        { type: "discover", pattern: /^(6011|65|64[4-9]|622)/, format: /(\d{1,4})/g, length: [16], cvcLength: [3], luhn: true },
        { type: "elo", pattern: /401178|401179|431274|438935|451416|457393|457631|457632|504175|627780|636297|636369|636368|506699|5067[0-6]\d|50677[0-8]|50900\d|5090[1-9]\d|509[1-9]\d{2}|65003[1-3]|65003[5-9]|65004\d|65005[0-1]|65040[5-9]|6504[1-3]\d|65048[5-9]|65049\d|6505[0-2]\d|65053[0-8]|65054[1-9]|6505[5-8]\d|65059[0-8]|65070\d|65071[0-8]|65072[0-7]|65090[1-9]|65091\d|650920|65165[2-9]|6516[6-7]\d|65500\d|65501\d|65502[1-9]|6550[3-4]\d|65505[0-8]|65092[1-9]|65097[0-8]/,
        format: /(\d{1,4})/g, length: [16], cvcLength: [3], luhn: true },
        { type: "hipercard", pattern: /^(384100|384140|384160|606282|637095|637568|60(?!11))/, format: /(\d{1,4})/g, length: [14, 15, 16, 17, 18, 19], cvcLength: [3], luhn: true },
        { type: "jcb", pattern: /^(308[8-9]|309[0-3]|3094[0]{4}|309[6-9]|310[0-2]|311[2-9]|3120|315[8-9]|333[7-9]|334[0-9]|35)/, format: /(\d{1,4})/g, length: [16, 19], cvcLength: [3], luhn: true },
        { type: "laser", pattern: /^(6706|6771|6709)/, format: /(\d{1,4})/g, length: [16, 17, 18, 19], cvcLength: [3], luhn: true },
        { type: "maestro", pattern: /^(50|5[6-9]|6007|6220|6304|6703|6708|6759|676[1-3])/, format: /(\d{1,4})/g, length: [12, 13, 14, 15, 16, 17, 18, 19], cvcLength: [3], luhn: true },
        { type: "mastercard", pattern: /^(5[1-5]|677189)|^(222[1-9]|2[3-6]\d{2}|27[0-1]\d|2720)/, format: /(\d{1,4})/g, length: [16], cvcLength: [3], luhn: true },
        { type: "mir", pattern: /^220[0-4][0-9][0-9]\d{10}$/, format: /(\d{1,4})/g, length: [16], cvcLength: [3], luhn: true },
        { type: "troy", pattern: /^9792/, format: /(\d{1,4})/g, length: [16], cvcLength: [3], luhn: true },
        { type: "unionpay", pattern: /^62/, format: /(\d{1,4})/g, length: [16, 17, 18, 19], cvcLength: [3], luhn: false },
        { type: "visaelectron", pattern: /^4(026|17500|405|508|844|91[37])/, format: /(\d{1,4})/g, length: [16], cvcLength: [3], luhn: true },
        { type: "visa", pattern: /^4/, format: /(\d{1,4})/g, length: [13, 16], cvcLength: [3], luhn: true },
    ];

    function findInstallmentPlans(obj) {
        var result = [];
        
        $.each(obj, function(key, value) {
          if (key === 'installment_plans') {
            result = result.concat(value);
          } else if (typeof value === 'object' && value !== null) {
            result = result.concat(findInstallmentPlans(value));
          }
        });
        
        return result;
    }

    if(agpagseguro.installments_method !== 'local') {
        const installmentsSelect = $('#agpagseguro_installment');
        installmentsSelect.empty();
        installmentsSelect.append('<option value="">Digite o número do seu cartão para visualizar as parcelas disponíveis.</option>');
    }

    // Centraliza o processamento do número do cartão em uma função
    function handleCardNumberInput() {
        const cardNumber = $('#agpagseguro_cardnumber').val();
        const cardElement = $('.jp-card');
        const orderTotalCents = 100 * $('#agpagseguro_credit_card_total').val();

        // Check if the installments should be calculated remotely
        // Also check if the card number has the first 6 digits
        // If it does, call the endpoint to get the installments
        if(agpagseguro.installments_method !== 'local' && cardNumber.replace(/\s+/g, '').length >= 6) {
            const installmentsSelect = $('#agpagseguro_installment');
            installmentsSelect.empty();

            // Show a loading option in the select box
            installmentsSelect.append('<option value="">Carregando parcelamento...</option>');
            installmentsSelect.attr('disabled', true);  // Disable the select while loading

            $.ajax({
                url: `${location.origin}/module/agpagseguro/simulateInstallments`,
                type: 'GET',
                data: {
                    cardbin: cardNumber.replace(/\s+/g, '').substring(0, 6),
                    value: orderTotalCents
                },
                success: function(response) {
                    const installmentPlans = findInstallmentPlans(JSON.parse(response));
                    if(installmentPlans.length) {
                        installmentsSelect.empty();
                        // Loop through the installment plans and add options to the select element
                        installmentPlans.forEach(function(installment) {
                            var total = (installment.amount.value / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            var installmentValue = (installment.installment_value / 100).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
                            var feeText = '';

                            if(installment.interest_free) {
                                feeText = ' sem juros';
                            } else {
                                feeText = ' com juros';
                            }

                            var optionText = `${installment.installments} x R$ ${installmentValue} (total de ${total}${feeText})`;
                            var optionValue = installment.installments;

                            installmentsSelect.append(`<option value="${optionValue}">${optionText}</option>`);
                        });
                        installmentsSelect.attr('disabled', false);
                    } else {
                        installmentsSelect.empty();  // Clear the loading option
                        installmentsSelect.append('<option value="">Número de cartão inválido.</option>');
                        installmentsSelect.attr('disabled', true);
                    }
                },
                error: function(error) {
                    installmentsSelect.empty();  // Clear the loading option
                    installmentsSelect.append('<option value="">Erro ao carregar parcelamento.</option>');
                    installmentsSelect.attr('disabled', true);
                }
            });            
        }

        // Remove previous classes related to card types
        cardTypes.forEach(card => cardElement.removeClass('jp-card-' + card.type));

        // Find matching card type
        cardTypes.forEach(card => {
            if (card.pattern.test(cardNumber) && !cardElement.hasClass('jp-card-' + card.type)) {
                cardElement.addClass('jp-card-' + card.type);
            }
        });
    }

    // Bind input, change and paste events. For paste we wait a tick so the value is available.
    $('#agpagseguro_cardnumber').on('input change paste', function (e) {
        if (e.type === 'paste') {
            // value will be available after the paste event completes
            setTimeout(handleCardNumberInput, 0);
        } else {
            handleCardNumberInput();
        }
    });

    // Autofill / autocomplete detectors
    // Some browsers (autocomplete) don't always fire input/change events when the user selects
    // an autofill suggestion. We detect value changes by short polling on page load and on focus.
    var _cardNumberInput = document.getElementById('agpagseguro_cardnumber');
    var _lastCardValue = _cardNumberInput ? _cardNumberInput.value : '';
    var _pollHandle = null;

    function startDetectingChanges(timeoutMs = 2000, intervalMs = 200) {
        if (!_cardNumberInput) return;
        // avoid multiple concurrent polls
        if (_pollHandle) return;
        var start = Date.now();
        _pollHandle = setInterval(function() {
            var current = _cardNumberInput.value;
            if (current !== _lastCardValue) {
                _lastCardValue = current;
                handleCardNumberInput();
                // keep watching a little longer in case browser fills additional fields
            }
            if (Date.now() - start > timeoutMs) {
                clearInterval(_pollHandle);
                _pollHandle = null;
            }
        }, intervalMs);
    }

    // Start a short poll on load to catch autofill that happens after page render
    startDetectingChanges(2500, 200);

    // Also poll while the field is focused (user selecting an autocomplete suggestion)
    if (_cardNumberInput) {
        _cardNumberInput.addEventListener('focus', function() { startDetectingChanges(3000, 150); });
        _cardNumberInput.addEventListener('blur', function() { if (_pollHandle) { clearInterval(_pollHandle); _pollHandle = null; } });
    }


});
