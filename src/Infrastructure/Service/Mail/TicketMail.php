<?php
namespace AGTI\PagSeguro\Infrastructure\Service\Mail;

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;

class TicketMail extends Mail
{
    public static function sendTicketMail(AgpagseguroTransaction $transaction)
    {
        $order = $transaction->getOrder();
        $vars = [];

        $vars['{customer_name}'] = $order->getCustomer()->getFirstname() . ' ' . $order->getCustomer()->getLastname();
        $vars['{order_name}'] = $transaction->getOrder()->getReference();
        $vars['{print_ticket_link}'] = $transaction->getTransactionLink();
        parent::send(
            $order->getIdLang(),
            'ticket',
            'Lembrete: conclua o pagamento do seu pedido.',
            $vars,
            $order->getCustomer()->getEmail(),
            $order->getCustomer()->getFirstname() . ' ' . $order->getCustomer()->getLastname(),
            _PS_MODULE_DIR_ . 'agpagseguro/mails/'
        );
    }
}