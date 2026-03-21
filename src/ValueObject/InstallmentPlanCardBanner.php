<?php
namespace AGTI\PagSeguro\ValueObject;

class InstallmentPlanCardBanner
{
    /**
     * @var InstallmentPlanCardBannerContainer
     */
    private $mastercard;

    /**
     * Get the value of mastercard
     *
     * @return  InstallmentPlanCardBannerContainer
     */ 
    public function getMastercard()
    {
        return $this->mastercard;
    }

    /**
     * Set the value of mastercard
     *
     * @param  InstallmentPlanCardBannerContainer  $mastercard
     *
     * @return  self
     */ 
    public function setMastercard(InstallmentPlanCardBannerContainer $mastercard)
    {
        $this->mastercard = $mastercard;

        return $this;
    }
}