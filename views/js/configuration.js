document.addEventListener('DOMContentLoaded', function()
{
    function getQtyInstallments()
    {
        let _return = Math.min(12, $('#agpagseguro_credit_card_installments_max').val());
        if (_return == 0) {
            _return = 12;
        }

        return _return;
    }

    function showOrHideInstallments()
    {
        if ($('[name=agpagseguro_credit_card_installment_method]:checked').val() != 'local') {
            for (var i=1; i<=12; i++) {
                $(`#agpagseguro_interest_${i}`).closest('.form-group').hide();
            }
        } else {
            for (var i=1; i<= getQtyInstallments(); i++) {
                $(`#agpagseguro_interest_${i}`).closest('.form-group').show();
            }

            for (var i=getQtyInstallments() + 1; i <= 12; i++) {
                $(`#agpagseguro_interest_${i}`).closest('.form-group').hide();
            }
        }
    }
    

    $('#agpagseguro_credit_card_installments_no_interest').prop('type', 'number').prop('max', 12).prop('min', '0');
    $('[name=agpagseguro_credit_card_installment_method]').change(showOrHideInstallments);
    $('#agpagseguro_credit_card_installments_max').on('input', showOrHideInstallments);

    showOrHideInstallments();
});