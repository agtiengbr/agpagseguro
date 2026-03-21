<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Application\Exception\HttpCodeException;
use AGTI\PagSeguro\Application\Exception\PaymentDeclinedException;
use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CreateOrder;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CreateOrderResponseSuccess;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Address;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Amount;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Boleto;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Card;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Charge;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Customer;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\InstructionLines;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Item;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\PaymentHolder;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\PaymentMethodCard;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\PaymentMethodTicket;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Phone;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\QrCode;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Shipping;
use AGTI\PagSeguro\ValueObject\Configuration;
use Doctrine\ORM\EntityManagerInterface;

class CreateCreditCardOrder
{
    use ApiApplicationTrait;

    private $infraService;
    private $em;
    private $config;

    public function __construct(CreateOrder $infraService, EntityManagerInterface $em, Configuration $config)
    {
        $this->infraService = $infraService;
        $this->em = $em;
        $this->config = $config;
    }

    public function exec(\Cart $cart, $installments, $cardToken, $holderName, $holderTaxId, $notificationUrl)
    {
        $psCustomer = new \Customer($cart->id_customer);

        $psAddress = new \Address($cart->id_address_invoice);
        $phone = $psAddress->phone_mobile ? $psAddress->phone_mobile : $psAddress->phone;
        $phone = preg_replace("/[^0-9]/", "", $phone);


        //array de produtos
        $items = [];
        foreach ($cart->getProducts() as $prod) {
            $maskedName = $this->getFeatureValue($prod['id_product'], 'MASKED_NAME');
            $productName = $maskedName ?: $prod['name'];

            $items[] = (new Item)
                ->setReferenceId($prod['reference'] ? $prod['reference'] : $prod['id_product'])
                ->setName($productName)
                ->setQuantity($prod['cart_quantity'])
                ->setUnitAmount((int) (100 * $prod['price_wt']));
        }


        $customerData = \AgColumnMapping::getCustomerDocument(
            $this->config->getCpfMapping(),
            $this->config->getCnpjMapping(),
            $this->config->getSocialNameMapping(),
            $psCustomer
        );
        $pagbankCustomer = 
        (new Customer)
        ->setEmail($psCustomer->email)
        ->setName("{$psCustomer->firstname} {$psCustomer->lastname}")
        ->setPhones([
            (new Phone)
                ->setCountry(55)
                ->setArea(substr($phone, 0, 2))
                ->setNumber(substr($phone, 2))
                ->setType('MOBILE')
        ]);

        if ($customerData['cnpj']) {
            $pagbankCustomer->setTaxId(preg_replace("/[^0-9]/", "", $customerData['cnpj']))
                ->setName($customerData['company']);
        } else {
            $pagbankCustomer->setTaxId(preg_replace("/[^0-9]/", "", $customerData['cpf']))
                ->setName($customerData['name']);
        }
        
        $order = new Order;
        $order->setReferenceId(uniqid())
            ->setCustomer($pagbankCustomer)
            ->setItems($items)
            ->setShipping(
                (new Shipping)
                    ->setAddress(
                        (new Address)
                            ->setStreet($psAddress->address1)
                            ->setNumber('123')
                            ->setLocality($psAddress->address2)
                            ->setCity($psAddress->city)
                            ->setRegionCode('SP')
                            ->setRegion('SP')
                            ->setCountry('BRA')
                            ->setPostalCode(preg_replace("/[^0-9]/", "", $psAddress->postcode))
                    )
            )
            ->setNotificationUrls([
                $notificationUrl
            ])
            ->setCharges([
                (new Charge)
                    ->setReferenceId(uniqid())
                    ->setDescription('Carrinho ' . $cart->id)
                    ->setAmount(
                        (new Amount)
                        ->setValue((int)(100 * $cart->getOrderTotal()))
                        ->setCurrency('BRL')
                    )
                    ->setPaymentMethod(
                        (new PaymentMethodCard)
                            ->setInstallments($installments)
                            ->setCapture(true)
                            ->setCard(
                                (new Card)
                                    ->setEncrypted($cardToken)
                                    ->setStore(false)
                            )
                            ->setHolder(
                                (new PaymentHolder)
                                    ->setName($holderName)
                                    ->setTaxId($holderTaxId)
                            )
                    )
            ]);

        /** @var CreateOrderResponseSuccess */
        $r = $this->infraService->exec($order);
        try {
            $this->postApiRequest($this->infraService->getRequest(), $this->em);

            if ($r instanceof CreateOrderResponseSuccess) {
                $transaction = new AgpagseguroTransaction;

                // Tenta obter a bandeira do cartão — prioriza o valor enviado pelo front
                $cardBrand = $cardBrandFromFront ?: null;
                $charges = $r->getOrder()->getCharges();
                if (isset($charges[0]) && method_exists($charges[0], 'getPaymentMethod') && $charges[0]->getPaymentMethod() && isset($charges[0]->getPaymentMethod()['card'])) {
                    $card = $charges[0]->getPaymentMethod()['card'];
                    $cardBrand = $card['brand'];
                }

                $transaction->setTransactionReference($r->getOrder()->getReferenceId())
                    ->setTransactionCode($r->getOrder()->getCharges()[0]->getId())
                    ->setTransactionStatus($r->getOrder()->getCharges()[0]->getStatus())
                    ->setType("1")
                    // ->setTransactionData()
                    ->setQtyInstallments($installments)
                    ->setTransactionTotal($r->getOrder()->getCharges()[0]->getAmount()->getValue()/100)
                    ->setPagseguroOrderId($r->getOrder()->getId())
                    // ->setIntermediationCost()
                    // ->setTransactionLink()
                    ->setCardBrand($cardBrand)
                    ;
                $this->em->persist($transaction);
                $this->em->flush();


                if ($r->getOrder()->getCharges()[0]->getStatus() == 'DECLINED') {
                    $e = new PaymentDeclinedException($r->getOrder()->getCharges()[0]->getPaymentResponse()->getMessage());
                    $e->setTransaction($transaction);

                    throw $e;
                }
                
                return $transaction;
            }
        } catch (HttpCodeException $e) {
            return $r;
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