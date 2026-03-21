<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote;
use AgApiManagerEmployeeToken;
use AGTI\ApiManager\Entity\AgApiManagerEmployeeToken as EntityAgApiManagerEmployeeToken;
use AGTI\HostManager\Entity\ApiRequest;
use AGTI\ApiManager\Entity\ApiUser;
use AGTI\PagSeguro\Entity\AgpagseguroRequest;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\Serializer\Serializer;
use AGTI\PagSeguro\ValueObject\Configuration;

abstract class BaseService
{
    const API_BASE = "https://api.pagseguro.com";
    const API_BASE_SANDBOX = "https://sandbox.api.pagseguro.com";

    private $serializer;
    private $request;
    private $token;
    private $http;
    private $config;
    
    public function __construct(CurlHttpClient $http, Serializer $serializer, Configuration $config)
    {
        $this->serializer = $serializer;
        $this->http = $http;
        $this->config = $config;
    }

    abstract function getApiEndpoint();

    /**
     * @return ApiRequest
     */
    protected function send($method, $querystring=[], $bodyData=null, $extraHeaders = []):AgpagseguroRequest
    {
        $url = $this->getBaseUrl() . $this->getApiEndpoint();
        $headers = [
            // 'Accept: application/json',
            'Content-Type: application/json',
            'Authorization: Bearer ' . $this->config->getCurrentToken()
        ];
        $headers = array_merge($headers, $extraHeaders);

    // measure duration without relying on Symfony Stopwatch
    $t0 = microtime(true);

        $r = $this->http->request(
            $method,
            $url,
            [
                'query' => $querystring,
                'json' => $bodyData,
                'headers' => $headers,
            ]
        );

    $durationMs = (microtime(true) - $t0) * 1000;

        $ret = new AgpagseguroRequest;
        $ret->setDateAdd(new \DateTime);

        try {
            $info = $r->getInfo();
            
            $ret->setEndpoint($info['url'])
                ->setHeaders($headers)
                ->setMethod($info['http_method'])
                ->setBody($bodyData)
                ->setHttpCode($r->getStatusCode())
                ->setTimeSpent($durationMs)
                ->setResponse($r->getContent());
        } catch (ServerException $e) {
            $ret->setResponse($r->getContent(false));
        } catch (ClientException $e) {
            $ret->setResponse($r->getContent(false));
        } catch (\Exception $e){}

    $ret->setStackTrace((new \Exception)->getTraceAsString());
        $this->request = $ret;
        return $ret;
    }

    private function getToken()
    {
        return $this->token;
    }

    /**
     * Get the value of isSandbox
     */ 
    private function getIsSandbox()
    {
        return $this->isSandbox;
    }

    private function getBaseUrl()
    {
        if ($this->config->getIsSandbox()) {
            return self::API_BASE_SANDBOX;
        }

        return self::API_BASE;
    }

    /**
     * Get the value of serializer
     */ 
    public function getSerializer()
    {
        return $this->serializer;
    }

    /**
     * Get the value of request
     * 
     * @return AgpagseguroRequest
     */ 
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Handle non-200 responses: log and throw an exception.
     * Child services should call this when they detect an unexpected HTTP code.
     *
     * @throws \RuntimeException
     */
    protected function handleErrorResponse()
    {
        if (!$this->request) {
            throw new \RuntimeException('PagSeguro request failed: no request info available');
        }

        // Log no PrestaShop
        $context = [
            'endpoint' => $this->request->getEndpoint(),
            'httpCode' => $this->request->getHttpCode(),
            'response' => $this->request->getResponse(),
        ];
        \Logger::addLog('PagSeguro API request failed: ' . json_encode($context), 3);

        throw new \RuntimeException(sprintf('PagSeguro API %s failed with HTTP %s', $this->request->getEndpoint(), $this->request->getHttpCode()));
    }
}
