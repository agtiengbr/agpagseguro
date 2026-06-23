<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order as EntityOrder;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrder;
use Doctrine\ORM\EntityManagerInterface;

/**
 * Atualiza/cria uma AgpagseguroTransaction a partir do ORDER_ID do PagBank,
 * que é o identificador utilizado na API atual (endpoint GET /orders/{id}).
 *
 * Esta classe substitui o fluxo antigo baseado em "código da transação"
 * (charge id) implementado em UpdateChargeFromApi, que está deprecated.
 */
class UpdateTransactionFromOrderId
{
    use ApiApplicationTrait;

    /** @var GetOrder */
    private $infraService;

    /** @var EntityManagerInterface */
    private $em;

    public function __construct(GetOrder $infraService, EntityManagerInterface $em)
    {
        $this->infraService = $infraService;
        $this->em = $em;
    }

    /**
     * @param string $pagseguroOrderId Ex.: "ORDE_XXXXXXXX-XXXX-..."
     * @return AgpagseguroTransaction
     * @throws \Exception
     */
    public function exec($pagseguroOrderId)
    {
        $pagseguroOrderId = trim((string) $pagseguroOrderId);
        if ($pagseguroOrderId === '') {
            throw new \Exception("Informe o ORDER_ID do PagBank.");
        }

        $apiOrder = $this->infraService->exec((new EntityOrder)->setId($pagseguroOrderId));
        $this->postApiRequest($this->infraService->getRequest(), $this->em);

        if (!$apiOrder instanceof EntityOrder || !$apiOrder->getId()) {
            throw new \Exception("A transação não foi localizada junto à API do PagBank.");
        }

        // Verifica se já existe transação para esse ORDER_ID
        $transaction = $this->em->getRepository(AgpagseguroTransaction::class)
            ->findOneBy(['pagseguroOrderId' => $apiOrder->getId()]);

        if (!$transaction) {
            $transaction = new AgpagseguroTransaction;
            $this->em->persist($transaction);
        }

        $this->populateFromApiOrder($transaction, $apiOrder);

        $this->em->flush();

        return $transaction;
    }

    /**
     * Preenche os campos da transação a partir do objeto Order da API.
     */
    private function populateFromApiOrder(AgpagseguroTransaction $transaction, EntityOrder $apiOrder)
    {
        $transaction->setPagseguroOrderId($apiOrder->getId());

        if ($apiOrder->getReferenceId()) {
            $transaction->setTransactionReference($apiOrder->getReferenceId());
        }

        $charges = $apiOrder->getCharges();
        $qrCodes = $apiOrder->getQrCodes();

        if (is_array($charges) && count($charges)) {
            $charge = $charges[0];

            $transaction->setTransactionCode($charge->getId())
                ->setTransactionStatus($charge->getStatus());

            if ($charge->getAmount()) {
                $value = (int) $charge->getAmount()->getValue();
                $transaction->setValue($value)
                    ->setTransactionTotal($value / 100);
            }

            $paymentMethod = $charge->getPaymentMethod();
            if (is_array($paymentMethod)) {
                $transaction->setType($this->mapPaymentMethodType($paymentMethod));

                if (isset($paymentMethod['installments'])) {
                    $transaction->setQtyInstallments((int) $paymentMethod['installments']);
                }

                if (isset($paymentMethod['card']['brand'])) {
                    $transaction->setCardBrand($paymentMethod['card']['brand']);
                }

                // Boleto: link de pagamento
                if (isset($paymentMethod['boleto']['barcode'])) {
                    $transaction->setCopyText($paymentMethod['boleto']['barcode']);
                }
                if (isset($paymentMethod['boleto']['due_date'])) {
                    try {
                        $transaction->setExpirationDate(new \DateTime($paymentMethod['boleto']['due_date']));
                    } catch (\Exception $e) {
                        // ignora data inválida
                    }
                }
            }

            // Link de pagamento (quando disponível, ex.: boleto/pix via charge)
            $links = method_exists($charge, 'getLinks') ? $charge->getLinks() : null;
            if (is_array($links) && isset($links[0]['href'])) {
                $transaction->setTransactionLink($links[0]['href']);
            }
        } elseif (is_array($qrCodes) && count($qrCodes)) {
            // Pedido PIX (sem charges no momento da consulta)
            $qr = $qrCodes[0];

            $transaction->setType('3') // PIX
                ->setTransactionStatus('WAITING')
                ->setQtyInstallments(1);

            if ($qr->getAmount()) {
                $value = (int) $qr->getAmount()->getValue();
                $transaction->setValue($value)
                    ->setTransactionTotal($value / 100);
            }

            if ($qr->getText()) {
                $transaction->setCopyText($qr->getText());
            }

            if ($qr->getExpirationDate()) {
                try {
                    $transaction->setExpirationDate(new \DateTime($qr->getExpirationDate()));
                } catch (\Exception $e) {
                    // ignora data inválida
                }
            }

            $links = $qr->getLinks();
            if (is_array($links) && isset($links[0]['href'])) {
                $transaction->setTransactionLink($links[0]['href']);
            }
        } else {
            throw new \Exception("O pedido foi localizado, mas não possui charges ou qrCodes para extrair os dados da transação.");
        }
    }

    /**
     * Mapeia o type retornado pela API (CREDIT_CARD, BOLETO, etc.)
     * para o código interno usado pelo módulo.
     *
     * 0 = Boleto, 1 = Cartão de Crédito, 2 = Débito em Conta, 3 = PIX
     *
     * @param array $paymentMethod
     * @return string
     */
    private function mapPaymentMethodType(array $paymentMethod)
    {
        $apiType = isset($paymentMethod['type']) ? strtoupper($paymentMethod['type']) : '';

        switch ($apiType) {
            case 'BOLETO':
                return '0';
            case 'CREDIT_CARD':
                return '1';
            case 'DEBIT_CARD':
                return '2';
            case 'PIX':
                return '3';
            default:
                return '1';
        }
    }
}
