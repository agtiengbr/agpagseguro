<?php

namespace AGTI\PagSeguro\Infrastructure\Serializer;

use AGTI\PagSeguro\ValueObject\InstallmentPlan;
use AGTI\PagSeguro\ValueObject\SimulationPaymentMethods;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class VBSimulationPaymentMethodsNormalizer implements DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $normalizer = Normalizer::buildObjectNormalizer();
        $normalizer->setSerializer(Serializer::buildSerializer());

        $dt = $normalizer->denormalize($data, $type, $format, $context);
        return $dt;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == SimulationPaymentMethods::class;
    }
}