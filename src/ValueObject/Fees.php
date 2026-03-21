<?php
namespace AGTI\PagSeguro\ValueObject;

class Fees
{
    /**
     * @var SellerFee
     */
    private $seller;


    /**
     * Get the value of seller
     *
     * @return  SellerFee
     */ 
    public function getSeller()
    {
        return $this->seller;
    }

    /**
     * Set the value of seller
     *
     * @param  SellerFee  $seller
     *
     * @return  self
     */ 
    public function setSeller(SellerFee $seller)
    {
        $this->seller = $seller;

        return $this;
    }
}