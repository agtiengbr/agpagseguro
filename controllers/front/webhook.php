<?php

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrder;

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

        $data = file_get_contents("php://input");
        $serializer = $this->get('agti.pagseguro.infrastructure.serializer.serializer');
        $order = $serializer->deserialize($data, Order::class, 'json');

        $processImmediately = (bool) Configuration::get('AGPAGSEGURO_WEBHOOK_PROCESS_IMMEDIATE');

        $obj = new AgPagSeguroWebhook;
        $obj->id_shop = $this->context->shop->id;
        $obj->notification_code = $order->getId();
        $obj->status = 0;

        $obj->add();

        if ($processImmediately) {
            try {
                $this->processWebhook($obj);
                $obj->status = 2;
                $obj->save();
            } catch (Exception $e) {
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

        exit();
    }

    protected function processNext()
    {
        // if (Configuration::get('AGPAGSEGURO_WATCHDOG') + 60 >= time()) {
        //     return;
        // }

        while(1) {
            try {
                Configuration::updateValue('AGPAGSEGURO_WATCHDOG', time());

                $next = AgPagSeguroWebhook::findNext();
                if (!$next) {
                    break;
                }

                $obj = new AgPagSeguroWebhook($next['id_agpagseguro_webhook']);
                AgClienteLogger::addLog("agpagseguro - Processando webhook #{$obj->id}", 1, null, null, null, true);

                if (Validate::isLoadedObject($obj)) {
                    $this->processWebhook($obj);
                    $obj->status = 2;
                    $obj->save();
                }
            } catch (Exception $e) {
                AgClienteLogger::addLog("agpagseguro - Erro processando webhook: " . $e->getMessage(), 3, null, null, null, true);
            }
            
            sleep(2);
        }
    }

    private function processWebhook($obj)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $class = $em->getRepository(AgpagseguroTransaction::class)->findOneBy(['pagseguroOrderId' => $obj->notification_code]);

        if (!$class) {
            return;
        }

        // Check if notification_code starts with "ORD"
        if (strpos($obj->notification_code, 'ORD') !== 0) {
            Logger::addLog("agpagseguro - notification_code inválido: {$obj->notification_code}", 2, null, 'AgPagSeguroWebhook', $obj->id, true);
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
}
