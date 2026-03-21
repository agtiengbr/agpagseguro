<?php
namespace AGTI\PagSeguro\Infrastructure\Serializer;

use AGTI\PagSeguro\Entity\AgPagseguroMovement;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Edi\Movimentos\EdiMovimentosResponseSuccess as MovimentosEdiMovimentosResponseSuccess;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\CreateOrderResponseError;
use AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity\Error;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class EdiMovimentosResponseSuccess implements DenormalizerInterface
{

    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $normalizer = Normalizer::buildObjectNormalizer();
        $normalizer->setSerializer(Serializer::buildSerializer());
        $data = $normalizer->denormalize($data, $type, $format, $context);

        $movements = [];
        foreach ($data->getDetalhes() as $movement) {
            $movements[] = Serializer::buildSerializer()->denormalize($movement, AgPagseguroMovement::class, $format, $context);
        }

        $data->setDetalhes($movements);
        return $data;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type === MovimentosEdiMovimentosResponseSuccess::class;
    }
}