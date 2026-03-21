<?php

namespace AGTI\PagSeguro\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class Orders
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(name="id_order", type="integer")
     */
    private $id;

    /**
     * @ORM\Column(name="reference", type="string")
     */
    private $reference;

    /**
     * @ORM\OneToOne(targetEntity="Customer", cascade={"persist"})
     * @ORM\JoinColumn(name="id_customer", referencedColumnName="id_customer")
     */
    private $customer;
    
    /**
     * @ORM\ManyToOne(targetEntity="Address", cascade={"persist"})
     * @ORM\JoinColumn(name="id_address_delivery", referencedColumnName="id_address")
     */
    private $addressDelivery;

    /**
     * @ORM\ManyToOne(targetEntity="Address", cascade={"persist"})
     * @ORM\JoinColumn(name="id_address_invoice", referencedColumnName="id_address")
     */
    private $addressInvoice;

    /**
     * @ORM\Column(type="string")
     */
    private $module;

    /**
     * @ORM\Column(type="string")
     */
    private $payment;


    /**
     * @ORM\Column(type="float")
     */
    private $totalPaid;
    
    /**
     * @ORM\Column(type="float")
     */
    private $totalPaidTaxExcl;
    
    /**
     * @ORM\Column(type="float")
     */
    private $totalPaidTaxIncl;
    
    /**
     * @ORM\Column(type="float")
     */
    private $totalPaidReal;
    
    /**
     * @ORM\Column(type="float")
     */
    private $totalProducts;
    
    /**
     * @ORM\Column(type="float")
     */
    private $totalProductsWt;

    /**
     * @ORM\Column(type="integer")
     */
    private $conversionRate;

    /**
     * @ORM\Column(type="string")
     */
    private $secureKey;

    
    /**
     * @ORM\Column(type="datetime")
     */
    private $dateAdd;

    /**
     * @ORM\Column(type="datetime")
     */
    private $dateUpd;


    /**
     * @ORM\Column(type="datetime")
     */
    private $invoiceDate;
    
    /**
     * @ORM\Column(type="datetime")
     */
    private $deliveryDate;

    /**
     * @ORM\Column(type="integer")
     */
    private $idLang;
    
    /**
     * Get the value of deliveryDate
     */ 
    public function getDeliveryDate()
    {
        return $this->deliveryDate;
    }

    /**
     * Set the value of deliveryDate
     *
     * @return  self
     */ 
    public function setDeliveryDate($deliveryDate)
    {
        $this->deliveryDate = $deliveryDate;

        return $this;
    }

    /**
     * Get the value of invoiceDate
     */ 
    public function getInvoiceDate()
    {
        return $this->invoiceDate;
    }

    /**
     * Set the value of invoiceDate
     *
     * @return  self
     */ 
    public function setInvoiceDate($invoiceDate)
    {
        $this->invoiceDate = $invoiceDate;

        return $this;
    }

    /**
     * Get the value of dateUpd
     */ 
    public function getDateUpd()
    {
        return $this->dateUpd;
    }

    /**
     * Set the value of dateUpd
     *
     * @return  self
     */ 
    public function setDateUpd($dateUpd)
    {
        $this->dateUpd = $dateUpd;

        return $this;
    }

    /**
     * Get the value of dateAdd
     */ 
    public function getDateAdd()
    {
        return $this->dateAdd;
    }

    /**
     * Set the value of dateAdd
     *
     * @return  self
     */ 
    public function setDateAdd($dateAdd)
    {
        $this->dateAdd = $dateAdd;

        return $this;
    }

    /**
     * Get the value of secureKey
     */ 
    public function getSecureKey()
    {
        return $this->secureKey;
    }

    /**
     * Set the value of secureKey
     *
     * @return  self
     */ 
    public function setSecureKey($secureKey)
    {
        $this->secureKey = $secureKey;

        return $this;
    }

    /**
     * Get the value of conversionRate
     */ 
    public function getConversionRate()
    {
        return $this->conversionRate;
    }

    /**
     * Set the value of conversionRate
     *
     * @return  self
     */ 
    public function setConversionRate($conversionRate)
    {
        $this->conversionRate = $conversionRate;

        return $this;
    }

    /**
     * Get the value of totalProductsWt
     */ 
    public function getTotalProductsWt()
    {
        return $this->totalProductsWt;
    }

    /**
     * Set the value of totalProductsWt
     *
     * @return  self
     */ 
    public function setTotalProductsWt($totalProductsWt)
    {
        $this->totalProductsWt = $totalProductsWt;

        return $this;
    }

    /**
     * Get the value of totalProducts
     */ 
    public function getTotalProducts()
    {
        return $this->totalProducts;
    }

    /**
     * Set the value of totalProducts
     *
     * @return  self
     */ 
    public function setTotalProducts($totalProducts)
    {
        $this->totalProducts = $totalProducts;

        return $this;
    }

    /**
     * Get the value of totalPaidReal
     */ 
    public function getTotalPaidReal()
    {
        return $this->totalPaidReal;
    }

    /**
     * Set the value of totalPaidReal
     *
     * @return  self
     */ 
    public function setTotalPaidReal($totalPaidReal)
    {
        $this->totalPaidReal = $totalPaidReal;

        return $this;
    }

    /**
     * Get the value of totalPaidTaxIncl
     */ 
    public function getTotalPaidTaxIncl()
    {
        return $this->totalPaidTaxIncl;
    }

    /**
     * Set the value of totalPaidTaxIncl
     *
     * @return  self
     */ 
    public function setTotalPaidTaxIncl($totalPaidTaxIncl)
    {
        $this->totalPaidTaxIncl = $totalPaidTaxIncl;

        return $this;
    }

    /**
     * Get the value of totalPaidTaxExcl
     */ 
    public function getTotalPaidTaxExcl()
    {
        return $this->totalPaidTaxExcl;
    }

    /**
     * Set the value of totalPaidTaxExcl
     *
     * @return  self
     */ 
    public function setTotalPaidTaxExcl($totalPaidTaxExcl)
    {
        $this->totalPaidTaxExcl = $totalPaidTaxExcl;

        return $this;
    }

    /**
     * Get the value of totalPaid
     */ 
    public function getTotalPaid()
    {
        return $this->totalPaid;
    }

    /**
     * Set the value of totalPaid
     *
     * @return  self
     */ 
    public function setTotalPaid($totalPaid)
    {
        $this->totalPaid = $totalPaid;

        return $this;
    }

    /**
     * Get the value of payment
     */ 
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set the value of payment
     *
     * @return  self
     */ 
    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get the value of module
     */ 
    public function getModule()
    {
        return $this->module;
    }

    /**
     * Set the value of module
     *
     * @return  self
     */ 
    public function setModule($module)
    {
        $this->module = $module;

        return $this;
    }

    /**
     * Get the value of addressInvoice
     */ 
    public function getAddressInvoice()
    {
        return $this->addressInvoice;
    }

    /**
     * Set the value of addressInvoice
     *
     * @return  self
     */ 
    public function setAddressInvoice($addressInvoice)
    {
        $this->addressInvoice = $addressInvoice;

        return $this;
    }

    /**
     * Get the value of addressDelivery
     */ 
    public function getAddressDelivery()
    {
        return $this->addressDelivery;
    }

    /**
     * Set the value of addressDelivery
     *
     * @return  self
     */ 
    public function setAddressDelivery($addressDelivery)
    {
        $this->addressDelivery = $addressDelivery;

        return $this;
    }

    /**
     * Get the value of products
     */ 
    public function getProducts()
    {
        return $this->products;
    }

    /**
     * Set the value of products
     *
     * @return  self
     */ 
    public function setProducts($products)
    {
        $this->products = $products;

        return $this;
    }

    /**
     * Get the value of customer
     */ 
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * Set the value of customer
     *
     * @return  self
     */ 
    public function setCustomer($customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get the value of reference
     */ 
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * Set the value of reference
     *
     * @return  self
     */ 
    public function setReference($reference)
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * Get the value of id
     */ 
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the value of id
     *
     * @return  self
     */ 
    public function setId($id)
    {
        $this->id = $id;

        return $this;
    }

    /**
     * Get the value of idLang
     */ 
    public function getIdLang()
    {
        return $this->idLang;
    }

    /**
     * Set the value of idLang
     *
     * @return  self
     */ 
    public function setIdLang($idLang)
    {
        $this->idLang = $idLang;

        return $this;
    }
}