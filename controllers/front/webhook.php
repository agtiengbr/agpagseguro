<?php

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Classic\GetTransactionByNotificationCode;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrder;
use AGTI\PagSeguro\Infrastructure\Webhook\ClassicNotificationPayloadParser;
use AGTI\PagSeguro\Infrastructure\Webhook\ClassicTransactionStatusMapper;

if (!class_exists('AgPagSeguroLock', false)) {
    require_once _PS_MODULE_DIR_ . 'agpagseguro/lib/AgPagSeguroLock.php';
}

class AgPagSeguroWebhookModuleFrontController extends ModuleFrontController
{
    /**
     * initContent
     * Lock global do controlador usando SysV semaphore (sem_get) com chave derivada de chave "namespaced" do módulo.
     * - Garante exclusão mútua entre processos PHP (CLI, FPM, cron) no mesmo host.
     * - Não depende de escrita repetida em OrderHistory para evitar corrida.
     * - Fallback: se extensão sysvsem não estiver disponível, retorna 500 (poderíamos reintroduzir flock se desejado).
     * Observação: em ambiente distribuído (múltiplos containers) SysV não funciona entre hosts; seria necessário Redis ou DB.
     */
    public function initContent()
    {
        set_time_limit(0);
        ignore_user_abort(true);

        // LOCK VIA SysV SEMAPHORE
        if (!function_exists('sem_get')) {
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Sem suporte a sysvsem (sem_get) para lock de webhook';
            exit();
        }
        $semKey = AgPagSeguroLock::semaphoreKey('agpagseguro:webhook:global');
        $maxAcquire = 1; // exclusão mútua
        $permissions = 0666; // permissões para múltiplos usuários do processo web
        $autoRelease = 1; // auto release on request end
        $sem = @sem_get($semKey, $maxAcquire, $permissions, $autoRelease);
        if (!$sem) {
            header('HTTP/1.1 500 Internal Server Error');
            echo 'Falha ao criar/obter semaphore';
            exit();
        }
        // Tenta adquirir sem bloqueio prolongado; se não conseguir rapidamente responde 423
        $acquired = @sem_acquire($sem, true); // modo non-blocking (a partir de PHP 8.1). Se versão mais antiga, isso devolverá false.
        if (!$acquired) {
            header('HTTP/1.1 423 Locked');
            echo 'Processo de webhook já em execução';
            exit();
        }
        // Garantir liberação ao final (caso autoRelease não funcione em alguma versão)
        register_shutdown_function(function() use ($sem) {
            @sem_release($sem);
        });

        if (Tools::getValue('process_next')) {
            $this->processNext();
            exit();
        }

        $data = file_get_contents('php://input');
        $contentType = isset($_SERVER['CONTENT_TYPE'])
            ? strtolower(trim(explode(';', (string) $_SERVER['CONTENT_TYPE'], 2)[0]))
            : '';
        $parser = new ClassicNotificationPayloadParser();

        if ($contentType === 'application/x-www-form-urlencoded') {
            try {
                $notificationCode = $parser->parse($data);
            } catch (\InvalidArgumentException $e) {
                $this->respond(400, 'Invalid classic notification payload.');
            }
        } else {
            $serializer = $this->get('agti.pagseguro.infrastructure.serializer.serializer');
            try {
                $order = $serializer->deserialize($data, Order::class, 'json');
                $notificationCode = $order instanceof Order ? $order->getId() : null;
            } catch (\Throwable $e) {
                $this->respond(400, 'Invalid Order webhook payload.');
            }

            if (!ClassicNotificationPayloadParser::isOrderId($notificationCode)) {
                $this->respond(400, 'Invalid Order webhook identifier.');
            }
        }

        $processImmediately = (bool) Configuration::get('AGPAGSEGURO_WEBHOOK_PROCESS_IMMEDIATE');
        $shopId = (int) $this->context->shop->id;

        $existing = AgPagSeguroWebhook::findByNotificationCode($notificationCode, $shopId);
        if ($existing) {
            $duplicate = new AgPagSeguroWebhook((int) $existing['id_agpagseguro_webhook']);
            if (Validate::isLoadedObject($duplicate) && (int) $duplicate->status === 1) {
                $duplicate->status = 0;
                if (!$duplicate->save()) {
                    $this->respond(500, 'Unable to requeue notification.');
                }
            }

            $this->respond(200);
        }

        $obj = new AgPagSeguroWebhook;
        $obj->id_shop = $shopId;
        $obj->notification_code = $notificationCode;
        $obj->status = 0;

        if (!$obj->add()) {
            $this->respond(500, 'Unable to enqueue notification.');
        }

        if ($processImmediately) {
            try {
                $this->processWebhook($obj);
                $obj->status = 2;
                if (!$obj->save()) {
                    throw new \RuntimeException('Unable to mark notification as processed.');
                }
            } catch (\Throwable $e) {
                $obj->status = 0;
                $obj->save();
                AgClienteLogger::addLog(
                    "agpagseguro - Erro processando webhook imediatamente #{$obj->id}: " . $e->getMessage(),
                    3,
                    null,
                    null,
                    null,
                    true
                );
            }
        }

        $this->respond(200);
    }

    protected function processNext()
    {
        // if (Configuration::get('AGPAGSEGURO_WATCHDOG') + 60 >= time()) {
        //     return;
        // }

        $excludedIds = [];
        while (true) {
            $obj = null;
            $webhookId = 0;
            try {
                Configuration::updateValue('AGPAGSEGURO_WATCHDOG', time());

                $next = AgPagSeguroWebhook::findNext($excludedIds);
                if (!$next) {
                    break;
                }

                $webhookId = (int) $next['id_agpagseguro_webhook'];
                if ($webhookId <= 0) {
                    break;
                }

                $obj = new AgPagSeguroWebhook($webhookId);
                AgClienteLogger::addLog("agpagseguro - Processando webhook #{$obj->id}", 1, null, null, null, true);

                if (Validate::isLoadedObject($obj)) {
                    $this->processWebhook($obj);
                    $obj->status = 2;
                    if (!$obj->save()) {
                        throw new \RuntimeException('Unable to mark notification as processed.');
                    }
                } else {
                    AgClienteLogger::addLog("agpagseguro - Webhook #{$webhookId} não pôde ser carregado.", 3, null, null, null, true);
                    $excludedIds[] = $webhookId;
                }

                sleep(2);
            } catch (\Throwable $e) {
                if ($obj && Validate::isLoadedObject($obj)) {
                    $obj->status = 0;
                    $obj->save();
                }

                AgClienteLogger::addLog("agpagseguro - Erro processando webhook: " . $e->getMessage(), 3, null, null, null, true);

                if ($webhookId <= 0) {
                    break;
                }

                $excludedIds[] = $webhookId;
                sleep(2);
            }
        }
    }

    private function processWebhook($obj)
    {
        if (ClassicNotificationPayloadParser::isClassicNotificationCode($obj->notification_code)) {
            $this->processClassicNotification($obj);
            return;
        }

        if (!ClassicNotificationPayloadParser::isOrderId($obj->notification_code)) {
            Logger::addLog("agpagseguro - notification_code inválido: {$obj->notification_code}", 2, null, 'AgPagSeguroWebhook', $obj->id, true);
            return;
        }

        $this->processOrderWebhook($obj);
    }

    private function processClassicNotification($obj)
    {
        /** @var GetTransactionByNotificationCode $remote */
        $remote = $this->get(GetTransactionByNotificationCode::class);
        $details = $remote->exec($obj->notification_code);

        $transaction = \AgPagseguroTransaction::getByTransaction($details['transactionCode']);
        if (!Validate::isLoadedObject($transaction)) {
            Logger::addLog(
                "agpagseguro - Notificação clássica {$obj->notification_code} refere-se à transação {$details['transactionCode']}, sem transação local correspondente.",
                2,
                null,
                'AgPagSeguroWebhook',
                $obj->id,
                true
            );
            return;
        }

        $mappedStatus = ClassicTransactionStatusMapper::toModuleStatus($details['status']);
        $transaction->transaction_status = $mappedStatus ?? $details['status'];
        if (!$transaction->update()) {
            throw new \RuntimeException('Unable to persist PagBank classic transaction status.');
        }

        if ($mappedStatus === null) {
            Logger::addLog(
                "agpagseguro - Status clássico {$details['status']} sem mapeamento; pedido {$transaction->id_order} mantido sem alteração.",
                2,
                null,
                'AgPagSeguroWebhook',
                $obj->id,
                true
            );
            return;
        }

        $transaction->updatePsStatus();
    }

    private function processOrderWebhook($obj)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $class = $em->getRepository(AgpagseguroTransaction::class)->findOneBy(['pagseguroOrderId' => $obj->notification_code]);

        if (!$class) {
            return;
        }

        /** @var GetOrder */
        $remote = new Order;
        $remote->setId($obj->notification_code);

        $s = $this->get(GetOrder::class);
        $order = $s->exec($remote);

        if (!$order->getCharges()) {
            return;
        }

        $charge = $order->getCharges()[0];

        //busca a transação do pagseguro que possui o ID remoto igual ao notification_code do webhook
        $transaction = $em->getRepository(AgpagseguroTransaction::class)->findOneBy(['pagseguroOrderId' => $obj->notification_code]);
        $transaction->setTransactionCode($charge->getId());
        $transaction->setTransactionStatus($charge->getStatus());
        $em->flush();

        $order = $transaction->getOrder();
        if (is_null($order)) {
            return;
        }
        
        $psOrder = new \Order($order->getId());


        if ($charge->getStatus() == 'CANCELED' && $psOrder->hasBeenPaid()) {
            $status = 'CANCELED_AFTER_PAYMENT';
        } else {
            $status = $charge->getStatus();
        }

        $status = Configuration::get('AGPAGSEGURO_STATUS_' . $status);
        if ((int) $status <= 0) {
            return;
        }

        

        $orderId = (int) $psOrder->id;
        AgPagSeguroLock::withOrderLock($orderId, function () use ($orderId, $status) {
            $psOrder = new \Order($orderId);
            if (!Validate::isLoadedObject($psOrder)) {
                return;
            }

            $history = $psOrder->getHistory(Context::getContext()->language->id, $status);
            if (count($history)) {
                return;
            }

            $psOrder->setCurrentState($status);
        });
    }

    private function respond($statusCode, $message = '')
    {
        http_response_code((int) $statusCode);
        if ($message !== '') {
            echo $message;
        }
        exit();
    }
}
