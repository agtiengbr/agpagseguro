<?php

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrder;

class agpagsegurocheckMissingTransactionsModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        PrestaShopLogger::addLog('agpagseguro: Iniciando verificação de transações faltantes.', 1, null, 'agpagsegurocheckMissingTransactionsModuleFrontController', null, true);

        $requests = Db::getInstance()->executeS(
            "SELECT * FROM " . _DB_PREFIX_ . "agpagseguro_request where (analyzed=0 or analyzed IS NULL) AND method='POST' and endpoint like '%api.pagseguro.com/orders%' and response != '' and response != 'internal server error' and http_code=201 and date_add < '" . date('Y-m-d H:i:s', strtotime('-20 minutes')) . "'"
        );

        PrestaShopLogger::addLog('agpagseguro: Número de requisições encontradas: ' . count($requests), 1, null, 'agpagsegurocheckMissingTransactionsModuleFrontController', null, true);

        $serializer = $this->get('agti.pagseguro.infrastructure.serializer.serializer');
        $em = $this->get('doctrine.orm.entity_manager');
        foreach ($requests as $r) {
            try {
                PrestaShopLogger::addLog('agpagseguro: Processando requisição ID: ' . $r['id_agpagseguro_request'], 1, null, 'AgPagseguroApiRequest', (int)$r['id_agpagseguro_request'], true);

                // Obtém o ID da order
                $localOrder = $serializer->deserialize($r['response'], Order::class, 'json');

                // Verifica se a order já existe na base local
                $ettTransaction = $em->getRepository(AgpagseguroTransaction::class)->findOneBy(['pagseguroOrderId' => $localOrder->getId()]);

                if (!is_null($ettTransaction)) {
                    //verifica se a transação não foi negada
                    if ($ettTransaction->getTransactionStatus() == 'DECLINED') {
                        continue;
                    }

                    // Verifica se a transação está vinculada a um ORDER
                    $ettOrder = $ettTransaction->getOrder();
                    if (!is_null($ettOrder)) {
                        Db::getInstance()->update('agpagseguro_request', ['analyzed' => 1], 'id_agpagseguro_request=' . (int)$r['id_agpagseguro_request']);
                        PrestaShopLogger::addLog('agpagseguro: Transação vinculada a um pedido. Requisição ID: ' . $r['id_agpagseguro_request'] . ' marcada como analisada.', 1, null, 'AgPagseguroApiRequest', (int)$r['id_agpagseguro_request'], true);
                        continue;
                    } else {
                        PrestaShopLogger::addLog('agpagseguro: Transação não vinculada a um pedido. Requisição ID: ' . $r['id_agpagseguro_request'] . ' não marcada como analisada.', 1, null, 'AgPagseguroApiRequest', (int)$r['id_agpagseguro_request'], true);
                    }
                } else {
                    PrestaShopLogger::addLog('agpagseguro: Transação não encontrada na base local. Requisição ID: ' . $r['id_agpagseguro_request'], 1, null, 'AgPagseguroApiRequest', (int)$r['id_agpagseguro_request'], true);
                }

                // Envia email de alerta
                $emails = explode(',', Configuration::get('AGPAGSEGURO_EMAILS_ALERT_MISSING_TRANSACTIONS'));
                $data = [
                    '{idRequest}' => $r['id_agpagseguro_request'],
                    '{orderId}' => $r['pagseguro_order_id']
                ];

                foreach ($emails as $email) {
                    Mail::Send(
                        Context::getContext()->language->id,
                        'missingTransactions',
                        'Erro ao salvar o pagamento',
                        $data,
                        $email,
                        '',
                        null,
                        null,
                        null,
                        null,
                        _PS_MODULE_DIR_ . 'agpagseguro/mails/'
                    );
                }
                Db::getInstance()->update('agpagseguro_request', ['analyzed' => 1], 'id_agpagseguro_request=' . (int)$r['id_agpagseguro_request']);
                PrestaShopLogger::addLog('agpagseguro: Email de alerta enviado e requisição ID: ' . $r['id_agpagseguro_request'] . ' marcada como analisada.', 1, null, 'AgPagseguroApiRequest', (int)$r['id_agpagseguro_request'], true);
            } catch (Exception $e) {
                PrestaShopLogger::addLog('agpagseguro: Erro ao processar requisição ID: ' . $r['id_agpagseguro_request'] . '. Erro: ' . $e->getMessage(), 3, null, 'AgPagseguroApiRequest', (int)$r['id_agpagseguro_request'], true);
                dump($e);
            }
        }
        PrestaShopLogger::addLog('agpagseguro: Verificação de transações faltantes concluída.', 1, null, 'agpagsegurocheckMissingTransactionsModuleFrontController', null, true);
        dump($requests);
        exit();
    }
}