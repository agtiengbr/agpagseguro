<?php
namespace AGTI\PagSeguro\Infrastructure\Serializer;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Serializer as SymfonySerializer;

class Serializer
{
    public static function buildSerializer()
    {
        $encoders = [new JsonEncoder(), new XmlEncoder()];

        $normalizers = [
            new DateTimeNormalizer(),
            new GetOrderByChargeResponseSuccessNormalizer,
            new CreateOrderResponseErrorDenormalizer,
            new RemoteBoletoNormalizer,
            new RemoteOrderNormalizer,
            new EdiMovimentosResponseSuccess,
            new VBSimulationPaymentMethodsNormalizer,
            new VBSimulationInstallmentPlanCardBannerNormalizer,
            Normalizer::buildObjectNormalizer()
        ];
        $serializer = new SymfonySerializer($normalizers, $encoders);

        return $serializer;
    }
}