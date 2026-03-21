<?php
class AgPagseguroTransaction extends AgObjectModel
{
    private static function ensureLockLoaded()
    {
        if (!class_exists('AgPagSeguroLock', false)) {
            require_once _PS_MODULE_DIR_ . 'agpagseguro/lib/AgPagSeguroLock.php';
        }
    }

    public static $definition = array(
        'table'     => 'agpagseguro_transaction',
        'primary'   => 'id_agpagseguro_transaction',
        'multilang' => false,
        'fields'    => array(
            'id_agpagseguro_transaction' => array('type' => self::TYPE_INT,    'validate' => 'isInt'),
            'id_order'                   => array('type' => self::TYPE_INT,    'validate' => 'isInt', 'db_type' => 'int'),
            'transaction_code'           => array('type' => self::TYPE_STRING,                        'db_type' => 'varchar(100)'),
            'transaction_status'         => array('type' => self::TYPE_STRING,                        'db_type' => 'varchar(100)'),
            'type'                       => array('type' => self::TYPE_INT,    'validate' => 'isInt', 'db_type' => 'int'),
            'date_add' => ['type' => self::TYPE_DATE, 'db_type' => 'datetime'],
            'date_upd' => ['type' => self::TYPE_DATE, 'db_type' => 'datetime'],
            'expiration_date' => ['type' => self::TYPE_DATE, 'db_type' => 'datetime'],
            
            'transaction_data' => ['type' => self::TYPE_STRING, 'db_type' => 'text'],
            'transaction_reference' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(100)'),
            'qty_installments' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
            'transaction_total' => array('type' => self::TYPE_FLOAT, 'db_type' => 'float'),
            'intermediation_cost' => array('type' => self::TYPE_FLOAT, 'db_type' => 'float'),
            'transaction_link' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'copy_text' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'pagseguro_order_id' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(255)'),
            'value' => array('type' => self::TYPE_INT, 'db_type' => 'int'),
            'card_brand' => array('type' => self::TYPE_STRING, 'db_type' => 'varchar(50)'),
        )
    );

    public $id_agpagseguro_transaction;
    public $id_order;
    public $transaction_code;
    public $transaction_status;
    //0: boleto; 1: cartão
    public $type;

    public $date_add;
    public $date_upd;
    public $expiration_date;
    public $transaction_data;

    public $transaction_reference;
    public $qty_installments;
    public $transaction_total;
    public $intermediation_cost;
    public $transaction_link;
    public $copy_text;
    public $pagseguro_order_id;
    public $value;
    /**
     * Bandeira do cartão de crédito utilizado na transação
     * @var string|null
     */
    public $card_brand;

    public static function getByOrderId($id_order)
    {
        $order = new Order($id_order);

        $sql = new DbQuery();
        $sql->select('t.*');
        $sql->from('agpagseguro_transaction', 't');
        $sql->where('t.id_order="' . (int) $id_order . '"');

        $db_data = Db::getInstance()->getRow($sql);
        if (!$db_data) {
            $db_data = array();
        }

        $return = new AgPagseguroTransaction();
        $return->hydrate($db_data);

        return $return;
    }


    /**
     * @return AgPagseguroTransaction
     */
    public static function getByTransaction($transaction_code)
    {
        $collection = new PrestaShopCollection('AgPagseguroTransaction');
        $collection->where('transaction_code', '=', $transaction_code);

        return $collection->getFirst();
    }

    

    /**
     * @return array[AgPagseguroTransaction]
     */
    public static function getByStatus($status)
    {
        $sql = new DbQuery;
        $sql->from('agpagseguro_transaction')
        ->where('transaction_status="'. pSQL($status) . '"');

        $db_data = Db::getInstance()->executeS($sql);
        if (!is_array($db_data)) {
            $db_data = [];
        }

        return ObjectModel::hydrateCollection('AgPagseguroTransaction', $db_data);
    }

    
    /**
     * @return Order[]
     */
     public static function findOrdersWithoutTransaction()
     {
         $sql = new DbQuery;
         $sql->from('orders', 'o')
             ->select('o.*')
             ->leftJoin('agpagseguro_transaction', 't', 'o.id_order=t.id_order')
             ->where('o.module="agpagseguro"')
             ->where('t.id_order IS NULL')
             ->where('o.date_add >= "' . date('Y-m-d H:i:s', strtotime("-1 months")) . '"');
        
            //  p($sql->build());
         $db_data = Db::getInstance()->executeS($sql);
         if (!is_array($db_data)) {
             $db_data = [];
         }
 
         return ObjectModel::hydrateCollection('Order', $db_data);
     }

     public function saveTransactionData()
     {
        $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');
        if ($sandbox) {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
        } else {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
        }

        $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);
        $pagseguro_transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $this->transaction_code);
        $this->transaction_data = serialize($pagseguro_transaction);

        switch ($pagseguro_transaction->getPaymentMethod()->getType()->getValue()) {
            case 2:
                $type = 0;
                break;
            default:
                $type = $pagseguro_transaction->getPaymentMethod()->getType()->getValue();
            break;
        }
        $this->qty_installments = $pagseguro_transaction->getInstallmentCount();
        $this->transaction_reference = $pagseguro_transaction->getReference();
        $this->transaction_total = $pagseguro_transaction->getGrossAmount();
        $this->type = $type;
        $this->intermediation_cost = $pagseguro_transaction->getGrossAmount() - $pagseguro_transaction->getNetAmount();

        $this->save();
     }

    public function updatePsStatus()
    {
        $orderId = (int) $this->id_order;
        self::ensureLockLoaded();
        AgPagSeguroLock::withOrderLock($orderId, function () use ($orderId) {
            $ps_order = new Order($orderId);
            if (!Validate::isLoadedObject($ps_order)) {
                return;
            }

            if ($this->transaction_status == 'CANCELED' && $ps_order->hasBeenPaid()) {
                $status = "CANCELED_AFTER_PAYMENT";
            } else {
                $status = $this->transaction_status;
            }
            $ps_status = Configuration::get("AGPAGSEGURO_STATUS_{$status}");
            // "-1" é a opção "Não atualizar pedidos nesse estado"
            // Evita tentar setar estado inválido e gerar erro "status de novo pedido inválido"
            if ((int) $ps_status <= 0) {
                return;
            }

            if ($ps_status != $ps_order->current_state) {
                $history = $ps_order->getHistory(Context::getContext()->language->id, $ps_status);
                if (!$history) {
                    $ps_order->setCurrentState($ps_status);
                }
            }
        });
    }
}
