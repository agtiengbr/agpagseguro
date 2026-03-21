<?php

use AGTI\PagSeguro\Application\Exception\HttpCodeException;
use AGTI\PagSeguro\Application\Exception\PaymentDeclinedException;
use AGTI\PagSeguro\Application\Service\CancelCharge;
use AGTI\PagSeguro\Application\Service\CreateCreditCardOrder;
use AGTI\PagSeguro\Application\Service\CreatePixOrder;
use AGTI\PagSeguro\Application\Service\CreateTicketOrder;
use AGTI\PagSeguro\Application\Service\GenerateRemindersForTransaction;
use AGTI\PagSeguro\Entity\AgpagseguroTransaction as EttAgPagseguroTransaction;
use AGTI\PagSeguro\Entity\Orders;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CreateOrderResponseError;
use AGTI\PagSeguro\Infrastructure\Service\Mail\PixMail;
use AGTI\PagSeguro\Infrastructure\Service\Mail\TicketMail;

class AgPagSeguroValidationModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if (!$this->module->active) {
            exit();
        }

        $semId = ftok(__FILE__, "s");
        $sem = sem_get($semId, 1);
        sem_acquire($sem);

        $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');

        if ($sandbox) {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
        } else {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
        }


        /*---------------------- descontos ------------------------*/
        try {
            $webhookNotificationUrl = $this->module->getWebhookNotificationUrl();

            if (Tools::getValue('payment_mode') === 'ticket') {
                $payment_way = 'Pagseguro - Boleto Bancário';

                $ticket_discount = (float) Configuration::get('AGPAGSEGURO_TICKET_DISCOUNT');
                if ($ticket_discount > 0) {
                    $this->createDiscount($ticket_discount);
                } else {
                    $this->removeDiscount();
                }

                /** @var CreateTicketOrder */
                $s = $this->get(CreateTicketOrder::class);

                /** @var EttAgPagseguroTransaction */
                $r = $s->exec($this->context->cart, $webhookNotificationUrl);


            } elseif (Tools::getValue('payment_mode') === 'credit_card') {
                $payment_way = 'Pagseguro - Cartão de Crédito';

                if (!$this->module->canShowCreditCardOption()) {
                    $this->context->controller->errors[] = 'O cartão de crédito só é exibido para clientes com pelo menos uma venda válida.';
                    $this->redirectWithNotifications($this->context->link->getPageLink('order'));
                    exit();
                }

                $this->removeDiscount();

                /** @var CreateCreditCardOrder */
                $s = $this->get(CreateCreditCardOrder::class);

                $quantity = Tools::getValue('agpagseguro_installment') + 1;

                /** @var EttAgPagseguroTransaction */
                $r = $s->exec(
                    $this->context->cart,
                    $quantity,
                    Tools::getValue('agpagseguro_credit_card_hash'),
                    Tools::getValue('agpagseguro_name'),
                    Tools::getValue('agpagseguro_document'),
                    $webhookNotificationUrl
                );

            } elseif (Tools::getValue('payment_mode') === 'pix') {
            /** @var CreatePixOrder */
                $payment_way = 'Pagseguro - PIX';

                $pix_discount = (float) Configuration::get('AGPAGSEGURO_PIX_DISCOUNT');
                if ($pix_discount > 0) {
                    $this->createDiscount($pix_discount);
                } else {
                    $this->removeDiscount();
                }

                $s = $this->get(CreatePixOrder::class);
                $r = $s->exec($this->context->cart, $webhookNotificationUrl);
            } else {
                throw new Exception('Método de pagamento desconhecido.');
            }

            if ($r instanceof CreateOrderResponseError) {
                if (!Configuration::get('AGPAGSEGURO_STATUS_PAGSEGURO_ERROR_FINISH_ORDER')) {
                    foreach ($r->getErrors() as $error) {
                        $this->context->controller->errors[] = "O seguinte erro foi retornado pelo PagSeguro: {$error->getError()}";
                    }
                    $this->redirectWithNotifications($this->context->link->getPageLink('order'));
                    exit();
                } else { 
                    $status = 'PAGSEGURO_ERROR';
                }
            } else {
                $status = $r->getTransactionStatus();
            }
        } catch (PaymentDeclinedException $e) {
            $status = "DECLINED";
            $r = $e->getTransaction();

            if (!Configuration::get("AGPAGSEGURO_STATUS_{$status}_FINISH_ORDER")) {
                $this->context->controller->errors[] = "O pagamento foi recusado. Erro: {$e->getMessage()}.";
                $this->redirectWithNotifications($this->context->link->getPageLink('order'));
                exit();    
            }
        }

        if (!Configuration::get("AGPAGSEGURO_STATUS_{$status}_FINISH_ORDER")) {
            $this->context->controller->errors[] = "Ocorreu um erro ao processar a sua transação. Por favor, verifique os dados informados.";
            $this->redirectWithNotifications($this->context->link->getPageLink('order'));
            exit();
        }

        $psStatus = Configuration::get('AGPAGSEGURO_STATUS_' . $status);

        try {
            if (!$this->context->cart->isAllProductsInStock()) {
                throw new Exception("Um ou mais produtos está fora de estoque.");
            }

            $this->module->validateOrder($this->context->cart->id, $psStatus, $this->context->cart->getOrderTotal(), $payment_way, NULL, null, (int)$this->context->currency->id, false, $this->context->customer->secure_key);
        } catch (Exception $e) {
            //estorna a transação ao cliente se o pagamento tiver sido aprovado
            if (isset($r) && $r->getTransactionCode()) {
                try {
                    /** @var CanceCharge */
                    $s = $this->get(CancelCharge::class);
                    $s->exec($r->getTransactionCode());
                } catch (\Exception $e) {
                    \Logger::addLog('agpagseguro - Erro ao cancelar a transação ' . $r->getTransactionCode() . ' - ' . $e->getMessage(), 4, null, null, null, true);
                }
            }
            $this->context->controller->errors[] = "Ocorreu um erro ao processar o seu pedido: {$e->getMessage()}";
            $this->context->controller->redirectWithNotifications($this->context->link->getPageLink("order", null, null, ['step' => 3]));
        }

        
        $ps_order = new Order(Order::getOrderByCartId($this->context->cart->id));

        if ($r instanceof EttAgPagseguroTransaction) {
            $ettOrder = $this->get('doctrine.orm.entity_manager')->getRepository(Orders::class)->findOneBy(['id' => $ps_order->id]);
            $r->setOrder($ettOrder);
            $this->get('doctrine.orm.entity_manager')->flush();


            if ($r->getType() == '3') {
                $mail = new PixMail;
                $mail->sendPixMail($r);
            } elseif ($r->getType() == '0') {
                $mail = new TicketMail;
                $mail->sendTicketMail($r);
            }
        }

        Tools::redirect('index.php?controller=order-confirmation&id_cart='.$this->context->cart->id.'&id_module='.$this->module->id.'&id_order='.$ps_order->id.'&key='.$this->context->customer->secure_key);



        // try {
        //     if (empty($customer_data['document_number'])) {
        //         throw new Exception('CPF/CNPJ não informados.');
        //     }

        //     if (empty($address_number)) {
        //         throw new Exception('Número do endereço de entrega não informado.');
        //     }

        //     if (empty($address_delivery->address2)) {
        //         throw new Exception('Bairro do endereço de entrega não informado.');
        //     }
            
        //     /**
        //      * #### Credentials #####
        //      * Replace the parameters below with your credentials
        //      * You can also get your credentials from a config file. See an example:
        //      * $credentials = PagSeguroConfig::getAccountCredentials();
        //      */
           
        //     $return = $directPaymentRequest->register($credentials);
        //     $status = $return->getStatus()->getValue();
        //     if ($status > 5) {
        //         throw new Exception('O pagamento não foi aprovado.');
        //     }

        //     $this->module->validateOrder($cart->id, Configuration::get('AGPAGSEGURO_STATUS_' . $status), $cart->getOrderTotal(), $payment_way, NULL, null, (int)$this->context->currency->id, false, $this->context->customer->secure_key);

        //     $ps_order = new Order(Order::getOrderByCartId($cart->id));


        //     //salva a transação no bd
        //     $transaction = new AgPagseguroTransaction();
        //     $transaction->id_order = $ps_order->id;
        //     $transaction->transaction_code = $return->getCode();
        //     $transaction->transaction_data = serialize($return);

        //     switch (Tools::getValue('payment_mode')) {
        //         case 'ticket':
        //             $transaction->type = 0;
        //             break;
        //         case 'credit_card':
        //             $transaction->type = 1;
        //             break;
        //         case 'eft':
        //             $transaction->type = 2;
        //             break;
        //         default:
        //             $transaction->type = -1;
        //             break;
        //     }

        //     $transaction->qty_installments = $return->getInstallmentCount();
        //     $transaction->transaction_reference = $return->getReference();
        //     $transaction->transaction_total = $return->getGrossAmount();
        //     $transaction->intermediation_cost = $return->getGrossAmount() - $return->getNetAmount();
        //     $transaction->transaction_link = $return->getPaymentLink();

        //     if (!$transaction->add()) {
        //         Logger::addLog('agpagseguro - Ocorreu um erro ao salvar a transação ' . $transaction->transaction_code . ' no banco de dados - ' . Db::getInstance()->getMsgError(), 3, '0001');
        //     }

        //     //gera os lembretes
        //     $em = $this->get('doctrine.orm.entity_manager');
        //     $transaction = $em->getRepository(EttAgPagseguroTransaction::class)->findOneBy(['id' => $transaction->id]);

        //     $service = $this->get(GenerateRemindersForTransaction::class);
        //     $service->exec($transaction);

        //     if (Tools::getValue('payment_mode') === 'ticket') {

        //         $data = array(
        //             '{print_ticket_link}' => $return->getPaymentLink(),
        //             '{order_name}' => $ps_order->reference,
        //             '{order_id}' => $ps_order->id,
        //             '{order_date}'=> Tools::displayDate($ps_order->date_add),
        //             '{order_price}' => Tools::displayPrice($ps_order->total_paid),
        //             '{shop_name}' => $this->context->shop->name,
        //             '{customer_name}' => $customer_data['name']
        //         );

                
        //         Mail::Send(
        //             (int)$ps_order->id_lang,
        //             'ticket',
        //             'Conclua o pagamento de seu pedido',
        //             $data,
        //             $this->context->customer->email,
        //             $this->context->customer->firstname . ' ' . $this->context->customer->lastname,
        //             null,
        //             null,
        //             null,
        //             null,
        //             _PS_MODULE_DIR_ . $this->module->name . '/mails/'
        //         );
        //     }

        //     $this->module->saveNotifications();
        //     sem_release($sem);
        //     Tools::redirect('index.php?controller=order-confirmation&id_cart='.$cart->id.'&id_module='.$this->module->id.'&id_order='.$ps_order->id.'&key='.$this->context->customer->secure_key);

        // } catch (Exception $e) {
        //     sem_release($sem);
        //     Logger::addLog('agpagseguro - Erro processando o pagamento do cliente ' . $this->context->customer->email . ' - ' . $e->getMessage(), 4, null, null, null, true);

        //     if ($this->module->ps17) {
        //         $link = $this->context->link->getPageLink('order', true, null, 'step=3');

        //         $this->errors[] = 'Ocorreu um erro ao processar o seu pagamento. Se achar necessário, entre em contato com nossa equipe de atendimento ao cliente.';
        //         $this->redirectWithNotifications($link);
        //     } else {
        //         if (Tools::getValue('payment_mode') === 'ticket') {
        //             $link = $this->context->link->getModuleLink('agpagseguro', 'ticket');
        //         } else {
        //             $link = $this->context->link->getModuleLink('agpagseguro', 'creditCard');
        //         }

        //         $this->module->errors[] = 'Ocorreu um erro ao processar o seu pagamento. Se achar necessário, entre em contato com nossa equipe de atendimento ao cliente.';
        //         $this->module->saveNotifications();

        //         Tools::redirect($link);
        //     }
        // }
        // sem_release($sem);

    }


    protected function createDiscount($discount)
    {
        $rules = $this->context->cart->getCartRules();

        foreach ($rules as $rule) {
            if ($this->isPagseguroDiscountRule($rule)) {
                $cart_rule = new CartRule($rule['id_cart_rule']);
                $cart_rule->reduction_percent = $discount;
                $cart_rule->date_to = date('Y-m-d H:i:s', strtotime("+2 days", strtotime(date('Y-m-d'))));
                $cart_rule->update();
                $this->context->cart->save();
                return;
            }
        }

        $cart_rule = new CartRule();

        foreach (Language::getLanguages() as $lang) {
            $cart_rule->name[$lang['id_lang']] = 'Desconto PagSeguro';
        }

        $cart_rule->id_customer = $this->context->cart->id_customer;
        $cart_rule->date_from = date('Y-m-d H:i:s');
        $cart_rule->date_to = date('Y-m-d H:i:s', strtotime("+2 days",strtotime(date('Y-m-d'))));
        $cart_rule->description = 'discount_agpagseguro';
        $cart_rule->quantity = 1;
        $cart_rule->quantity_per_user = 1;
        $cart_rule->priority = 1;
        $cart_rule->partial_use = 1;
        $cart_rule->code = md5('discount_agpagseguro' .$this->context->cart->id_customer . date('Y-m-d H:i:s'));

        $cart_rule->minimum_amount = 0;
        $cart_rule->minimum_amount_tax = 0;
        $cart_rule->minimum_amount_currency = 1;
        $cart_rule->minimum_amount_shipping = 0;
        $cart_rule->country_restriction = 0;
        $cart_rule->carrier_restriction = 0;
        $cart_rule->group_restriction = 0;
        $cart_rule->cart_rule_restriction = 0;
        $cart_rule->product_restriction = 0;
        $cart_rule->shop_restriction = 0;
        $cart_rule->free_shipping = 0;

        $cart_rule->reduction_percent = $discount;

        $cart_rule->reduction_tax = 1;
        $cart_rule->reduction_currency = $this->context->currency->id;
        $cart_rule->reduction_product = 0;

        $cart_rule->gift_product = 0;
        $cart_rule->gift_product_attribute = 0;
        $cart_rule->highlight = 0;
        $cart_rule->active = 1;

        $cart_rule->add();
        $this->context->cart->addCartRule($cart_rule->id);

        $this->context->cart->save();
    }

    protected function removeTicketsDiscount()
    {
        $rules = $this->context->cart->getCartRules();

        foreach ($rules as $rule) {
            if ($rule['description'] === 'Desconto boleto') {
                $this->context->cart->removeCartRule($rule['id_cart_rule']);
            }
        }
    }

    protected function removeDiscount()
    {
        $rules = $this->context->cart->getCartRules();

        foreach ($rules as $rule) {
            if ($this->isPagseguroDiscountRule($rule)) {
                $this->context->cart->removeCartRule($rule['id_cart_rule']);
            }
        }
    }

    protected function isPagseguroDiscountRule($rule)
    {
        return isset($rule['description']) && in_array($rule['description'], ['Desconto PagSeguro', 'discount_agpagseguro']);
    }

}
