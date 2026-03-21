<?php

class AgPagSeguroWebhook extends AgObjectModel
{
    private static function ensureLockLoaded()
    {
        if (!class_exists('AgPagSeguroLock', false)) {
            require_once _PS_MODULE_DIR_ . 'agpagseguro/lib/AgPagSeguroLock.php';
        }
    }

    public static $definition = array(
        'table'     => 'agpagseguro_webhook',
        'primary'   => 'id_agpagseguro_webhook',
        'multilang' => false,
        'fields'    => array(
            'id_agpagseguro_webhook' => array('type' => self::TYPE_INT),
            'notification_code' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(64)', 'required' => true),
            'id_shop' => ['type' => self::TYPE_INT, 'db_type' => 'int'],
            'status' => ['type' => self::TYPE_INT, 'db_type' => 'int'],
            'date_add' => ['type' => self::TYPE_DATE, 'db_type' => 'datetime'],
            'date_upd' => ['type' => self::TYPE_DATE, 'db_type' => 'datetime']
        )
    );

    public $id_agpagseguro_webhook;
    public $notification_code;
    public $id_shop;

    //0: na fila
    //1: erro
    //2: processada
    public $status;
    public $date_add;
    public $date_upd;

    public static function findNext()
    {
        $sql = new DbQuery;
        $sql->from('agpagseguro_webhook', 'a')
            ->where('status = 0')
            ->orderBy('date_upd ASC')
            ->where("id_shop=" . (int) Context::getContext()->shop->id)
            ->where('date_add > "' . date('Y-m-d H:i:s', strtotime('-14 days')) . '"')
            // ->where('date_upd < "' . date('Y-m-d H:i:s', strtotime('-5 minutes')) . '"');
            ;

        $return = Db::getInstance()->getRow($sql);

        return $return;
    }

    public function process()
    {
        //apenas atualiza a data de atualização
        $this->save();

        $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');
        if ($sandbox) {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
        } else {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
        }

        try {
            $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);
            $notificationCode = $this->notification_code;
            $pagseguro_transaction = PagSeguroNotificationService::checkTransaction($credentials, $notificationCode);
            $transaction = AgPagseguroTransaction::getByTransaction($pagseguro_transaction->getCode());

            if (!Validate::isLoadedObject($transaction)) {
                return;
            }

            $ps_order = new Order($transaction->id_order);
	        if (!Validate::isLoadedObject($ps_order)) {
                return;
            }
            $pagseguro_transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction->transaction_code);
            $status = $pagseguro_transaction->getStatus()->getValue();

            $transaction->transaction_status = $status;
            $transaction->update();

            $ps_status = Configuration::get("AGPAGSEGURO_STATUS_{$status}");
            if ((int) $ps_status <= 0) {
                return;
            }

            if($status == 7){
                $data = array(
                    '{shop_url}' => Context::getContext()->link->getPageLink('index'),
                    '{shop_name}' => Configuration::get('PS_SHOP_NAME'),
                    '{firstname}' => $ps_order->getCustomer()->firstname,
                    '{lastname}' => $ps_order->getCustomer()->lastname,
                    '{order_name}' => $ps_order->reference,
                    '{CancellationSource}' => $pagseguro_transaction->getCancellationSource()->getValue() === 'EXTERNAL' ? 'Pagamento recusado pela operadora de cartão de crédito' : 'Pagamento recusado pelo PagSeguro'
                );

                $subject = Configuration::get('AGPATSEGURO_EMAIL_SUBJECT_CANCEL_TRANSACTION');

                $serch= [
                	"{shop_name}",
                    "{order_id}" ,
                    "{order_reference}"
                ];
                
                $replace = [
                    Configuration::get('PS_SHOP_NAME'),
                    $ps_order->id,
                    $ps_order->reference
                ];

                $subject = str_replace($serch, $replace, $subject);

                Mail::Send(
                    (int)$ps_order->id_lang,
                    'cancelTransaction',
                    $subject,
                    $data,
                    $ps_order->getCustomer()->email,
                    $ps_order->getCustomer()->firstname . ' ' . $ps_order->getCustomer()->lastname,
                    null,
                    null,
                    null,
                    null,
                    _PS_MODULE_DIR_ . 'agpagseguro/mails/'
                );
            }

            $orderId = (int) $ps_order->id;
            self::ensureLockLoaded();
            AgPagSeguroLock::withOrderLock($orderId, function () use ($orderId, $ps_status) {
                $ps_order = new Order($orderId);
                if (!Validate::isLoadedObject($ps_order)) {
                    return;
                }

                if ($ps_status != $ps_order->current_state) {
                    $history = $ps_order->getHistory(Context::getContext()->language->id, $ps_status);
                    if (!$history) {
                        $ps_order->setCurrentState($ps_status);
                    }
                }
            });

            $this->status = 2;
            $this->save();
        } catch (Exception $e) {
            Logger::addLog("agpagseguro - Erro processando webhook {$this->id} - " . $e->getMessage(), 3, null, null, null, true);
        }
    }
}
