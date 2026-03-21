<?php
namespace AGTI\PagSeguro\ValueObject;

class InstallmentPlanCardBannerContainer
{
    /**
     * @var InstallmentPlan[]
     */
    private $installmentPlans;

    /**
     * Get the value of installmentPlans
     *
     * @return  InstallmentPlan[]
     */ 
    public function getInstallmentPlans()
    {
        return $this->installmentPlans;
    }

    /**
     * Set the value of installmentPlans
     *
     * @param  InstallmentPlan[]  $installmentPlans
     *
     * @return  self
     */ 
    public function setInstallmentPlans($installmentPlans)
    {
        $this->installmentPlans = $installmentPlans;

        return $this;
    }
}