<?php
namespace AGTI\PagSeguro\Infrastructure\Serializer;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CreateOrderResponseError;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Boleto;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Error;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrderByChargeResponseSuccess;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class CreateOrderResponseErrorDenormalizer implements DenormalizerInterface
{

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $normalizer = Normalizer::buildObjectNormalizer();
        $normalizer->setSerializer(Serializer::buildSerializer());
        $data = $normalizer->denormalize($data, $type, $format, $context);

        $errors = [];
        foreach ($data->getErrors() as $error) {
            $errors[] = Serializer::buildSerializer()->denormalize($error, Error::class, $format, $context);
        }

        $data->setErrorMessages($errors);
        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === CreateOrderResponseError::class;
    }
}