<?php
namespace AGTI\PagSeguro\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 * @ORM\HasLifecycleCallbacks
 */
class AgpagseguroTransaction
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_agpagseguro_transaction", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
    
    /**
     * @ORM\ManyToOne(targetEntity="Orders")
     * @ORM\JoinColumn(name="id_order", referencedColumnName="id_order")
     */
    private $order;
    
    /**
     * @ORM\Column(type="string")
     */
    private $transactionCode;
    
    /**
     * @ORM\Column(type="string")
     */
    private $transactionStatus;
    
    /**
     * @ORM\Column(type="string")
     */
    private $type;
    
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
    private $expirationDate;

    /**
     * @ORM\Column(type="string")
     */
    private $transactionData;
    
    /**
     * @ORM\Column(type="string")
     */
    private $transactionReference;
    
    /**
     * @ORM\Column(type="integer")
     */
    private $qtyInstallments;
    
    /**
     * @ORM\Column(type="float")
     */
    private $transactionTotal;
    
    /**
     * @ORM\Column(type="float")
     */
    private $intermediationCost;

    /**
     * @ORM\Column(type="string")
     **/
    private $transactionLink;
    
    /**
     * @ORM\Column(type="string")
     **/
    private $copyText;

    /**
     * @ORM\Column(type="string")
     **/
    private $pagseguroOrderId;


    /**
     * @ORM\Column(type="integer")
     **/
    private $value;

    /**
     * Bandeira do cartão de crédito
     * @ORM\Column(type="string", name="card_brand", nullable=true)
     */
    private $cardBrand;
    
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
     * Get the value of order
     */ 
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set the value of order
     *
     * @return  self
     */ 
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get the value of transactionCode
     */ 
    public function getTransactionCode()
    {
        return $this->transactionCode;
    }

    /**
     * Set the value of transactionCode
     *
     * @return  self
     */ 
    public function setTransactionCode($transactionCode)
    {
        $this->transactionCode = $transactionCode;

        return $this;
    }

    /**
     * Get the value of transactionStatus
     */ 
    public function getTransactionStatus()
    {
        return $this->transactionStatus;
    }

    /**
     * Set the value of transactionStatus
     *
     * @return  self
     */ 
    public function setTransactionStatus($transactionStatus)
    {
        $this->transactionStatus = $transactionStatus;

        return $this;
    }

    /**
     * Get the value of type
     */ 
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set the value of type
     *
     * @return  self
     */ 
    public function setType($type)
    {
        $this->type = $type;

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
     * Get the value of transactionData
     */ 
    public function getTransactionData()
    {
        return $this->transactionData;
    }

    /**
     * Set the value of transactionData
     *
     * @return  self
     */ 
    public function setTransactionData($transactionData)
    {
        $this->transactionData = $transactionData;

        return $this;
    }

    /**
     * Get the value of transactionReference
     */ 
    public function getTransactionReference()
    {
        return $this->transactionReference;
    }

    /**
     * Set the value of transactionReference
     *
     * @return  self
     */ 
    public function setTransactionReference($transactionReference)
    {
        $this->transactionReference = $transactionReference;

        return $this;
    }

    /**
     * Get the value of qtyInstallments
     */ 
    public function getQtyInstallments()
    {
        return $this->qtyInstallments;
    }

    /**
     * Set the value of qtyInstallments
     *
     * @return  self
     */ 
    public function setQtyInstallments($qtyInstallments)
    {
        $this->qtyInstallments = $qtyInstallments;

        return $this;
    }

    /**
     * Get the value of intermediationCost
     */ 
    public function getIntermediationCost()
    {
        return $this->intermediationCost;
    }

    /**
     * Set the value of intermediationCost
     *
     * @return  self
     */ 
    public function setIntermediationCost($intermediationCost)
    {
        $this->intermediationCost = $intermediationCost;

        return $this;
    }

    /**
     * Get the value of transactionTotal
     */ 
    public function getTransactionTotal()
    {
        return $this->transactionTotal;
    }

    /**
     * Set the value of transactionTotal
     *
     * @return  self
     */ 
    public function setTransactionTotal($transactionTotal)
    {
        $this->transactionTotal = $transactionTotal;

        return $this;
    }

    /**
     * Get the value of transactionLink
     */ 
    public function getTransactionLink()
    {
        return $this->transactionLink;
    }

    /**
     * Set the value of transactionLink
     *
     * @return  self
     */ 
    public function setTransactionLink($transactionLink)
    {
        $this->transactionLink = $transactionLink;

        return $this;
    }

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->setDateAdd(new \DateTime);
        $this->setDateUpd(new \DateTime);

    }

    /**
     * Get the value of copyText
     */ 
    public function getCopyText()
    {
        return $this->copyText;
    }

    /**
     * Set the value of copyText
     *
     * @return  self
     */ 
    public function setCopyText($copyText)
    {
        $this->copyText = $copyText;

        return $this;
    }

    /**
     * Get the value of pagseguroOrderId
     */ 
    public function getPagseguroOrderId()
    {
        return $this->pagseguroOrderId;
    }

    /**
     * Set the value of pagseguroOrderId
     *
     * @return  self
     */ 
    public function setPagseguroOrderId($pagseguroOrderId)
    {
        $this->pagseguroOrderId = $pagseguroOrderId;

        return $this;
    }

    /**
     * Get the value of expirationDate
     */ 
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * Set the value of expirationDate
     *
     * @return  self
     */ 
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;

        return $this;
    }

    /**
     * Get the value of value
     */ 
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set the value of value
     *
     * @return  self
     */ 
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get the value of cardBrand
     */
    public function getCardBrand()
    {
        return $this->cardBrand;
    }

    /**
     * Set the value of cardBrand
     *
     * @return  self
     */
    public function setCardBrand($cardBrand)
    {
        $this->cardBrand = $cardBrand;

        return $this;
    }
}