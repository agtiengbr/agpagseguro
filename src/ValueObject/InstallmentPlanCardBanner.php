<?php
namespace AGTI\PagSeguro\ValueObject;

class InstallmentPlanCardBanner
{
    /**
     * @var InstallmentPlanCardBannerContainer
     */
    private $mastercard;

    /**
     * @var InstallmentPlanCardBannerContainer
     */
    private $visa;

    /**
     * @var InstallmentPlanCardBannerContainer
     */
    private $elo;

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

    /**
     * Get the value of visa
     *
     * @return  InstallmentPlanCardBannerContainer
     */ 
    public function getVisa()
    {
        return $this->visa;
    }

    /**
     * Set the value of visa
     *
     * @param  InstallmentPlanCardBannerContainer  $visa
     *
     * @return  self
     */ 
    public function setVisa(InstallmentPlanCardBannerContainer $visa)
    {
        $this->visa = $visa;

        return $this;
    }

    /**
     * Get the value of elo
     *
     * @return  InstallmentPlanCardBannerContainer
     */ 
    public function getElo()
    {
        return $this->elo;
    }

    /**
     * Set the value of elo
     *
     * @param  InstallmentPlanCardBannerContainer  $elo
     *
     * @return  self
     */ 
    public function setElo(InstallmentPlanCardBannerContainer $elo)
    {
        $this->elo = $elo;

        return $this;
    }
}