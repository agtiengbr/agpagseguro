<?php
namespace AGTI\PagSeguro\Infrastructure\Serializer;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Boleto;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Charge;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Order;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\QrCode;
use Symfony\Component\Serializer\Normalizer\DenormalizableInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class RemoteOrderNormalizer implements DenormalizerInterface
{

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $normalizer = Normalizer::buildObjectNormalizer();
        $normalizer->setSerializer(Serializer::buildSerializer());
        $data = $normalizer->denormalize($data, $type, $format, $context);

        $remoteCharges = [];

        if (is_array($data->getCharges())) {
            foreach ($data->getCharges() as $charge) {
                $remoteCharges[] = $normalizer->denormalize($charge, Charge::class, $format, $context);
            }
            $data->setcharges($remoteCharges);
        }

        $remoteCodes = [];
        if (is_array($data->getQrCodes())) {
            foreach ($data->getQrCodes() as $code) {
                $remoteCodes[] = $normalizer->denormalize($code, QrCode::class, $format, $context);
            }
            $data->setQrCodes($remoteCodes);

        }
        
        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null) {
        return $type == Order::class;
    }


    public function normalize($object, $format = null, array $context = [])
    {
        $normalizer = Normalizer::buildObjectNormalizer();
        $normalizer->setSerializer(Serializer::buildSerializer());
        $data = $normalizer->normalize($object, $format, $context);

        $objectCharges = [];
        foreach ($data['charges'] as $i=>$charge) {
            $objectCharges[] = $normalizer->denormalize($charge, Charge::class);
        }

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        
    }
    
}