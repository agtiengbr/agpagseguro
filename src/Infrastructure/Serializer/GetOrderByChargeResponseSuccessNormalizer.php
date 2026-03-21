<?php
namespace AGTI\PagSeguro\Infrastructure\Serializer;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Boleto;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\GetOrderByChargeResponseSuccess;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class GetOrderByChargeResponseSuccessNormalizer implements DenormalizerInterface
{

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $normalizer = Normalizer::buildObjectNormalizer();
        $normalizer->setSerializer(Serializer::buildSerializer());
        $data = $normalizer->denormalize($data, $type, $format, $context);

        $orders = [];
        foreach ($data->getOrders() as $order) {
            $orders[] = Serializer::buildSerializer()->denormalize($order, Order::class, $format, $context);
        }

        $data->setOrders($orders);
        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === GetOrderByChargeResponseSuccess::class;
    }
}