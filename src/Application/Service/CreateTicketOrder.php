<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CreateOrder;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CreateOrderResponseSuccess;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Address;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Amount;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Boleto;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Charge;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Customer;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\InstructionLines;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Item;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\PaymentHolder;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\PaymentMethodTicket;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Phone;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Shipping;
use Doctrine\ORM\EntityManagerInterface;
use AGTI\PagSeguro\ValueObject\Configuration;

class CreateTicketOrder
{
    use ApiApplicationTrait;

    private $infraService;
    private $em;
    private $config;

    public function __construct(CreateOrder $infraService, EntityManagerInterface $em, Configuration $configuration)
    {
        $this->infraService = $infraService;
        $this->em = $em;
        $this->config = $configuration;
    }

    public function exec(\Cart $cart, $notificationUrl)
    {
        $psCustomer = new \Customer($cart->id_customer);

        $psAddress = new \Address($cart->id_address_invoice);
        $phone = $psAddress->phone_mobile ? $psAddress->phone_mobile : $psAddress->phone;
        $phone = preg_replace("/[^0-9]/", "", $phone);


        $customerData = \AgColumnMapping::getCustomerDocument(
            $this->config->getCpfMapping(),
            $this->config->getCnpjMapping(),
            $this->config->getSocialNameMapping(),
            $psCustomer
        );

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
                (new PaymentMethodTicket)
                    ->setBoleto(
                        (new Boleto)
                            ->setDueDate(new \DateTime('+1 day'))
                            ->setInstructionLines(
                                (new InstructionLines)
                                    ->setLine1('linha1')
                                    ->setLine2('linha2')
                            )
                            ->setHolder(
                                (new PaymentHolder)
                                    ->setEmail($psCustomer->email)
                                    ->setName($psCustomer->firstname . ' ' . $psCustomer->lastname)
                                    ->setTaxId($pagbankCustomer->getTaxId())
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
                    )
            )
        ]);

        /** @var CreateOrderResponseSuccess */
        $r = $this->infraService->exec($order);
        $this->postApiRequest($this->infraService->getRequest(), $this->em);

        if ($r instanceof CreateOrderResponseSuccess) {
            $transaction = new AgpagseguroTransaction;

            $transaction->setTransactionReference($r->getOrder()->getReferenceId())
                ->setTransactionCode($r->getOrder()->getCharges()[0]->getId())
                ->setTransactionStatus($r->getOrder()->getCharges()[0]->getStatus())
                ->setType(0)
                // ->setTransactionData()
                ->setQtyInstallments(1)
                ->setTransactionTotal($r->getOrder()->getCharges()[0]->getAmount()->getValue()/100)
                // ->setIntermediationCost()
                ->setTransactionLink($r->getOrder()->getCharges()[0]->getLinks()[0]['href'])
                ->setCopyText($r->getOrder()->getCharges()[0]->getPaymentMethod()['boleto']['barcode'])
                ->setPagseguroOrderId($r->getOrder()->getId())
                ;

            $this->em->persist($transaction);
            $this->em->flush();

            return $transaction;
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