<?php
namespace AGTI\PagSeguro\Infrastructure\Service\Mail;

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;

class DelayedTicketMail extends Mail
{
    public static function sendOrderMail(AgpagseguroTransaction $transaction)
    {
        $order = $transaction->getOrder();
        $vars = [];

        $vars['{firstname}'] = $order->getCustomer()->getFirstname();
        $vars['{lastname}'] = $order->getCustomer()->getLastname();
        $vars['{ticket_link}'] = $transaction->getTransactionLink();;
        $vars['{order_reference}'] = $order->getReference();

        parent::send(
            $order->getIdLang(),
            'ticket_reminder',
            'Lembrete: conclua o pagamento do seu pedido.',
            $vars,
            $order->getCustomer()->getEmail(),
            $order->getCustomer()->getFirstname() . ' ' . $order->getCustomer()->getLastname(),
            _PS_MODULE_DIR_ . 'agpagseguro/mails/'
        );
    }
}