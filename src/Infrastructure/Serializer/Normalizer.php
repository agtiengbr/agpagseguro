<?php
namespace AGTI\PagSeguro\Infrastructure\Serializer;

use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\NameConverter\CamelCaseToSnakeCaseNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

if (version_compare(_PS_VERSION_, '9', '<')) {
    class Normalizer
    {
        public static function buildObjectNormalizer()
        {
            $normalizer = new ObjectNormalizer(null, new CamelCaseToSnakeCaseNameConverter(), null, new ReflectionExtractor());
            $normalizer->setCircularReferenceHandler(function(){
                return -1;
            });

            return $normalizer;
        }
    }
} else {
    class Normalizer extends ObjectNormalizer
    {
        public static function buildObjectNormalizer()
        {
            $normalizer = new self(null, new CamelCaseToSnakeCaseNameConverter(), null, new ReflectionExtractor());

            return $normalizer;
        }

        public function normalize($object, $format = null, array $context = [])
        {
            $context[AbstractNormalizer::CIRCULAR_REFERENCE_HANDLER] = function ($object) {
                return -1;
            };

            return parent::normalize($object, $format, $context);
        }
    }
}