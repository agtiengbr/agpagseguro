<?php

class agpagseguroupdatetransactiondataModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        
        /** @var AgClienteWorker */
        $id_worker = Tools::getValue('id_agworker');

        Logger::addLog("teste - agworker = {$id_worker}", 1, null, null, null, true);

        global $agti_worker;
        $agti_worker = new AgClienteWorker($id_worker);
        $agti_worker->save();
        
        $dbPrefix = _DB_PREFIX_;
        Db::getInstance()->execute(
            "
            update {$dbPrefix}agpagseguro_transaction t
            INNER JOIN {$dbPrefix}orders o ON o.id_order = t.id_order
            INNER JOIN {$dbPrefix}order_payment op ON op.order_reference = o.reference
            SET op.transaction_id = t.transaction_code
            WHERE (op.transaction_id = '' OR op.transaction_id IS NULL) AND t.transaction_status IN ('PAID');
            "
        );

        /** @var array[AgPagseguroTransaction] */
        $transactions = array_merge(
            AgPagseguroTransaction::getByStatus(""),
            AgPagseguroTransaction::getByStatus("AUTHORIZED"),
            AgPagseguroTransaction::getByStatus("IN_ANALYSIS"),
            AgPagseguroTransaction::getByStatus("WAITING")
        );

        foreach ($transactions as $transaction) {
            if ($transaction->pagseguro_order_id) {
                // Check if there is already an unprocessed webhook with the same notification_code
                $existingWebhook = Db::getInstance()->getValue(
                    'SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'agpagseguro_webhook 
                    WHERE notification_code = "' . pSQL($transaction->pagseguro_order_id) . '" 
                    AND status = 0'
                );

                if ($existingWebhook == 0) {
                    // Insert a webhook for the transaction to be analyzed later
                    $wh = new AgPagSeguroWebhook;

                    $wh->notification_code = $transaction->pagseguro_order_id;
                    $wh->id_shop = $this->context->shop->id;
                    $wh->status = 0;

                    $wh->add();
                }
            }
        }

        exit();
    }
}