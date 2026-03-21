<?php

class agpagsegurosavetransactiondataModuleFrontController extends ModuleFrontController
{
    //ID = 66
    public function initContent()
    {
        set_time_limit(0);
        ignore_user_abort(true);
        
        $sql = new DbQuery;
        $sql->from('agpagseguro_transaction')
            ->where('qty_installments = ""');

        $db_data = Db::getInstance()->executeS($sql);

        // seller authentication
        $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');
        if ($sandbox) {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX');
        } else {
            $token = Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
        }

        foreach ($db_data as $transaction) {
            $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);
            $pagseguro_transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction['transaction_code']);
            $obj = new AgPagseguroTransaction($transaction['id_agpagseguro_transaction']);
            $obj->transaction_data = serialize($pagseguro_transaction);

            $obj->qty_installments = $pagseguro_transaction->getInstallmentCount();
            $obj->transaction_reference = $pagseguro_transaction->getReference();
            $obj->transaction_total = $pagseguro_transaction->getGrossAmount();
            $obj->intermediation_cost = $pagseguro_transaction->getGrossAmount() - $pagseguro_transaction->getNetAmount();

            $obj->save();
        }
    }
}