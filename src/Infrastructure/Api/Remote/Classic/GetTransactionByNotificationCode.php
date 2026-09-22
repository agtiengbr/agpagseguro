<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Classic;

use AGTI\PagSeguro\Infrastructure\Webhook\ClassicNotificationPayloadParser;
use AGTI\PagSeguro\ValueObject\Configuration;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class GetTransactionByNotificationCode
{
    private $httpClient;
    private $configuration;

    public function __construct(HttpClientInterface $httpClient, Configuration $configuration)
    {
        $this->httpClient = $httpClient;
        $this->configuration = $configuration;
    }

    public function exec($notificationCode)
    {
        if (!ClassicNotificationPayloadParser::isClassicNotificationCode($notificationCode)) {
            throw new \InvalidArgumentException('Invalid classic notification code.');
        }

        if (!function_exists('simplexml_load_string')) {
            throw new \RuntimeException('The SimpleXML PHP extension is required to process PagBank classic notifications.');
        }

        $email = trim((string) \Configuration::get('AGPAGSEGURO_EMAIL'));
        $token = trim((string) $this->configuration->getCurrentToken());
        if ($email === '' || $token === '') {
            throw new \RuntimeException('PagBank classic notification credentials are not configured.');
        }

        $baseUrl = $this->configuration->getIsSandbox()
            ? 'https://ws.sandbox.pagseguro.uol.com.br/v3'
            : 'https://ws.pagseguro.uol.com.br/v3';
        $endpoint = $baseUrl . '/transactions/notifications/' . rawurlencode($notificationCode);

        try {
            $response = $this->httpClient->request('GET', $endpoint, [
                'query' => [
                    'email' => $email,
                    'token' => $token,
                ],
                'headers' => [
                    'Content-Type' => 'application/xml',
                ],
                'timeout' => 15,
                'max_redirects' => 0,
            ]);

            $httpCode = $response->getStatusCode();
            $xmlBody = $response->getContent(false);
        } catch (\Throwable $e) {
            // Avoid exposing the authenticated URL, whose query contains the account token.
            throw new \RuntimeException('PagBank classic notification lookup failed before receiving a response.', 0, $e);
        }

        if ($httpCode !== 200) {
            throw new \RuntimeException(sprintf('PagBank classic notification lookup returned HTTP %d.', $httpCode));
        }

        $previousLibxmlSetting = libxml_use_internal_errors(true);
        try {
            $transaction = simplexml_load_string(
                $xmlBody,
                \SimpleXMLElement::class,
                LIBXML_NONET | LIBXML_NOBLANKS
            );
        } catch (\Throwable $e) {
            throw new \RuntimeException('PagBank classic notification response could not be parsed as XML.', 0, $e);
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousLibxmlSetting);
        }

        if (!$transaction || $transaction->getName() !== 'transaction') {
            throw new \RuntimeException('PagBank classic notification response has an invalid XML document.');
        }

        $transactionCode = trim((string) $transaction->code);
        $status = trim((string) $transaction->status);
        if (strlen($transactionCode) !== 36 || preg_match('/\A[A-Za-z0-9-]{36}\z/', $transactionCode) !== 1) {
            throw new \RuntimeException('PagBank classic notification response has an invalid transaction code.');
        }

        if ($status === '' || !ctype_digit($status)) {
            throw new \RuntimeException('PagBank classic notification response has an invalid transaction status.');
        }

        return [
            'transactionCode' => $transactionCode,
            'status' => $status,
        ];
    }
}
