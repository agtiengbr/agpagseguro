<?php

namespace AGTI\PagSeguro\Infrastructure\Serializer;

use AGTI\PagSeguro\ValueObject\InstallmentPlan;
use AGTI\PagSeguro\ValueObject\InstallmentPlanCardBanner;
use AGTI\PagSeguro\ValueObject\InstallmentPlanCardBannerContainer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

class VBSimulationInstallmentPlanCardBannerNormalizer implements DenormalizerInterface
{
    public function denormalize($data, $type, $format = null, array $context = [])
    {
        $normalizer = Normalizer::buildObjectNormalizer();
        $normalizer->setSerializer(Serializer::buildSerializer());

        $dt = $normalizer->denormalize($data, $type, $format, $context);

        $plans = [];
        foreach ($dt->getInstallmentPlans() as $plan) {
            $plans[] = $normalizer->denormalize($plan, InstallmentPlan::class, $format, $context);
        }

        $dt->setInstallmentPlans($plans);
        
        return $dt;
    }

    public function supportsDenormalization($data, $type, $format = null)
    {
        return $type == InstallmentPlanCardBannerContainer::class;
    }
}