<?php
namespace AGTI\PagSeguro\Infrastructure\Service\Mail;

use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use PrestaShop\PrestaShop\Adapter\Product\PriceFormatter;

class PixMail extends Mail
{
    private static function formatPrice($price)
    {
        if (class_exists(PriceFormatter::class)) {
            return (new PriceFormatter())->format($price);
        }

        return \Tools::displayPrice($price);
    }

    public static function sendPixMail(AgpagseguroTransaction $transaction)
    {
        $order = $transaction->getOrder();
        $vars = [];

        $vars['{customer_name}'] = $order->getCustomer()->getFirstname() . ' ' . $order->getCustomer()->getLastname();
        $vars['{pix_expiration_date}'] = $transaction->getExpirationDate()->format('d/m/Y H:i:s');
        $vars['{value_invoiced}'] = $transaction->getValue();
        $vars['{pix_qrcode_url}'] = self::formatPrice($transaction->getTransactionTotal() / 100);
        $vars['{pix_qrcode_hash}'] = $transaction->getCopyText();

        parent::send(
            $order->getIdLang(),
            'create_pix',
            'Lembrete: conclua o pagamento do seu pedido.',
            $vars,
            $order->getCustomer()->getEmail(),
            $order->getCustomer()->getFirstname() . ' ' . $order->getCustomer()->getLastname(),
            _PS_MODULE_DIR_ . 'agpagseguro/mails/'
        );
    }
}