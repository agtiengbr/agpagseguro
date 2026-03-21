<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Edi\Movimentos;
use AGTI\HostManager\Entity\ApiRequest;
use AGTI\PagSeguro\Entity\AgpagseguroRequest;
use Symfony\Component\HttpClient\CurlHttpClient;
use Symfony\Component\HttpClient\Exception\ClientException;
use Symfony\Component\HttpClient\Exception\ServerException;
use Symfony\Component\Serializer\Serializer;
use AGTI\PagSeguro\ValueObject\Configuration;

class EdiMovimentosService
{
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

    private function getApiEndpoint()
    {
        return "https://edi.api.pagbank.com.br/edi/v1/2.01/movimentos";
    }

    /**
     * @return ApiRequest
     */
    public function send(\DateTime $dateFrom, $pageSize, $pageNumber, $tipoMovimento)
    {
        $headers = [
            'Content-Type: application/json',
        ];

    // measure duration without relying on Symfony Stopwatch
    $t0 = microtime(true);

        $r = $this->http->request(
            'GET',
            $this->getApiEndpoint(),
            [
                'headers' => $headers,
                'auth_basic' => [$this->config->getEdiUsername(), $this->config->getEdiToken()],
                'query' => [
                    'dataMovimento' => $dateFrom->format('Y-m-d'),
                    'pageNumber' => $pageNumber,
                    'pageSize' => $pageSize,
                    'tipoMovimento' => $tipoMovimento
                ]
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

        if ($ret->getHttpCode() == 200) {
            return $this->getSerializer()->deserialize($ret->getResponse(), EdiMovimentosResponseSuccess::class, 'json');
        }
    }

    private function getToken()
    {
        return $this->token;
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
}