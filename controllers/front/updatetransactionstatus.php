<?php
class agpagseguroupdatetransactionstatusModuleFrontController extends ModuleFrontController
{
    private function parsePsDateTime($value)
    {
        $value = is_string($value) ? trim($value) : '';
        if ($value === '' || $value === '0000-00-00 00:00:00') {
            return null;
        }

        $dt = \DateTimeImmutable::createFromFormat('Y-m-d H:i:s', $value);
        if ($dt instanceof \DateTimeImmutable) {
            return $dt;
        }

        try {
            return new \DateTimeImmutable($value);
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function toDateTimeImmutable($dt)
    {
        if ($dt instanceof \DateTimeImmutable) {
            return $dt;
        }

        if ($dt instanceof \DateTime) {
            return \DateTimeImmutable::createFromMutable($dt);
        }

        if ($dt instanceof \DateTimeInterface) {
            try {
                return new \DateTimeImmutable($dt->format(\DateTime::ATOM));
            } catch (\Throwable $e) {
                return null;
            }
        }

        return null;
    }

    public function initContent()
    {
        AgClienteLogger::createLogger(_PS_MODULE_DIR_ . 'agpagseguro/logs/update_transaction_status.txt', 1);
        
        AgClienteLogger::addLog("Iniciando rotina de atualização do estado das transações.");

        // Status pendentes (compatível com versões antigas e novas)
        $pendingStatuses = [
            '',
            '0', // legado
            '1', // legado
            '2', // legado
            'INITIATED',
            'WAITING',
            'IN_ANALYSIS',
            'AUTHORIZED',
        ];

        /** @var AgPagseguroTransaction[] $transactions */
        $transactions = [];
        foreach ($pendingStatuses as $status) {
            // Cast para array para compatibilidade com analisadores estáticos (ex.: Intelephense)
            $transactions = array_merge($transactions, (array) AgPagseguroTransaction::getByStatus($status));
        }

        // Dedup por id (protege contra merges duplicados)
        $dedup = [];
        foreach ($transactions as $t) {
            $dedup[(int) $t->id_agpagseguro_transaction] = $t;
        }
        $transactions = array_values($dedup);

        // Serviços da API nova (PagBank)
        $getOrderService = null;
        $getOrderByChargeService = null;
        try {
            $getOrderService = $this->get(\AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrder::class);
        } catch (\Throwable $e) {
            $getOrderService = null;
        }
        try {
            $getOrderByChargeService = $this->get(\AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrderByCharge::class);
        } catch (\Throwable $e) {
            $getOrderByChargeService = null;
        }

        usort($transactions, function($t1, $t2) {
            $d1 = DateTime::createFromFormat('Y-m-d H:i:s', $t1->date_upd);
            $d2 = DateTime::createFromFormat('Y-m-d H:i:s', $t2->date_upd);

            if ($d1 > $d2) {
                return 1;
            }

            if ($d1 < $d2) {
                return -1;
            }
            return 0;
        });

        AgClienteLogger::addLog(count($transactions) . " transações encontradas.");
        foreach ($transactions as $transaction) {
            try {
                $localId = (int) $transaction->id_agpagseguro_transaction;
                $orderId = (int) $transaction->id_order;
                AgClienteLogger::addLog("Processando transação #{$localId} do pedido #{$orderId}.");

                $remoteOrderId = null;
                $remoteChargeId = null;
                $remoteStatus = null;
                $changed = false;

                $now = new \DateTimeImmutable('now');

                // Preferencial: buscar por `pagseguro_order_id` (ORD...) via GetOrder
                if ($getOrderService && $transaction->pagseguro_order_id && strpos((string) $transaction->pagseguro_order_id, 'ORD') === 0) {
                    $remote = new \AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
                    $remote->setId($transaction->pagseguro_order_id);
                    $order = $getOrderService->exec($remote);

                    $httpCode = 0;
                    try {
                        $httpCode = (int) $getOrderService->getRequest()->getHttpCode();
                    } catch (\Throwable $e) {}

                    if ($httpCode === 404) {
                        AgClienteLogger::addLog("Order {$transaction->pagseguro_order_id} não encontrado na API (404). Marcando como CANCELED para não reprocessar.", 2);
                        $remoteStatus = 'CANCELED';
                        $remoteOrderId = $transaction->pagseguro_order_id;
                    } elseif ($httpCode >= 400) {
                        AgClienteLogger::addLog("Erro ao buscar order {$transaction->pagseguro_order_id} na API (HTTP {$httpCode}); ignorando.", 2);
                        continue;
                    } elseif (!$order) {
                        AgClienteLogger::addLog("API não retornou dados ao buscar order {$transaction->pagseguro_order_id}; ignorando.", 2);
                        continue;
                    } elseif (!$order->getCharges()) {
                        $expiration = $this->parsePsDateTime($transaction->expiration_date);
                        if (!$expiration && $order->getQrCodes() && isset($order->getQrCodes()[0]) && $order->getQrCodes()[0]->getExpirationDate()) {
                            $exp = $order->getQrCodes()[0]->getExpirationDate();
                            $expiration = $this->toDateTimeImmutable($exp);
                            if ($expiration) {
                                $transaction->expiration_date = $expiration->format('Y-m-d H:i:s');
                                $changed = true;
                            }
                        }

                        if ($expiration && $expiration <= $now) {
                            AgClienteLogger::addLog("Transação expirada (order {$transaction->pagseguro_order_id}, expira em {$expiration->format('Y-m-d H:i:s')}). Sem charges; marcando como CANCELED.", 1);
                            $remoteStatus = 'CANCELED';
                            $remoteOrderId = $order->getId();
                        } else {
                            AgClienteLogger::addLog("Sem charges ao buscar order {$transaction->pagseguro_order_id}; ignorando.", 2);
                            if ($changed) {
                                $transaction->save();
                            }
                            continue;
                        }
                    } else {
                        $charge = $order->getCharges()[0];
                        $remoteOrderId = $order->getId();
                        $remoteChargeId = $charge->getId();
                        $remoteStatus = $charge->getStatus();
                    }

                }
                // Alternativa: buscar por chargeId (transaction_code) via GetOrderByCharge
                elseif ($getOrderByChargeService && $transaction->transaction_code) {
                    $r = $getOrderByChargeService->exec($transaction->transaction_code);

                    if ($r instanceof \AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrderByChargeResponseSuccess && count($r->getOrders())) {
                        $order = $r->getOrders()[0];
                        if (!$order->getCharges()) {
                            $expiration = $this->parsePsDateTime($transaction->expiration_date);
                            if (!$expiration && $order->getQrCodes() && isset($order->getQrCodes()[0]) && $order->getQrCodes()[0]->getExpirationDate()) {
                                $exp = $order->getQrCodes()[0]->getExpirationDate();
                                $expiration = $this->toDateTimeImmutable($exp);
                                if ($expiration) {
                                    $transaction->expiration_date = $expiration->format('Y-m-d H:i:s');
                                    $changed = true;
                                }
                            }

                            if ($expiration && $expiration <= $now) {
                                AgClienteLogger::addLog("Transação expirada (charge {$transaction->transaction_code}, expira em {$expiration->format('Y-m-d H:i:s')}). Sem charges; marcando como CANCELED.", 1);
                                $remoteStatus = 'CANCELED';
                                $remoteOrderId = $order->getId();
                            } else {
                                AgClienteLogger::addLog("Sem charges ao buscar por charge {$transaction->transaction_code}; ignorando.", 2);
                                if ($changed) {
                                    $transaction->save();
                                }
                                continue;
                            }
                        }

                        if ($order->getCharges()) {
                            $charge = $order->getCharges()[0];
                            $remoteOrderId = $order->getId();
                            $remoteChargeId = $charge->getId();
                            $remoteStatus = $charge->getStatus();
                        }
                    } else {
                        AgClienteLogger::addLog("API nova não retornou order para charge {$transaction->transaction_code}; ignorando.", 2);
                        continue;
                    }
                } else {
                    // Fallback opcional (SDK antiga), apenas se existir no ambiente
                    if (class_exists('PagSeguroAccountCredentials', false) && class_exists('PagSeguroTransactionSearchService', false)) {
                        $sandbox = Configuration::get('AGPAGSEGURO_SANDBOX');
                        $token = $sandbox ? Configuration::get('AGPAGSEGURO_TOKEN_SANDBOX') : Configuration::get('AGPAGSEGURO_TOKEN_PRODUCTION');
                        $credentials = new PagSeguroAccountCredentials(Configuration::get('AGPAGSEGURO_EMAIL'), $token);
                        $pagseguro_transaction = PagSeguroTransactionSearchService::searchByCode($credentials, $transaction->transaction_code);
                        $remoteStatus = $pagseguro_transaction->getStatus()->getValue();
                    } else {
                        AgClienteLogger::addLog("Sem identificador compatível (pagseguro_order_id/chargeId) e SDK antiga indisponível; ignorando.", 2);
                        continue;
                    }
                }

                if ($remoteOrderId && !$transaction->pagseguro_order_id) {
                    $transaction->pagseguro_order_id = $remoteOrderId;
                    $changed = true;
                }

                if ($remoteChargeId && $remoteChargeId !== $transaction->transaction_code) {
                    $transaction->transaction_code = $remoteChargeId;
                    $changed = true;
                }

                if ($remoteStatus && $remoteStatus !== $transaction->transaction_status) {
                    AgClienteLogger::addLog("Atualizando status {$transaction->transaction_status} -> {$remoteStatus}.");
                    $transaction->transaction_status = $remoteStatus;
                    $changed = true;
                } else {
                    AgClienteLogger::addLog("Sem atualização de status; mantendo {$transaction->transaction_status}.");
                }

                // Sempre salva para atualizar date_upd e registrar processamento
                $transaction->save();

                if ($changed) {
                    AgClienteLogger::addLog("Atualizando estado do pedido.");
                }
                $transaction->updatePsStatus();
            } catch (\Throwable $e) {
                AgClienteLogger::addLog($e->getMessage(), 3);
            }
        }

        AgClienteLogger::addLog("Encerrando rotina de atualização do estado das transações.");
        exit();
    }
}
