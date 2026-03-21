<?php

class AgPagSeguroTicketModuleFrontController extends ModuleFrontController
{
    public $warnings      = [];
    public $confirmations = [];
    public $informations  = [];

    public function initContent()
    {
        if (!$this->module->active) {
            exit();
        }
        
        parent::initContent();
        $this->module->prepareNotifications();

        if (
            //pagamento por boleto desativado
            !Configuration::get('AGPAGSEGURO_TICKET_ENABLE')
            //carrinho de compras vazio
            || @count($this->context->cart->getProducts()) == 0
        ) {
            $link = 'index.php?controller=order&step=3';

            if (Tools::getIsSet('error')) {
                $link .= '&agpagseguro_error=1';
            }

            Tools::redirect($link);
        }

        
        $total = $this->context->cart->getOrderTotal();

        //verifica se o desconto do boleto já foi aplicado ao carrinho
        $has_discount = false;

        $rules = $this->context->cart->getCartRules();
        foreach ($rules as $rule) {
            if ($rule['description'] === 'Desconto PagSeguro') {
                $has_discount = true;
            }
        }

        if (!$has_discount) {
            $discount = (float) Configuration::get('AGPAGSEGURO_TICKET_DISCOUNT');
            $total_with_discount = $total * (100 - $discount) / 100;
        } else {
            $total_with_discount = $total;
        }
        
        $this->context->smarty->assign(array(
            'total' => $total,
            'total_with_discount' => $total_with_discount,
            'errors' => $this->errors
        ));

        $this->setTemplate('ticket.tpl');
    }
}
