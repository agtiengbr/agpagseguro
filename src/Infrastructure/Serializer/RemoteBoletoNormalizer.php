<?php
namespace AGTI\PagSeguro\Infrastructure\Serializer;

use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Boleto;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class RemoteBoletoNormalizer implements NormalizerInterface
{

    public function normalize($object, $format = null, array $context = [])
    {
        $normalizer = Normalizer::buildObjectNormalizer();
        $normalizer->setSerializer(Serializer::buildSerializer());
        $data = $normalizer->normalize($object, $format, $context);
        $data['due_date'] = $object->getDueDate()->format('Y-m-d');

        return $data;
    }

    public function supportsNormalization($data, $format = null)
    {
        return $data instanceof Boleto;
    }
    
}