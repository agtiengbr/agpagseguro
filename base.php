<?php

use AGTI\PagSeguro\Application\Service\GenerateRemindersForTransaction;
use AGTI\PagSeguro\Application\Service\GetCardKey;
use AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey\CreateCardKey;
use AGTI\PagSeguro\Application\Service\UpdateChargeFromApi;
use AGTI\PagSeguro\Application\Service\UpdateTransactionFromOrderId;
use AGTI\PagSeguro\Entity\AgpagseguroTicketReminder;
use AGTI\PagSeguro\Entity\AgpagseguroTransaction as EttAgPagseguroTransaction;
use AGTI\PagSeguro\Entity\Orders;
use PrestaShop\PrestaShop\Core\Payment\PaymentOption;
use AGTI\PagSeguro\Enum\AgPagSeguroTransactionStatuses;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrder;
use Doctrine\ORM\EntityManagerInterface;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

require_once _PS_MODULE_DIR_ . 'agpagseguro/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . 'agcliente/lib/AgPaymentModule.php';

class BaseAgPagSeguro extends AgPaymentModule
{
    protected $hooks = [
        'displayHeader',
        'displayBackOfficeHeader',
        'paymentOptions',
        'displayPaymentTop',
        'payment',
        'orderConfirmation',
        'displayOrderDetail',
        'displayAdminOrderContentOrder',
        'DisplayAdminOrderTabContent',
        'displayAdminOrderTabOrder',
        'displayAdminOrderTabLink',
        'actionOrderStatusUpdate'
    ];

	//menus do administrativo
    protected $main_tab = 'AdminParentPayment';
    protected $main_tab_ps16 = 'AdminParentPayment';


    protected $tabs = array(
        array(
            "name"      => "PagSeguro",
            "className" => "AdminAgPagSeguroConfig",
            "active"    => 1
        ),
        array(
            "name"      => "vazio",
            "className" => "AdminAgPagSeguroServices",
            "active"    => 0,
            "childs"    => array(
                array(
                    "name"      => "PagSeguro Transações",
                    "className" => "AdminAgPagSeguroTransaction",
                    "active"    => 1
                ),
                array(
                    "name"      => "PagSeguro WebHook",
                    "className" => "AdminAgPagSeguroWebhook",
                    "active"    => 1
                ),
                array(
                    "name"      => "PagSeguro Requisições",
                    "className" => "AdminAgPagSeguroRequest",
                    "active"    => 1
                )
            )
        )
    );

    protected $workers = [
        [
            'name' => 'updatetransactionstatus',
            'controller' => 'updatetransactionstatus',
            'delay' => 3600
        ],
        [
            'name' => 'sendReminders',
            'controller' => 'sendReminders',
            'delay' => 3600
        ],
        [
            'name' => 'checkMissingTransactions',
            'controller' => 'checkMissingTransactions',
            'delay' => 3600*24
        ],
        [
            'name' => 'downloadAccountData',
            'controller' => 'downloadAccountData',
            'delay' => 3600*24
        ]
    ];

	public function __construct()
	{
		$this->name     = 'agpagseguro';
        $this->tab      = 'payments_gateways';
        $this->version  = '2.2.5';
        $this->author   = 'AGTI';
        // $this->controllers = array('payment', 'validation');

        $this->bootstrap = true;
        
        parent::__construct();

        $this->displayName = 'PagSeguro Transparente';
        $this->description = 'Integra a sua loja PrestaShop com o PagSeguro.';

        $this->loadMappings();
	}

    public function install(){
        Configuration::updateValue('AGPAGSEGURO_TICKET_TEXT_CHECKOUT','Pague via Boleto Bancário');
        Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_TEXT_CHECKOUT','Pague via Cartão de Crédito');
        Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_REQUIRE_VALID_ORDER', 0);
        Configuration::updateValue('AGPAGSEGURO_EFT_TEXT_CHECKOUT','Pague via Débito em Conta');
		Configuration::updateValue('AGPAGSEGURO_WEBHOOK_PROCESS_IMMEDIATE', 0);
        Configuration::updateValue('AGPAGSEGURO_SHOW_MISSING_TRANSACTION_WARNING', 1);
        Configuration::updateValue('AGPAGSEGURO_STATUS_NOT_FOUND', -1);
        Configuration::updateValue('AGPAGSEGURO_STATUS_NOT_FOUND_FINISH_ORDER', 0);
        Configuration::updateValue('AGPAGSEGURO_WEBHOOK_NOTIFICATION_URL', $this->context->link->getModuleLink($this->name, 'webhook'));

        return parent::install() && $this->createMissingTransactionIgnoreStorage();
    }

    public function getWebhookNotificationUrl()
    {
        $configuredWebhookUrl = trim((string) Configuration::get('AGPAGSEGURO_WEBHOOK_NOTIFICATION_URL'));

        if (!empty($configuredWebhookUrl)) {
            return $configuredWebhookUrl;
        }

        return $this->context->link->getModuleLink($this->name, 'webhook');
    }

    protected function createMissingTransactionIgnoreStorage()
    {
        if (!class_exists('AgPagseguroMissingTransactionIgnore', false)) {
            require_once _PS_MODULE_DIR_ . $this->name . '/classes/AgPagseguroMissingTransactionIgnore.php';
        }

        $model = new AgPagseguroMissingTransactionIgnore();

        if (method_exists($model, 'createDatabase') && !$model->createDatabase()) {
            return false;
        }

        if (method_exists($model, 'createMissingColumns') && !$model->createMissingColumns()) {
            return false;
        }

        if (method_exists($model, 'createIndexes') && !$model->createIndexes()) {
            return false;
        }

        return true;
    }

    protected function getIgnoredMissingTransactionOrderIds()
    {
        $ignored = AgPagseguroMissingTransactionIgnore::getAll();
        if (!is_array($ignored)) {
            return [];
        }

        return array_values(array_unique(array_map(function ($item) {
            return (int) $item->id_order;
        }, $ignored)));
    }

    protected function ignoreMissingTransactionOrder($orderId)
    {
        $orderId = (int) $orderId;
        if ($orderId <= 0) {
            return;
        }

        if (AgPagseguroMissingTransactionIgnore::getByOrderId($orderId)) {
            return;
        }

        $ignore = new AgPagseguroMissingTransactionIgnore();
        $ignore->id_order = $orderId;
        $ignore->date_add = date('Y-m-d H:i:s');
        $ignore->add();
    }

	public function getContent()
	{
        $this->context->controller->addJs($this->_path . 'views/js/configuration.js');
        $this->context->controller->addCss($this->_path . 'views/css/configuration.css');

		return $this->renderConfigForm();
	}

    public function getCpfMapping()
    {
        return $this->cpf_mapping;
    }

    public function getCnpjMapping()
    {
        return $this->cnpj_mapping;
    }

    public function getSocialNameMapping()
    {
        return $this->social_name_mapping;
    }

    public function getAddressNumberMapping()
    {
        return $this->address_number_mapping;
    }

    public function getCustomerData(Customer $customer)
    {
        $cpf_mapping         = $this->getCpfMapping();
        $cnpj_mapping        = $this->getCnpjMapping();
        $social_name_mapping = $this->getSocialNameMapping();

        if ($cnpj_mapping->isMappingEnabled() && $social_name_mapping->isMappingEnabled()) {
            if ($cnpj_mapping->getMappedField() !== 'djtalbrazilianregister') {
                $cnpj    = $customer->{$cnpj_mapping->getMappedField()};
                $company = $customer->{$social_name_mapping->getMappedField()};
            } else {
                $sql = new DbQuery;
                $sql->from('djtalbrazilianregister')
                    ->where('id_customer=' . (int)$customer->id);

                $data = Db::getInstance()->getRow($sql);

                $cnpj    = @$data['cnpj'];
                $company = $customer->{$social_name_mapping->getMappedField()};
            }
        }

        if ($cpf_mapping->getMappedField() !== 'djtalbrazilianregister') {
            $cpf = @$customer->{$cpf_mapping->getMappedField()};
            $name = $customer->firstname . ' ' . $customer->lastname;
        } else {
            $sql = new DbQuery;
            $sql->from('djtalbrazilianregister')
                ->where('id_customer=' . (int)$customer->id);

            $data = Db::getInstance()->getRow($sql);
            
            $cpf = @$data['cpf'];
            $name = $customer->firstname . ' ' . $customer->lastname;
        }

        if (@$cnpj && @$company) {
            return [
                'document_type'   => 'CNPJ',
                'document_number' => $cnpj,
                'name' => $company
            ];
        }

        return [
            'document_type'   => 'CPF',
            'document_number' => $cpf,
            'name' => $name
        ];
    }


    /*************** HOOKS *********************/
    /*************** HOOKS - BACKOFFICE *********************/
    public function hookDisplayBackOfficeHeader()
    {
        $this->context->controller->addJs([
            _PS_MODULE_DIR_ . $this->name . '/views/js/admin_orders.js'
        ]);

        if ($this->context->controller->controller_name == 'AdminOrders' && !$this->context->controller->ajax) {
            if (Tools::getValue('agpagseguro_ignore_missing_transaction')) {
                $this->ignoreMissingTransactionOrder(Tools::getValue('agpagseguro_ignore_missing_transaction'));

                $redirectParams = [];
                if (Tools::getValue('id_order')) {
                    $redirectParams['id_order'] = (int) Tools::getValue('id_order');
                }
                if (Tools::getValue('vieworder') !== false) {
                    $redirectParams['vieworder'] = true;
                }

                Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders', true, [], $redirectParams));
            }

            if (Tools::isSubmit('agpagseguro-transaction-save')) {
                $service = $this->get(UpdateTransactionFromOrderId::class);

                try {
                    /** @var EttAgPagseguroTransaction */
                    $transaction = $service->exec(Tools::getValue('agpagseguro_pagseguro_order_id'));

                    /** @var EntityManagerInterface */
                    $em = $this->get('doctrine.orm.entity_manager');

                    $order = $em->getRepository(Orders::class)->findOneBy(['id' => Tools::getValue('agpagseguro_id_order')]);
                    $transaction->setOrder($order);
                    $em->flush();
                } catch (Exception $e) {
                    $this->context->controller->errors[] = $e->getMessage();
                }
            }

            if (!Configuration::get('AGPAGSEGURO_SHOW_MISSING_TRANSACTION_WARNING')) {
                return;
            }

            $ignoredOrderIds = $this->getIgnoredMissingTransactionOrderIds();
            $orders = array_values(array_filter(AgPagseguroTransaction::findOrdersWithoutTransaction(), function ($order) use ($ignoredOrderIds) {
                return !in_array((int) $order->id, $ignoredOrderIds, true);
            }));
            $error_msg = "";
            if (count($orders)) {
                $error_msg .= "Os seguintes pedidos do PrestaShop estão com erros de integração com o PagSeguro. Clique sobre o ID dos pedidos para corrigir. <ul>";

                foreach ($orders as $order) {
                    if (version_compare(_PS_VERSION_, '1.7', '>=')) {
                        $link = $this->context->link->getAdminLink('AdminOrders', true, [], ['id_order' => $order->id, 'vieworder' => true]);
                    } else{
                        $link = $this->context->link->getAdminLink('AdminOrders', true) . "&id_order={$order->id}&vieworder";
                    }

                    $ignoreLinkParams = ['agpagseguro_ignore_missing_transaction' => $order->id];
                    if (Tools::getValue('id_order')) {
                        $ignoreLinkParams['id_order'] = (int) Tools::getValue('id_order');
                    }
                    if (Tools::getValue('vieworder') !== false) {
                        $ignoreLinkParams['vieworder'] = true;
                    }

                    $ignoreLink = $this->context->link->getAdminLink('AdminOrders', true, [], $ignoreLinkParams);

                    $error_msg .= "<li><a href='{$link}'>{$order->reference}</a> <a href='{$ignoreLink}'>ignorar</a></li>";
                }

                $this->context->controller->errors[] = $error_msg;
            }
        }
    }

    public function hookDisplayAdminOrderTabOrder($params)
    {
        if (version_compare(_PS_VERSION_, '1.7.7', '>=')) {
            return false;
        }

        /** @var Order */
        $order = $params['order'];
        if ($order->module !== 'agpagseguro') {
            return;
        }

        return $this->display(_PS_MODULE_DIR_ . $this->name, 'admin_tab_order.tpl');
    }

    public function hookDisplayAdminOrderTabLink()
    {
        return '
            <li class="nav-item">
                <a class="nav-link" id="agpagseguro" data-toggle="tab" href="#agpagseguroContent" role="tab" aria-controls="agpagseguroContent" aria-expanded="true" aria-selected="true">
                    <i class="material-icons">money</i>
                    PagSeguro
                </a>
            </li>';
    }


    public function hookDisplayAdminOrderContentOrder($params)
    {
        if (!$this->active) {
            return;
        }

        $order = $params['order'];
        /** @var AgPagseguroTransaction */
        $transaction = AgPagseguroTransaction::getByOrderId($order->id);

        // seller authentication
        $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');
        if ($sandbox) {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
        } else {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
        }

        $return = '';

        /* botão de imprimir boleto */
        print_ticket:
        if ($order->hasBeenPaid()) {
            goto transaction_data;
        }

        if (Validate::isLoadedObject($transaction)) {
            try {
                $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);
                $pagseguro_transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction->transaction_code);

                $print_ticket_link = $pagseguro_transaction->getPaymentLink();
                $this->context->smarty->assign([
                    'print_ticket_link' => $print_ticket_link,
                ]);
    
                $return .= $this->display(_PS_MODULE_DIR_ . $this->name, 'admin_order_detail.tpl');
                goto transaction_data;
            } catch (Exception $e) {
                goto transaction_data;
            }
        } else {
            try {
                $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);
                $session = PagSeguroSessionService::getSession($credentials);

                $this->context->smarty->assign([
                    'agpagseguro_session' => $session,
                    'agpagseguro_transaction_token' => Tools::getAdminTokenLite('AdminAgPagSeguroTransaction')
                ]);

                $return .= $this->display(_PS_MODULE_DIR_ . $this->name, 'admin_order_detail.tpl');
                goto transaction_data;
            } catch (Exception $e) {
                goto transaction_data;
            }
        }

        /* dados da transação */
        transaction_data:
        if (Validate::isLoadedObject($transaction)) {
            try {
                $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);
                if (!isset($pagseguro_transaction)) {
                    $pagseguro_transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction->transaction_code);
                }

                $this->context->smarty->assign([
                    'pagseguro_transaction' => $pagseguro_transaction,
                    'transaction' => $transaction,
                    'transaction_status_text' => AgPagSeguroTransactionStatuses::get($pagseguro_transaction->getStatus()->getValue()),
                    'pagseguro_tax' => $pagseguro_transaction->getGrossAmount() - $pagseguro_transaction->getNetAmount()
                ]);
            } catch (Exception $e) {
            }
        }

        $return .= $this->display(_PS_MODULE_DIR_ . $this->name, 'admin_order_transaction_data.tpl');

        return $return;
    }

    public function hookDisplayAdminOrderTabContent($params)
    {
        $order = new \Order($params['id_order']);
        if (!$this->active) {
            return;
        }


    $em = $this->get('doctrine.orm.entity_manager');
    $ettOrder = $em->getRepository(Orders::class)->findOneBy(['id' => $order->id]);
    $transaction = $em->getRepository(EttAgPagseguroTransaction::class)->findOneBy(['order' => $ettOrder]);
        
        if ($transaction) {
            /** @var GetOrder */
            $s = $this->get(GetOrder::class);
            $apiOrder = $s->exec((new Order)->setId($transaction->getPagseguroOrderId()));
            try {
                $this->context->smarty->assign([
                    'transaction' => $transaction,
                    'apiOrder' => $apiOrder
                    // 'pagseguro_tax' => $pagseguro_transaction->getGrossAmount() - $pagseguro_transaction->getNetAmount()
                ]);
            } catch (Exception $e) {
            }
        }

        $this->context->smarty->assign(['id_order' => $order->id]);
        $return .= $this->display(_PS_MODULE_DIR_ . $this->name, 'admin_order_transaction_data.ps17.tpl');

        return $return;
    }

    /*************** HOOKS - FRONTOFFICE *********************/

    public function hookDisplayHeader()
    {
        if (!$this->active) {
            return;
        }

        if (!$this->context->controller instanceof OrderController) {
            return;
        }

        if ($this->context->controller->php_self === 'history') {
            $this->context->controller->addCss($this->_path . '/views/css/order_detail.css');
        }

        if ($this->ps17) {
            $this->context->controller->addCSS($this->_path . '/views/css/front.ps17.css');
        } else {
            $this->context->controller->addCSS($this->_path . '/views/css/front.css');
        }

        $this->context->controller->addJs($this->_path . 'views/js/loadingOverlay.js');
        $this->context->controller->addJs([
            $this->_path . 'views/js/mask.min.js',
            $this->_path . 'views/js/payment.js'
        ]);

        $this->context->controller->addCss($this->_path . 'views/css/card.css');
        $this->context->controller->addJs([
            $this->_path . 'views/js/card.js',
            $this->_path . 'views/js/card_setup.js'
        ]);

        $this->context->controller->registerJavascript(
            'pagseguro-sdk',
            'https://assets.pagseguro.com.br/checkout-sdk-js/rc/dist/browser/pagseguro.min.js',
            [
                'server' => 'remote'
            ]
        );



        if (
            ($this->ps17 && $this->context->shop->theme->getName() === 'warehouse')
            || ($this->ps16 && $this->context->shop->theme_name === 'warehouse')
        ) {
            $this->context->controller->addCss($this->_path . 'views/css/warehouse.css');
        }

            
        if ($this->context->controller instanceof OrderController) {
            try {
                $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');
                if ($sandbox) {
                    $token = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
                } else {
                    $token = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
                }

                $s = $this->get(GetCardKey::class);
                try {
                    $r = $s->exec($token);
                } catch (\RuntimeException $e) {
                    \Logger::addLog('PagSeguro public key GET failed; attempting to create a new key. Error: ' . $e->getMessage(), 2);
                    // If GET failed (for example 404), attempt to create the card public key
                    try {
                        $creator = $this->get(CreateCardKey::class);
                        $r = $creator->exec();
                        \Logger::addLog('PagSeguro public key created after GET failure.', 1);
                    } catch (\Exception $e2) {
                        // both attempts failed; rethrow to outer catch
                        throw $e2;
                    }
                }

                Media::addJsDef(['agpagseguro_public_key' => $r->getPublicKey()]);
                $jsvars = [
                    'public_key' => $r->getPublicKey()
                ];
            } catch (Exception $e) {
                $jsvars = [];
            }


            $jsvars += [
                'max_installments_no_interest' => Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_NO_INTEREST'),
                'max_installments' => Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_MAX'),
                'installments_method' => Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENT_METHOD'),
            ];

            
            Media::addJsDef(array(
                'agpagseguro_base_uri' => $this->context->shop->getBaseURL(),
                'agpagseguro' => $jsvars
            ));
        }

    }

    public function validatePayment(Cart $cart, Customer $customer)
    {
        $address_delivery = new Address($cart->id_address_delivery);

        $mapping_number = $this->getAddressNumberMapping();
        $address_number = 's/n';
        if ($mapping_number->isMappingEnabled()) {
            $address_number = @$address_delivery->{$mapping_number->getMappedField()};
        }

        $errors = [];
        if (empty($customer->birthday) || $customer->birthday == '0000-00-00' || $customer->birthday == '0000/00/00') {
            $errors[] = 'Data de nascimento do comprador não informada.';
        }

        if (empty($address_number)) {
            $errors[] = 'Número da residência do endereço de entrega não informado.';
        }

        if (empty($address_delivery->address2)) {
            $errors[] = 'Bairro do endereço de entrega não informado.';
        }

        $customer_data = $this->getCustomerData($customer);
        if (empty($customer_data['document_number'])) {
            $errors[] = 'CPF/CNPJ do comprador não informados.';
        }

        $invoice_address = new Address($cart->id_address_invoice);
        $phone_number = $invoice_address->phone_mobile ?: $invoice_address->phone;
        if (empty($phone_number)) {
            $errors[] = 'Número do telefone não informado.';
        }

        return $errors;
    }

    protected function customerHasValidOrder($customerId)
    {
        if (!$customerId) {
            return false;
        }

        $query = new DbQuery();
        $query->select('1');
        $query->from('orders', 'o');
        $query->where('o.id_customer = ' . (int)$customerId);
        $query->where('o.valid = 1');

        return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($query);
    }

    public function canShowCreditCardOption()
    {
        if (!Configuration::get('AGPAGSEGURO_CREDIT_CARD_ENABLE')) {
            return false;
        }

        if (!Configuration::get('AGPAGSEGURO_CREDIT_CARD_REQUIRE_VALID_ORDER')) {
            return true;
        }

        $customerId = (int) $this->context->customer->id;
        if (!$customerId) {
            return false;
        }

        return $this->customerHasValidOrder($customerId);
    }

    public function hookPaymentTop()
    {
        if (!$this->active) {
            return;
        }

        $errors = $this->validatePayment($this->context->cart, $this->context->customer);
        
        $this->context->smarty->assign([
            'errors' => $errors,
            'display_banner' => Configuration::get('AGPAGSEGURO_DISPLAY_BANNER'),
        ]);

        return $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/hook/payment_top.tpl');
    }

    public function hookPaymentOptions()
    {
        if (!$this->active) {
            return;
        }

        $errors = $this->validatePayment($this->context->cart, $this->context->customer);
        if (count($errors)) {
            return;
        }

        // seller authentication
        $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');
        if ($sandbox) {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
        } else {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
        }

        $options = [];

        if (Configuration::get('AGPAGSEGURO_TICKET_ENABLE') && $this->context->currency->iso_code === 'BRL') {
            $newOption = new PaymentOption();
            
            if(Configuration::get('AGPAGSEGURO_TICKET_TEXT_CHECKOUT')){
                $textDisplay = Configuration::get('AGPAGSEGURO_TICKET_TEXT_CHECKOUT');
            }else{
                $textDisplay = 'Pague via Boleto Bancário';
            }

            $newOption->setCallToActionText($textDisplay)
                ->setForm($this->generateTicketForm());

            $options[] = $newOption;
        }

        if (Configuration::get('AGPAGSEGURO_PIX_ENABLE') && $this->context->currency->iso_code === 'BRL') {
            $newOption = new PaymentOption();
            
            if(Configuration::get('AGPAGSEGURO_PIX_TEXT_CHECKOUT')){
                $textDisplay = Configuration::get('AGPAGSEGURO_PIX_TEXT_CHECKOUT');
            }else{
                $textDisplay = 'Pague no PIX';
            }

            $newOption->setCallToActionText($textDisplay)
                ->setForm($this->generatePixForm());

            $options[] = $newOption;
        }

        if ($this->canShowCreditCardOption()) {
            $newOption = new PaymentOption();
            
            if(Configuration::get('AGPAGSEGURO_CREDIT_CARD_TEXT_CHECKOUT')){
                $textDisplay = Configuration::get('AGPAGSEGURO_CREDIT_CARD_TEXT_CHECKOUT');
            }else{
                $textDisplay = 'Pague via Cartão de Crédito';
            }

            $newOption->setCallToActionText($textDisplay)
                ->setForm($this->generateCreditCardForm());
            
            $options[] = $newOption;
        }

        // if (Configuration::get('AGPAGSEGURO_EFT_ENABLED')) {            
        //     $newOption = new PaymentOption();

        //     if(Configuration::get('AGPAGSEGURO_EFT_TEXT_CHECKOUT')){
        //         $textDisplay = Configuration::get('AGPAGSEGURO_EFT_TEXT_CHECKOUT');
        //     }else{
        //         $textDisplay = 'Pague via Débito em Conta';
        //     }

        //     $newOption->setCallToActionText($textDisplay)
        //         ->setForm($this->generateEFTForm($session))
        //         ->setLogo(Configuration::get('AGPAGSEGURO_EFT_IMG_METHOD')? Media::getMediaPath(_PS_MODULE_DIR_ . $this->name . '/views/img/credit_card.png') : null);

        //     $options[] = $newOption;
        // }

        return $options;
    }


    public function hookPayment()
    {
        if (!$this->active) {
            return;
        }

        
        $errors = $this->validatePayment($this->context->cart, $this->context->customer);
        if (count($errors)) {
            return;
        }

        $image_boleto_path = __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/boleto.png';
        $image_credit_card_path = __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/credit_card.png';
        $image_eft_path = __PS_BASE_URI__ . 'modules/' . $this->name . '/views/img/eft.png';

        $this->context->smarty->assign(array(
            'image_boleto' => Tools::getShopProtocol() . Tools::getMediaServer($image_boleto_path) . $image_boleto_path,
            'image_credit_card' => Tools::getShopProtocol() . Tools::getMediaServer($image_credit_card_path) . $image_credit_card_path,
            'image_eft' => Tools::getShopProtocol() . Tools::getMediaServer($image_eft_path) . $image_eft_path,
            'ticket_active' => Configuration::get('AGPAGSEGURO_TICKET_ENABLE'),
            'credit_card_active' => $this->canShowCreditCardOption(),
            'eft_active' => Configuration::get('AGPAGSEGURO_EFT_ENABLED')
        ));    
        
        return $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/hook/payment_options.tpl');
    }

    public function hookDisplayOrderConfirmation($params)
    {
        if (!$this->active) {
            return;
        }

        if (version_compare(_PS_VERSION_, '1.7', '<')) {
            $order = $params['objOrder'];
        } else {
            $order = $params['order'];
        }

        if ($order->module != 'agpagseguro') {
            return;
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $ettOrder = $em->getRepository(Orders::class)->findOneBy(['id' => $order->id]);
        $transaction = $em->getRepository(EttAgPagseguroTransaction::class)->findOneBy(['order' => $ettOrder]);
        // $transaction = AgPagseguroTransaction::getByOrderId($order->id);

        ///if (is_null($transaction)) {
            // Logger::addLog('agpagseguro - Erro buscando a transação associada ao pedido ' . $order->id, 3, '0002');
        //    return;
        //}

        try {
            // $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);
            // $pagseguro_transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction->transaction_code);

            $error = null;

            if (is_null($transaction)) {
                $error = 'Desculpe, ocorreu um erro ao realizar o pagamento.';
            }

            // $payment_link = $pagseguro_transaction->getPaymentLink();
            $orderState = new OrderState($order->current_state, $this->context->language->id);

            $this->context->smarty->assign(array(
                'transaction' => $transaction,
                // 'remote_transaction' => $pagseguro_transaction,
                'order' => $order,
                'error' => $error,
                'order_state' => $orderState
            ));

            $canListen = false;
            if ($transaction && !$order->hasBeenPaid()) {
                $canListen = in_array((int) $transaction->getType(), [1, 3], true);
            }

            $eventConfig = [
                'enabled' => $canListen,
                'sseUrl' => null,
                'orderId' => (int) $order->id,
                'isPaid' => (bool) $order->hasBeenPaid(),
                'browserEventName' => 'agpagseguro:order:approved',
                'reloadDelay' => 0,
                'autoReload' => true,
                'approvedMessage' => $this->l('Pagamento aprovado! Vamos atualizar esta página para mostrar os novos detalhes.'),
                'waitingMessage' => $this->l('Estamos aguardando a confirmação do pagamento. Assim que o PagSeguro aprovar, atualizaremos esta página automaticamente.'),
                'timeoutMessage' => $this->l('Ainda não recebemos a confirmação do pagamento. Você pode continuar navegando e voltar mais tarde para verificar novamente.'),
                'currentState' => $orderState->name,
                'approvedStateLabel' => $this->l('Pagamento aprovado'),
            ];

            if ($canListen) {
                $eventConfig['sseUrl'] = $this->context->link->getModuleLink(
                    $this->name,
                    'orderstatussse',
                    [
                        'id_order' => $order->id,
                        'key' => $order->secure_key
                    ],
                    true
                );
            }

            Media::addJsDef([
                'agpagseguroConfirmation' => $eventConfig
            ]);

            $this->context->controller->addCss($this->_path . 'views/css/confirmation.css');
            $this->context->controller->addJs($this->_path . 'views/js/confirmation.js');

            return $this->display(_PS_MODULE_DIR_ . $this->name, 'success.tpl');

        } catch (Exception $e) {
            Logger::addLog('agpagseguro - Erro buscando a transação associada ao pedido ' . $order->id . ' - ' . $e->getMessage(), 3, '0003');
        }
    }



    public function hookDisplayOrderDetail($params)
    {
        if (!$this->active) {
            return;
        }

        $order = $params['order'];
        if ($order->module !== $this->name) {
            return;
        }

        if ($order->hasBeenPaid()) {
            return;
        }

        $em = $this->get('doctrine.orm.entity_manager');
        $ettOrder = $em->getRepository(Orders::class)->findOneBy(['id' => $order->id]);
        $transaction = $em->getRepository(EttAgPagseguroTransaction::class)->findOneBy(['order' => $ettOrder]);

        if (is_null($transaction)) {
            Logger::addLog('agpagseguro - Erro buscando a transação associada ao pedido ' . $order->id, 3, '0004');
            return;
        }

        try {
            $this->context->smarty->assign([
                'transaction' => $transaction
            ]);

            $this->context->controller->addCss($this->_path . 'views/css/confirmation.css');
            $this->context->controller->addJs($this->_path . 'views/js/confirmation.js');

            return $this->display(_PS_MODULE_DIR_ . $this->name, 'order_detail.tpl');
        } catch (Exception $e) {
            Logger::addLog('agpagseguro - Erro buscando a transação associada ao pedido ' . $order->id . ' - ' . $e->getMessage(), 3, '0005');
            return;
        }
    }


    protected function generateTicketForm()
    {
        if (!$this->active) {
            return;
        }

        $total = $this->context->cart->getOrderTotal();

        $discount = (float) Configuration::get('AGPAGSEGURO_TICKET_DISCOUNT');
        $total_with_discount = $total * (100 - $discount) / 100;

        $this->context->smarty->assign(array(
            'total_with_discount' => (new PriceFormatter)->format($total_with_discount),
            'form_action' => $this->context->link->getModuleLink($this->name, 'validation')
        ));

        return $this->display($this->_path, 'views/templates/front/ticket.ps17.tpl');
    }

    protected function generatePixForm()
    {
        if (!$this->active) {
            return;
        }

        $total = $this->context->cart->getOrderTotal();

        $discount = (float) Configuration::get('AGPAGSEGURO_PIX_DISCOUNT');
        $total_with_discount = $total * (100 - $discount) / 100;

        $this->context->smarty->assign(array(
            'total_with_discount' => (new PriceFormatter)->format($total_with_discount),
            'form_action' => $this->context->link->getModuleLink($this->name, 'validation')
        ));

        return $this->display($this->_path, 'views/templates/front/pix.ps17.tpl');
    }

    public function generateCreditCardForm()
    {
        if (!$this->active) {
            return;
        }

        $total = $this->context->cart->getOrderTotal();

        $this->context->smarty->assign(array(
            'total' => $total,
            'form_action' => $this->context->link->getModuleLink($this->name, 'validation')
        ));

        if (Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENT_METHOD') == 'local') {
            $this->context->smarty->assign(array(
                'agpagseguro_installments' => $this->getLocalInstallments($total)
            ));
        }

        return $this->display($this->_path, 'views/templates/front/credit_card.ps17.tpl');
    }

    // public function generateEFTForm($session_id)
    // {
    //     if (!$this->auth() || !$this->active) {
    //         return;
    //     }

    //     $total = $this->context->cart->getOrderTotal();

    //     $discount = (float) Configuration::get('AGPAGSEGURO_TICKET_DISCOUNT');
    //     $total_with_discount = $total * (100 - $discount) / 100;

    //     $this->context->smarty->assign(array(
    //         'total' => Tools::displayPrice($total_with_discount),
    //         'form_action' => $this->context->link->getModuleLink($this->name, 'validation'),
    //         'session_id' => $session_id
    //     ));

    //     return $this->display($this->_path, 'views/templates/front/eft.ps17.tpl');
    // }

    protected function loadMappings()
    {
        $this->cpf_mapping = new AgColumnMapping();
        $this->cpf_mapping->setData(array(
            'table_name' => 'customer',
            'configuration_name' => 'AGPAGSEGURO_CPF'
        ));
        $this->cpf_mapping->addColumn('djtalbrazilianregister', 'Módulo de Cadastro Brasileiro');

        $this->cnpj_mapping = new AgColumnMapping();
        $this->cnpj_mapping->setData(array(
            'table_name' => 'customer',
            'configuration_name' => 'AGPAGSEGURO_CNPJ'
        ));
        $this->cnpj_mapping->addColumn('djtalbrazilianregister', 'Módulo de Cadastro Brasileiro');

        $this->social_name_mapping = new AgColumnMapping();
        $this->social_name_mapping->setData(array(
            'table_name' => 'customer',
            'configuration_name' => 'AGPAGSEGURO_SOCIAL_NAME'
        ));

        $this->address_number_mapping = new AgColumnMapping();
        $this->address_number_mapping->setData(array(
            'table_name' => 'address',
            'configuration_name' => 'AGPAGSEGURO_ADDRESS_NUMBER'
        ));
    }


    public function renderAuthForm()
    {
        if (Tools::isSubmit('agpagseguro-config-auth')) {
            Configuration::updateValue('AGPAGSEGURO_EMAIL', Tools::getValue('agpagseguro_email'));
            Configuration::updateValue('AGPAGSEGURO_TOKEN_PRODUCTION', Tools::getValue('agpagseguro_token_production'));
            Configuration::updateValue('AGPAGSEGURO_TOKEN_SANDBOX', Tools::getValue('agpagseguro_token_sandbox'));
            Configuration::updateValue('AGPAGSEGURO_SANDBOX', Tools::getValue('agpagseguro_sandbox_enabled'));
            Configuration::updateValue('AGPAGSEGURO_CONFIGURATION_TIMEOUT_CLEAR_REQUESTS', Tools::getValue('agpagseguro_timeout_clear_requests'));
            Configuration::updateValue('AGPATSEGURO_EMAIL_SUBJECT_CANCEL_TRANSACTION', Tools::getValue('agpagseguro_email_subject_cancel_transaction'));
            Configuration::updateValue('AGPAGSEGURO_EMAILS_ALERT_MISSING_TRANSACTIONS', Tools::getValue('agpagseguro_emails_alert_missing_transactions'));
            Configuration::updateValue('AGPAGSEGURO_SHOW_MISSING_TRANSACTION_WARNING', Tools::getValue('agpagseguro_show_missing_transaction_warning'));
            Configuration::updateValue('AGPAGSEGURO_WEBHOOK_PROCESS_IMMEDIATE', Tools::getValue('agpagseguro_webhook_process_immediate'));
            Configuration::updateValue('AGPAGSEGURO_WEBHOOK_NOTIFICATION_URL', trim((string) Tools::getValue('agpagseguro_webhook_notification_url')));

            Configuration::updateValue('AGPAGSEGURO_EDI_USER', Tools::getValue('agpagseguro_edi_user'));
            Configuration::updateValue('AGPAGSEGURO_EDI_TOKEN', Tools::getValue('agpagseguro_edi_token'));
        }

        $helper = $this->generateDefaultHelperForm();

        $panels = [];
        $panels[0]['form'] = [
            'legend' => [
                'title' => 'Autenticação'
            ],
            'input' => [
                [
                    'name' => 'agpagseguro_email',
                    'type' => 'text',
                    'label' => 'E-mail',
                ],
                [
                    'name' => 'agpagseguro_token_production',
                    'type' => 'text',
                    'label' => 'Token para Produção',
                ],
                [
                    'name' => 'agpagseguro_token_sandbox',
                    'type' => 'text',
                    'label' => 'Token para Sandbox',
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Ativar Sandbox',
                    'name'   => 'agpagseguro_sandbox_enabled',
                    'id'     => 'agpagseguro_sandbox_enabled',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_sandbox_enabled_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_sandbox_enabled_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    ),
                ],
                [
                    'label' => 'Manter Requisições no Banco de Dados',
                    'name' => 'agpagseguro_timeout_clear_requests',
                    'type' => 'text',
                    'class' => 'center',
                    'col' => 1,
                    'suffix' => 'dias',
                    'desc'   => 'Se deixar 0 dias, a tabela de requisições não será limpa e isso pode fazer com que o disco do servidor encha mais rapidamente',
                ],
                [
                    'label' => 'Assunto do email de Pagamento Cancelado',
                    'name' => 'agpagseguro_email_subject_cancel_transaction',
                    'type' => 'text',
                    'desc'   => '{shop_name} = "nome da loja"<br/>
                    {order_id} = "ID do pedido"<br/>
                    {order_reference} = "Referencia do pedido"',
                ],
                [
                    'label' => 'Emails para receberem avisos de erro ao salvar o pagamento',
                    'name' => 'agpagseguro_emails_alert_missing_transactions',
                    'type' => 'text',
                    'desc'   => 'Separe os emails com ","',
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Exibir aviso de pedidos sem integração',
                    'name'   => 'agpagseguro_show_missing_transaction_warning',
                    'id'     => 'agpagseguro_show_missing_transaction_warning',
                    'desc'   => 'Quando desativado, o alerta com a lista de pedidos sem transação vinculada deixa de aparecer no administrativo.',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_show_missing_transaction_warning_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_show_missing_transaction_warning_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    ),
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Processar webhooks imediatamente',
                    'name'   => 'agpagseguro_webhook_process_immediate',
                    'id'     => 'agpagseguro_webhook_process_immediate',
                    'desc'   => 'Quando ativado, os webhooks são processados assim que chegam, sem depender da fila. Isso pode provocar lentidão se a loja receber muitos webhooks de uma só vez.',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_webhook_process_immediate_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_webhook_process_immediate_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    ),
                ],
                [
                    'name' => 'agpagseguro_webhook_notification_url',
                    'type' => 'text',
                    'label' => 'URL para recebimento de webhooks',
                    'desc' => 'Por padrão, utiliza a URL nativa do módulo. Você pode sobrescrever com uma URL personalizada.',
                ]
            ],
            'submit' => [
                'name' => 'agpagseguro-config-auth',
                'title' => 'Salvar'
            ]
        ];

        $panels[1]['form'] = [
            'legend' => [
                'title' => 'EDI'
            ],
            'description' => 'O EDI (Extrato Digital) do PagBank é utilizado para realizar a conciliação dos seus recebíveis e das tarifas cobrada a cada transação. Para utiliza-lo, é necessário solicitar a ativação do EDI na sua conta do PagBank.',
            'input' => [
                [
                    'type' => 'text',
                    'name' => 'agpagseguro_edi_user',
                    'label' => 'Usuário'
                ],
                [
                    'type' => 'text',
                    'name' => 'agpagseguro_edi_token',
                    'label' => 'Senha'
                ],
            ],
            'submit' => [
                'name' => 'agpagseguro-config-auth',
                'title' => 'Salvar'
            ]
        ];

        $helper->fields_value['agpagseguro_email'] = Configuration::get('AGPAGSEGURO_EMAIL');
        $helper->fields_value['agpagseguro_token_production'] = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
        $helper->fields_value['agpagseguro_token_sandbox'] = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
        $helper->fields_value['agpagseguro_sandbox_enabled'] = Configuration::get('AGPAGSEGURO_SANDBOX');
        $helper->fields_value['agpagseguro_timeout_clear_requests'] = Configuration::get('AGPAGSEGURO_CONFIGURATION_TIMEOUT_CLEAR_REQUESTS');
        $helper->fields_value['agpagseguro_email_subject_cancel_transaction'] = Configuration::get('AGPAGSEGURO_EMAIL_SUBJECT_CANCEL_TRANSACTION');
        $helper->fields_value['agpagseguro_emails_alert_missing_transactions'] = Configuration::get('AGPAGSEGURO_EMAILS_ALERT_MISSING_TRANSACTIONS');
        $helper->fields_value['agpagseguro_show_missing_transaction_warning'] = (int) Configuration::get('AGPAGSEGURO_SHOW_MISSING_TRANSACTION_WARNING');
        $helper->fields_value['agpagseguro_webhook_process_immediate'] = Configuration::get('AGPAGSEGURO_WEBHOOK_PROCESS_IMMEDIATE');
        $helper->fields_value['agpagseguro_webhook_notification_url'] = $this->getWebhookNotificationUrl();
        $helper->fields_value['agpagseguro_edi_user'] = Configuration::get('AGPAGSEGURO_EDI_USER');
        $helper->fields_value['agpagseguro_edi_token'] = Configuration::get('AGPAGSEGURO_EDI_TOKEN');

        return $helper->generateForm($panels);
    }
    
    public function renderMappingsForm()
    {
        if (Tools::isSubmit('agpagseguro-config-mappings')) {
            $this->getCpfMapping()->mapsTo(Tools::getValue('cpf_mapping'));
            $this->getCnpjMapping()->mapsTo(Tools::getValue('cnpj_mapping'));
            $this->getSocialNameMapping()->mapsTo(Tools::getValue('company_name_mapping'));
            $this->getAddressNumberMapping()->mapsTo(Tools::getValue('address_number_mapping'));

            foreach (AgPagSeguroTransactionStatuses::$values as $key=>$value) {
                Configuration::updateValue("AGPAGSEGURO_STATUS_{$key}", Tools::getValue("agpagseguro_status_{$key}"));
                Configuration::updateValue("AGPAGSEGURO_STATUS_{$key}_FINISH_ORDER", Tools::getValue("agpagseguro_status_{$key}_finish_order"));
            }
        }

        $cpf_fields = [];
        foreach ($this->getCpfMapping()->getColumnsFromTable() as $key => $column) {
            $cpf_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $cnpj_fields = [];
        foreach ($this->getCnpjMapping()->getColumnsFromTable() as $key => $column) {
            $cnpj_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $company_fields = [];
        foreach ($this->getSocialNameMapping()->getColumnsFromTable() as $key => $column) {
            $company_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }

        $number_fields = [];
        foreach ($this->getAddressNumberMapping()->getColumnsFromTable() as $key => $column) {
            $number_fields[] = [
                'id' => $key,
                'name' => $column
            ];
        }
        
        $panels = [];
        $panels[0]['form'] = [
            'legend' => [
                'title' => 'Mapeamento de Campos',
                'icon' => 'icon-person'
            ],
            'input'  => [
                [
                    'label' => 'CPF',
                    'name' => 'cpf_mapping',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $cpf_fields
                    ]
                ],
                [
                    'label' => 'CNPJ',
                    'name' => 'cnpj_mapping',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $cnpj_fields
                    ]
                ],
                [
                    'label' => 'Razão Social',
                    'name' => 'company_name_mapping',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $company_fields
                    ]
                ],
                [
                    'label' => 'Número do Endereço',
                    'name' => 'address_number_mapping',
                    'type' => 'select',
                    'col' => 4,
                    'options' => [
                        'id' => 'id',
                        'name' => 'name',
                        'query' => $number_fields
                    ]
                ],
            ],
            'submit' => [
                'name' => 'agpagseguro-config-mappings',
                'title' => 'Salvar'
            ]
        ];

        $panels[1]['form'] = [
            'legend' => [
                'title' => 'Mapeamento de Status',
                'icon' => 'icon-list'
            ],
            'input'  => [],
            'submit' => [
                'name' => 'agpagseguro-config-mappings',
                'title' => 'Salvar'
            ]
        ];

        $helper = $this->generateDefaultHelperForm();
        $states = [[
            'id_order_state' => -1,
            'name' => 'Não atualizar pedidos nesse estado'
        ]];

        $states = array_merge(
            $states,
            OrderState::getOrderStates($this->context->language->id
        ));
        foreach (AgPagSeguroTransactionStatuses::$values as $key=>$value) {
            $panels[1]['form']['input'][] = [
                'type' => 'select',
                'label' => $value,
                'name' => "agpagseguro_status_{$key}",
                'options' => [
                    'id' => 'id_order_state',
                    'name' => 'name',
                    'query' => $states
                ]
            ];

            $helper->fields_value["agpagseguro_status_{$key}"] = Configuration::get("AGPAGSEGURO_STATUS_{$key}");
        }


        $panels[2]['form'] = [
            'legend' => [
                'title' => 'Tratamento de Erros',
                'icon' => 'icon-list'
            ],
            'description' => 'Abaixo você pode especificar em quais situações um pedido será finalizado, e em quais situações exibiremos uma mensagem de erro para que o cliente possa tentar finalizar a compra novamente.',
            'input'  => [],
            'submit' => [
                'name' => 'agpagseguro-config-mappings',
                'title' => 'Salvar'
            ]
        ];

        foreach (AgPagSeguroTransactionStatuses::$values as $key=>$value) {
            $panels[2]['form']['input'][] = [
                'type' => 'switch',
                'label' => $value,
                'name' => "agpagseguro_status_{$key}_finish_order",
                'id' => 'agpagseguro_status_{$key}_finish_order',
                'values' => array(
                    array(
                        'id'    => "agpagseguro_status_{$key}_finish_order_on",
                        'value' => 1,
                        'label' => 'Sim',
                    ),
                    array(
                        'id'    => "agpagseguro_status_{$key}_finish_order",
                        'value' => 0,
                        'label' => 'Não',
                    ),
                )
            ];

            $helper->fields_value["agpagseguro_status_{$key}_finish_order"] = Configuration::get("AGPAGSEGURO_STATUS_{$key}_FINISH_ORDER");
        }

        $helper->fields_value['cpf_mapping'] = $this->getCpfMapping()->getMappedfield();
        $helper->fields_value['cnpj_mapping'] = $this->getCnpjMapping()->getMappedfield();
        $helper->fields_value['company_name_mapping'] = $this->getSocialNameMapping()->getMappedfield();
        $helper->fields_value['address_number_mapping'] = $this->getAddressNumberMapping()->getMappedfield();
        
        return $helper->generateForm($panels);
    }

    public function renderCreditCardForm()
    {
        if (Tools::isSubmit('agpagseguro-config-credit-card')) {
            Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_ENABLE', Tools::getValue('agpagseguro_credit_card_enable'));

            Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_NO_INTEREST', Tools::getValue('agpagseguro_credit_card_installments_no_interest'));
            Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_MAX', Tools::getValue('agpagseguro_credit_card_installments_max'));
            Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_MIN_VALUE', Tools::getValue('agpagseguro_credit_card_installments_min_value'));
            Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_INSTALLMENT_METHOD', Tools::getValue('agpagseguro_credit_card_installment_method'));

            Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_REQUIRE_VALID_ORDER', Tools::getValue('agpagseguro_credit_card_require_valid_order'));

            Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_IMG_METHOD', Tools::getValue('agpagseguro_credit_img_method'));
            Configuration::updateValue('AGPAGSEGURO_CREDIT_CARD_TEXT_CHECKOUT', Tools::getValue('agpagseguro_credit_card_text_checkout'));

            for ($i=1; $i<=12; $i++) {
                Configuration::updateValue("AGPAGSEGURO_INTEREST_{$i}", Tools::getValue("agpagseguro_interest_{$i}"));
            }
        }

        $panels[0]['form'] = [
            'legend' => [
                'title' => 'Cartão de Crédito'
            ],
            'input' => [
                [
                    'type'   => 'text',
                    'label'  => 'Texto exibido no checkout',
                    'name'   => 'agpagseguro_credit_card_text_checkout',
                    'id'     => 'agpagseguro_credit_card_text_checkout',
                    'col' => 4
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Exibir imagem do método no checkout',
                    'name'   => 'agpagseguro_credit_img_method',
                    'id'     => 'agpagseguro_credit_img_method',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_credit_img_method_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_credit_img_method_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    )
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Ativar Cartão de Crédito',
                    'name'   => 'agpagseguro_credit_card_enable',
                    'id'     => 'agpagseguro_credit_card_enable',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_credit_card_enable_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_credit_card_enable_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    )],
                [
                    'type'   => 'switch',
                    'label'  => 'Exibir Cartão apenas para clientes com uma venda válida',
                    'name'   => 'agpagseguro_credit_card_require_valid_order',
                    'id'     => 'agpagseguro_credit_card_require_valid_order',
                    'desc'   => 'Quando ativado, o cartão de crédito só aparece se o cliente já tiver pelo menos um pedido válido (valid=1).',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_credit_card_require_valid_order_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_credit_card_require_valid_order_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    )
                ],
                [
                    'type'   => 'text',
                    'label'  => 'Vezes sem juros',
                    'name'   => 'agpagseguro_credit_card_installments_no_interest',
                    'id'     => 'agpagseguro_credit_card_installments_no_interest',
                    'col' => 1
                ],
                [
                    'type'   => 'text',
                    'label'  => 'Máximo de Parcelas',
                    'name'   => 'agpagseguro_credit_card_installments_max',
                    'id'     => 'agpagseguro_credit_card_installments_max',
                    'col' => 1
                ],
                [
                    'type'   => 'text',
                    'label'  => 'Valor Mínimo da Parcela',
                    'name'   => 'agpagseguro_credit_card_installments_min_value',
                    'id'     => 'agpagseguro_credit_card_installments_min_value',
                    'col' => 1
                ],
                [
                    'type' => 'radio',
                    'label' => 'Método de Cálculo do Parcelamento',
                    'name' => 'agpagseguro_credit_card_installment_method',
                    'desc' => 'O cálculo via PagSeguro requer que o número do cartão de crédito seja informado antes do parcelamento ser calculado. O cálculo local requer que você especifique manualmente as tarifas de juros a serem utilizadas.',
                    'values' => [
                        [
                            'id' => 'agpagseguro_credit_card_installment_method_ws',
                            'value' => 'ws',
                            'label' => 'Webserver PagSeguro'
                        ],
                        [
                            'id' => 'agpagseguro_credit_card_installment_method_local',
                            'value' => 'local',
                            'label' => 'Calculo Local'
                        ],
                    ]
                ]
            ],
            'submit' => [
                'name' => 'agpagseguro-config-credit-card',
                'title' => 'Salvar'
            ]
        ];

        for ($i=1; $i<=12; $i++) {
            $panels[0]['form']['input'][] = [
                'type' => 'text',
                'label' => "Tarifa de juros para {$i}x",
                'name' => "agpagseguro_interest_{$i}",
                'col' => 1,
                'suffix' => '%'
            ];
        }

        $helper = $this->generateDefaultHelperForm();
        $helper->fields_value['agpagseguro_credit_card_enable'] = Configuration::get('AGPAGSEGURO_CREDIT_CARD_ENABLE');
        $helper->fields_value['agpagseguro_credit_card_installments_no_interest'] = Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_NO_INTEREST');
        $helper->fields_value['agpagseguro_credit_card_installments_max'] = Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_MAX');
        $helper->fields_value['agpagseguro_credit_card_installments_min_value'] = Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_MIN_VALUE');
        $helper->fields_value['agpagseguro_credit_card_installment_method'] = Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENT_METHOD');
        
        $helper->fields_value['agpagseguro_credit_img_method'] = Configuration::get('AGPAGSEGURO_CREDIT_CARD_IMG_METHOD');
        $helper->fields_value['agpagseguro_credit_card_require_valid_order'] = Configuration::get('AGPAGSEGURO_CREDIT_CARD_REQUIRE_VALID_ORDER');
        $helper->fields_value['agpagseguro_credit_card_text_checkout'] = Configuration::get('AGPAGSEGURO_CREDIT_CARD_TEXT_CHECKOUT');

        for ($i=1; $i<=12; $i++) {
            $helper->fields_value["agpagseguro_interest_{$i}"] = Configuration::get("AGPAGSEGURO_INTEREST_{$i}");
        }
        
        return $helper->generateForm($panels);
    }

    public function renderTicketForm()
    {
        if (Tools::isSubmit('agpagseguro-config-ticket')) {
            Configuration::updateValue('AGPAGSEGURO_TICKET_DISCOUNT', Tools::getValue('agpagseguro_ticket_discount'));
            Configuration::updateValue('AGPAGSEGURO_TICKET_ENABLE', Tools::getValue('agpagseguro_ticket_enable'));
        
            Configuration::updateValue('AGPAGSEGURO_TICKET_IMG_METHOD', Tools::getValue('agpagseguro_ticket_img_method'));
            Configuration::updateValue('AGPAGSEGURO_TICKET_TEXT_CHECKOUT', Tools::getValue('agpagseguro_ticket_text_checkout'));
        }
        
        $panels[0]['form'] = [
            'legend' => [
                'title' => 'Boleto Bancário'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'Texto exibido no checkout',
                    'name' => 'agpagseguro_ticket_text_checkout',
                    'id'     => 'agpagseguro_ticket_text_checkout',
                    'col' => 4
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Exibir imagem do método no checkout',
                    'name'   => 'agpagseguro_ticket_img_method',
                    'id'     => 'agpagseguro_ticket_img_method',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_ticket_img_method_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_ticket_img_method_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    )
                ],
                [
                    'type' => 'text',
                    'label' => 'Desconto',
                    'suffix' => '%',
                    'col' => 2,
                    'name' => 'agpagseguro_ticket_discount'
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Ativar Boleto Bancário',
                    'name'   => 'agpagseguro_ticket_enable',
                    'id'     => 'agpagseguro_ticket_enable',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_ticket_enable_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_ticket_enable_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    ),
                ],
            ],
            'submit' => [
                'name' => 'agpagseguro-config-ticket',
                'title' => 'Salvar'
            ]
        ];

        $helper = $this->generateDefaultHelperForm();
        
        $helper->fields_value['agpagseguro_ticket_enable'] = Configuration::get('AGPAGSEGURO_TICKET_ENABLE');
        $helper->fields_value['agpagseguro_ticket_discount'] = Configuration::get('AGPAGSEGURO_TICKET_DISCOUNT');
        
        $helper->fields_value['agpagseguro_ticket_img_method'] = Configuration::get('AGPAGSEGURO_TICKET_IMG_METHOD');
        $helper->fields_value['agpagseguro_ticket_text_checkout'] = Configuration::get('AGPAGSEGURO_TICKET_TEXT_CHECKOUT');

        return $helper->generateForm($panels);
    }


    public function renderPixForm()
    {
        if (Tools::isSubmit('agpagseguro-config-pix')) {
            Configuration::updateValue('AGPAGSEGURO_PIX_DISCOUNT', Tools::getValue('agpagseguro_pix_discount'));
            Configuration::updateValue('AGPAGSEGURO_PIX_ENABLE', Tools::getValue('agpagseguro_pix_enable'));
            Configuration::updateValue('AGPAGSEGURO_PIX_TEXT_CHECKOUT', Tools::getValue('agpagseguro_pix_text_checkout'));
        }
        
        $panels[0]['form'] = [
            'legend' => [
                'title' => 'PIX'
            ],
            'input' => [
                [
                    'type' => 'text',
                    'label' => 'Texto exibido no checkout',
                    'name' => 'agpagseguro_pix_text_checkout',
                    'id'     => 'agpagseguro_pix_text_checkout',
                    'col' => 4
                ],
                [
                    'type' => 'text',
                    'label' => 'Desconto',
                    'suffix' => '%',
                    'col' => 2,
                    'name' => 'agpagseguro_pix_discount'
                ],
                [
                    'type'   => 'switch',
                    'label'  => 'Ativar PIX',
                    'name'   => 'agpagseguro_pix_enable',
                    'id'     => 'agpagseguro_pix_enable',
                    'values' => array(
                        array(
                            'id'    => 'agpagseguro_pix_enable_on',
                            'value' => 1,
                            'label' => 'Sim',
                        ),
                        array(
                            'id'    => 'agpagseguro_pix_enable_off',
                            'value' => 0,
                            'label' => 'Não',
                        ),
                    )
                ],
            ],
            'submit' => [
                'name' => 'agpagseguro-config-pix',
                'title' => 'Salvar'
            ]
        ];

        $helper = $this->generateDefaultHelperForm();
        
        $helper->fields_value['agpagseguro_pix_enable'] = Configuration::get('AGPAGSEGURO_PIX_ENABLE');
        $helper->fields_value['agpagseguro_pix_discount'] = Configuration::get('AGPAGSEGURO_PIX_DISCOUNT');       
        $helper->fields_value['agpagseguro_pix_text_checkout'] = Configuration::get('AGPAGSEGURO_PIX_TEXT_CHECKOUT');

        return $helper->generateForm($panels);
    }
    
    // public function renderEFTForm()
    // {
    //     if (Tools::isSubmit('agpagseguro-config-eft')) {
    //         Configuration::updateValue('AGPAGSEGURO_EFT_DISCOUNT', Tools::getValue('agpagseguro_eft_discount'));
    //         Configuration::updateValue('AGPAGSEGURO_EFT_ENABLED', Tools::getValue('agpagseguro_eft_enable'));
        
    //         Configuration::updateValue('AGPAGSEGURO_EFT_TEXT_CHECKOUT', Tools::getValue('agpagseguro_eft_text_checkout'));
    //         Configuration::updateValue('AGPAGSEGURO_EFT_IMG_METHOD', Tools::getValue('agpagseguro_eft_img_method'));
    //     }
        
    //     $panels[0]['form'] = [
    //         'legend' => [
    //             'title' => 'Débito em Conta'
    //         ],
    //         'input' => [
    //             [
    //                 'type' => 'text',
    //                 'label' => 'Texto exibido no checkout',
    //                 'name' => 'agpagseguro_eft_text_checkout',
    //                 'id'     => 'agpagseguro_eft_text_checkout',
    //                 'col' => 4
    //             ],
    //             [
    //                 'type'   => 'switch',
    //                 'label'  => 'Exibir imagem do método no checkout',
    //                 'name'   => 'agpagseguro_eft_img_method',
    //                 'id'     => 'agpagseguro_eft_img_method',
    //                 'values' => array(
    //                     array(
    //                         'id'    => 'agpagseguro_eft_img_method_on',
    //                         'value' => 1,
    //                         'label' => 'Sim',
    //                     ),
    //                     array(
    //                         'id'    => 'agpagseguro_eft_img_method_off',
    //                         'value' => 0,
    //                         'label' => 'Não',
    //                     ),
    //                 )
    //             ],
    //             [
    //                 'type' => 'text',
    //                 'label' => 'Desconto',
    //                 'suffix' => '%',
    //                 'col' => 2,
    //                 'name' => 'agpagseguro_eft_discount'
    //             ],
    //             [
    //                 'type'   => 'switch',
    //                 'label'  => 'Ativar Débito em Conta',
    //                 'name'   => 'agpagseguro_eft_enable',
    //                 'id'     => 'agpagseguro_eft_enable',
    //                 'values' => array(
    //                     array(
    //                         'id'    => 'agpagseguro_eft_enable_on',
    //                         'value' => 1,
    //                         'label' => 'Sim',
    //                     ),
    //                     array(
    //                         'id'    => 'agpagseguro_eft_enable_off',
    //                         'value' => 0,
    //                         'label' => 'Não',
    //                     ),
    //                 ),
    //                 'readonly' => $force_sandbox
    //             ],
    //         ],
    //         'submit' => [
    //             'name' => 'agpagseguro-config-eft',
    //             'title' => 'Salvar'
    //         ]
    //     ];

    //     $helper = $this->generateDefaultHelperForm();
        
    //     $helper->fields_value['agpagseguro_eft_enable'] = Configuration::get('AGPAGSEGURO_EFT_ENABLED');
    //     $helper->fields_value['agpagseguro_eft_discount'] = Configuration::get('AGPAGSEGURO_EFT_DISCOUNT');
        
    //     $helper->fields_value['agpagseguro_eft_text_checkout'] = Configuration::get('AGPAGSEGURO_EFT_TEXT_CHECKOUT');
    //     $helper->fields_value['agpagseguro_eft_img_method'] = Configuration::get('AGPAGSEGURO_EFT_IMG_METHOD');

    //     return $helper->generateForm($panels);
    // }

    protected function renderConfigForm()
    {
        agcliente::prepareConfigHelpTab($this->name);

        $auth_tab = $this->renderAuthForm();
        $mappings_tab = $this->renderMappingsForm();
        $credit_card_tab = $this->renderCreditCardForm();
        $ticket_tab = $this->renderTicketForm();
        $pix_tab = $this->renderPixForm();
        // $eft_tab = $this->renderEFTForm();

        $this->context->smarty->assign([
            'tabs' => [
                'auth' => $auth_tab,
                'mappings' => $mappings_tab,
                'credit_card' => $credit_card_tab,
                'pix' => $pix_tab,
                'maintenance' => agcliente::renderMaintanceTab($this),
                'ticket' => $ticket_tab
            ],
            'modules_path' => _PS_MODULE_DIR_,

            'url_requests' => $this->context->link->getAdminLink('AdminAgPagSeguroRequest'),
            'url_transactions' => $this->context->link->getAdminLink('AdminAgPagSeguroTransaction'),
            'url_webhooks' => $this->context->link->getAdminLink('AdminAgPagSeguroWebhook'),
        ]);

        $html = $this->display(_PS_MODULE_DIR_ . $this->name, 'views/templates/admin/configuration.tpl');
        return $html;
    }


    public function createTicketForOrder(Order $order, $options = array())
    {
         $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');

        if ($sandbox) {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
        } else {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
        }

        $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);

         // Instantiate a new payment request
        $directPaymentRequest = new PagSeguroDirectPaymentRequest();

        // Set the Payment Mode for this payment request
        $directPaymentRequest->setPaymentMode('DEFAULT');

        $directPaymentRequest->setReceiverEmail(Configuration::get('AGPAGSEGURO_EMAIL'));

        // Set the currency
        $directPaymentRequest->setCurrency("BRL");

        //adiciona os produtos ao pedido
        $sql = new DbQuery;
        $sql->from('order_detail')
            ->where('id_order=' . (int)$order->id);

        $products = Db::getInstance()->executeS($sql);
        $directPaymentRequest->setReference($order->reference);

        foreach ($products as $product) {
            $maskedName = $this->getFeatureValue($product['product_id'], 'MASKED_NAME');
            $productName = $maskedName ?: $product['product_name'];

            $directPaymentRequest->addItem(
                $product['product_reference'] ? $product['product_reference'] : $product['product_id'],
                $productName,
                $product['product_quantity'],
                $product['unit_price_tax_incl']
            );
        }

        $customer = new Customer($order->id_customer);
        $invoice_address = new Address($order->id_address_invoice);
        $phone_number = $invoice_address->phone_mobile ?: $invoice_address->phone;
        $phone_number = preg_replace('/[^0-9]/', '', $phone_number);

        $customer_data = $this->getCustomerData($customer);

        $directPaymentRequest->setSender(
            $customer_data['name'],
            $sandbox? 'teste123@sandbox.pagseguro.com.br' : $customer->email,
            Tools::substr($phone_number, 0, 2),
            Tools::substr($phone_number, 2),
            $customer_data['document_type'],
            $customer_data['document_number']
        );

        $address_delivery = new Address($order->id_address_delivery);
        $mapping_number = $this->getAddressNumberMapping();
        $address_number = 's/n';
        if ($mapping_number->isMappingEnabled()) {
            $address_number = @$address_delivery->{$mapping_number->getMappedField()};
        }

        $state = new State($address_delivery->id_state);
        $directPaymentRequest->setShippingAddress(
            $address_delivery->postcode, 
            $address_delivery->address1,
            $address_number,
            $address_delivery->other,
            $address_delivery->address2,
            $address_delivery->city,
            $state->iso_code,
            'BRA'
        );

        $products = $order->total_products;
        $discount = $order->total_discounts;
        $shipping = $order->total_shipping;

        if ($discount >= $shipping) {
            $discount -= $shipping;
            $shipping = 0;
        } else {
            $shipping -= $discount;
        }

        $directPaymentRequest->setExtraAmount(number_format(-$discount, 2, '.', ''));
        $directPaymentRequest->setShippingCost($shipping);

        $directPaymentRequest->setNotificationURL($this->context->link->getModuleLink($this->name, 'webhook'));

        $directPaymentRequest->setPaymentMethod('BOLETO');
        $directPaymentRequest->setSenderHash($options['sender_hash']);


        $payment_way = 'Pagseguro - Boleto Bancário';
        $return = $directPaymentRequest->register($credentials);

        $print_ticket_link = $return->getPaymentLink();

        //salva a transação no bd
        $transaction = new AgPagseguroTransaction();
        $transaction->id_order = $order->id;
        $transaction->transaction_code = $return->getCode();

        $transaction->type = 0;

        if (!$transaction->add()) {
            Logger::addLog('agpagseguro - Ocorreu um erro ao salvar a transação ' . $transaction->transaction_code . ' no banco de dados - ' . Db::getInstance()->getMsgError(), 3, '0001');
        }
        return $print_ticket_link;
    }

    public function getLocalInstallments($order_total)
    {
        $interests = [];
        for ($i=1; $i<=12; $i++) {
            $interests[$i] = Configuration::get("AGPAGSEGURO_INTEREST_{$i}");
        }

        $params = [
            'qtt_instalments_max' => Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_MAX') ?: 12,
            'installment_value_min' => Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_MIN_VALUE'),
            'qtt_installments_without_interest' => Configuration::get('AGPAGSEGURO_CREDIT_CARD_INSTALLMENTS_NO_INTEREST'),
            'value' => $order_total,
            'interest_rate' => $interests,
            'interest_mode' => 1
        ];

        $installments = AgClienteMathHelper::calcInstallments($params);
        return $installments;
    }

    public function hookActionOrderStatusUpdate($param)
    {
        try {
            if ($param["newOrderStatus"]->paid == 1) {
                $em = $this->get('doctrine.orm.entity_manager');

                $order = $em->getRepository(Orders::class)->findOneBy(['id' => $param['id_order']]);

                $transactions = $em->getRepository(EttAgPagseguroTransaction::class)->findBy(['order' => $order]);

                foreach ($transactions as $transaction) {
                    $reminders = $em->getRepository(AgpagseguroTicketReminder::class)->findBy(['transaction' => $transaction]);
                    foreach ($reminders as $reminder) {
                        $reminder->setCanceled(true);
                    }   
                }
                $em->flush();
            }
        } catch (Exception $e) {
        }
    }

    private function getFeatureValue($productId, $featureName)
    {
        $sql = new \DbQuery();
        $sql->select('fvl.value')
            ->from('feature_lang', 'fl')
            ->innerJoin('feature_product', 'fp', 'fp.id_feature = fl.id_feature')
            ->innerJoin('feature', 'f', 'f.id_feature = fl.id_feature')
            ->innerJoin('feature_value_lang', 'fvl', 'fvl.id_feature_value = fp.id_feature_value AND fvl.id_lang = fl.id_lang')
            ->where('fp.id_product = ' . (int)$productId)
            ->where('fl.id_lang = ' . (int)\Context::getContext()->language->id)
            ->where('fl.name = "' . pSQL($featureName) . '"');

        return \Db::getInstance()->getValue($sql);
    }
}
