<?php

use AGTI\PagSeguro\Application\Service\CreatePixOrder;
use AGTI\PagSeguro\Application\Service\CreateTicketOrder;
use AGTI\PagSeguro\Application\Service\GetCardKey as ServiceGetCardKey;
use AGTI\PagSeguro\Application\Service\UpdateChargeFromApi;
use AGTI\PagSeguro\Application\Service\UpdateMovementsPreviousDay;
use AGTI\PagSeguro\Entity\Orders;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate\CalculateChargesService;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Charge\Calculate\CalculateChargesServiceArgs;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CreateOrder;
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
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrder;
use AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey\CreateCardKey;
use AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey\GetCardKey;

class agpagsegurotestModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();
        // $this->GetOrder();
        // $this->UpdateChargeFromApi();
        // $this->CreateOrder();
        // $this->createTicketOrder();
        // $this->createPixOrder();

        // $this->createTicketOrderApplication();
        // $this->createPixOrderApplication();

        // $this->getPublicKey();
        // $this->createPublicKey();
        // $this->appGetPublicKey();

        $this->updateEdi();
        // $this->simulateInstallments();

        exit();
    }

    private function CreateOrder()
    {
        /** @var CreateOrder */
        $s = $this->get(CreateOrder::class);
        $s->setToken("15C7D97A9D964D9EB8039271D24A1E15");
        $order = new Order;
        $order->setReferenceId('teste')
            ->setCustomer(
                (new Customer)
                ->setEmail('test@example.com')
                ->setTaxId('22649835150')
                ->setName('joao da silva')
                ->setPhones([
                (new Phone)
                    ->setCountry(55)
                    ->setArea(14)
                    ->setNumber(981914711)
                    ->setType('MOBILE')
                ])
            )
            ->setItems([
                (new Item)
                    ->setReferenceId('teste')
                    ->setName('teste')
                    ->setQuantity(1)
                    ->setUnitAmount(10)
            ])
            ->setShipping(
                (new Shipping)
                    ->setAddress(
                        (new Address)
                            ->setStreet('rua de teste')
                            ->setNumber('123')
                            ->setLocality('centro')
                            ->setCity('Guaicara')
                            ->setRegionCode('SP')
                            ->setCountry('BRA')
                            ->setPostalCode('01001001')
                    )
            )
            ->setNotificationUrls([
                'https://teste.com'
            ])
            ->setCharges([
                (new Charge)
                    ->setReferenceId('teste')
                    ->setDescription('teste')
                    ->setAmount(
                        (new Amount)
                            ->setValue(500)
                            ->setCurrency('BRL')
                    )
                    ->setPaymentMethod(
                        (new PaymentMethodCard)
                            ->setInstallments(1)
                            ->setCapture(true)
                            ->setCard(
                                (new Card)
                                    ->setEncrypted("b5WBhfGRxlitZhTu/w8fQHiuBiiJjS/dRSBcD3W8TReM3WlyCLTzxIF3FHaO12cwBHeM33XYVV4UvCugr2d9XOSzQzkbrDawLdhRKU9tvH2Y3P9zpEJktbtqrCb62YxelQNe/j7F83JcH0ogCmw9C2TdVUDT1QphhdwElGrTbidjjEgX3yTXGM6oCOoFXVLUMzwN6Vc7jcVZo6AjtvUnyu/7r/iAdwsB+BVZ7LL67S3KyVR++aQfKFhs21aGwv5yEUtb5r+0MhuKw0YGszO0lG2gzuG/gCgVbhRkRlfx7DYp/fFWAQ2DoFG9/Jl1rHXbemK4oVqPXAZL+vfh1dhftw==")
                                    ->setStore(false)
                            )
                            ->setHolder(
                                (new PaymentHolder)
                                    ->setName("teste")
                                    ->setTaxId("22649835150")
                            )
                    )
            ]);

        $s->exec($order);
    }

    private function createTicketOrder()
    {
        /** @var CreateOrder */
        $s = $this->get(CreateOrder::class);
        $s->setToken("15C7D97A9D964D9EB8039271D24A1E15");
        $order = new Order;
        $order->setReferenceId('teste')
            ->setCustomer(
                (new Customer)
                ->setEmail('test@example.com')
                ->setTaxId('22649835150')
                ->setName('joao da silva')
                ->setPhones([
                (new Phone)
                    ->setCountry(55)
                    ->setArea(14)
                    ->setNumber(981914711)
                    ->setType('MOBILE')
                ])
            )
            ->setItems([
                (new Item)
                    ->setReferenceId('teste')
                    ->setName('teste')
                    ->setQuantity(1)
                    ->setUnitAmount(10)
            ])
            ->setShipping(
                (new Shipping)
                    ->setAddress(
                        (new Address)
                            ->setStreet('rua de teste')
                            ->setNumber('123')
                            ->setLocality('centro')
                            ->setCity('Guaicara')
                            ->setRegionCode('SP')
                            ->setCountry('BRA')
                            ->setPostalCode('01001001')
                    )
            )
            ->setNotificationUrls([
                'https://teste.com'
            ])
            ->setCharges([
                (new Charge)
                    ->setReferenceId('teste')
                    ->setDescription('teste')
                    ->setAmount(
                        (new Amount)
                            ->setValue(500)
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
                                            ->setEmail('fjasdlkfj@fsdasdf.asfd')
                                            ->setName("teste")
                                            ->setTaxId("22649835150")
                                            ->setAddress(
                                                (new Address)
                                                    ->setStreet('rua de teste')
                                                    ->setNumber('123')
                                                    ->setLocality('centro')
                                                    ->setCity('Guaicara')
                                                    ->setRegionCode('SP')
                                                    ->setCountry('BRA')
                                                    ->setPostalCode('01001001')
                                                    ->setRegion('Guaicara')
                                            )
                                    )
                            )
                    )
            ]);

        $s->exec($order);
    }

    private function createPixOrder()
    {
        /** @var CreateOrder */
        $s = $this->get(CreateOrder::class);
        $s->setToken("15C7D97A9D964D9EB8039271D24A1E15");
        $order = new Order;
        $order->setReferenceId('teste')
            ->setCustomer(
                (new Customer)
                ->setEmail('test@example.com')
                ->setTaxId('22649835150')
                ->setName('joao da silva')
                ->setPhones([
                (new Phone)
                    ->setCountry(55)
                    ->setArea(14)
                    ->setNumber(981914711)
                    ->setType('MOBILE')
                ])
            )
            ->setItems([
                (new Item)
                    ->setReferenceId('teste')
                    ->setName('teste')
                    ->setQuantity(1)
                    ->setUnitAmount(10)
            ])
            ->setShipping(
                (new Shipping)
                    ->setAddress(
                        (new Address)
                            ->setStreet('rua de teste')
                            ->setNumber('123')
                            ->setLocality('centro')
                            ->setCity('Guaicara')
                            ->setRegionCode('SP')
                            ->setCountry('BRA')
                            ->setPostalCode('01001001')
                    )
            )
            ->setNotificationUrls([
                'https://teste.com'
            ])
            ->setQrCodes([
                (new QrCode)
                    ->setAmount(
                        (new Amount)
                            ->setValue('10')
                            ->setCurrency('BRL')
                    )->setArrangements([
                        'PAGBANK'
                    ])
                ]);
            
            

        $s->exec($order);
    }

    private function createTicketOrderApplication()
    {
        /** @var CreateTicketCart */
        $s = $this->get(CreateTicketOrder::class);
        $s->exec($this->context->cart, '15C7D97A9D964D9EB8039271D24A1E15');
    }

    private function createPixOrderApplication()
    {
        /** @var CreatePixOrder */
        $s = $this->get(CreatePixOrder::class);
        $s->exec($this->context->cart, '15C7D97A9D964D9EB8039271D24A1E15');
    }

    private function getPublicKey()
    {
        $s = $this->get(GetCardKey::class);
        $s->setToken("15C7D97A9D964D9EB8039271D24A1E15");
        $r = $s->exec();
        dump($r);
    }

    public function createPublicKey()
    {
        $s = $this->get(CreateCardKey::class);
        $s->setToken("15C7D97A9D964D9EB8039271D24A1E15");
        $r = $s->exec();
        
    }

    public function appGetPublicKey()
    {
        $s = $this->get(ServiceGetCardKey::class);
        $r = $s->exec("15C7D97A9D964D9EB8039271D24A1E15");

        dump($r);
    }

    private function getOrder()
    {
        $em = $this->get('doctrine.orm.entity_manager');
        // $order = $em->getRepository(Orders::class)->findOneBy(['id' => ])
        $s = $this->get(GetOrder::class);
        $r = $s->exec("15C7D97A9D964D9EB8039271D24A1E15");   
    }

    private function UpdateChargeFromApi()
    {
        $s = $this->get(UpdateChargeFromApi::class);
        $r = $s->exec("CHAR_251ED3C9-7B45-41F3-A75E-870AEE1DD910", "15C7D97A9D964D9EB8039271D24A1E15");
    }

    private function updateEdi()
    {
        $s = $this->get(UpdateMovementsPreviousDay::class);
        $s->exec();
    }

    private function simulateInstallments()
    {
        $s = $this->get(CalculateChargesService::class);
        dump('aaa');
        $args = new CalculateChargesServiceArgs;
        $args->setPaymentMethods("CREDIT_CARD,PIX,BOLETO")
            ->setValue(10000)
            ->setMaxInstallments(6)
            ->setMaxInstallmentsNoInterest(6)
            ->setCreditCardBin(516292)
            ->setShowSellerFees(true);
        
        $r = $s->exec($args);
        dump($r);

    }
}
