<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Remote\Order\Entity;

class Order
{
    private $id;
    private $referenceId;
    private $customer;
    private $items;
    private $shipping;
    private $notificationUrls;
    private $charges;
    private $qrCodes;

    /**
     * Get the value of referenceId
     */ 
    public function getReferenceId()
    {
        return $this->referenceId;
    }

    /**
     * Set the value of referenceId
     *
     * @return  self
     */ 
    public function setReferenceId($referenceId)
    {
        $this->referenceId = $referenceId;

        return $this;
    }

    /**
     * Get the value of items
     */ 
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Set the value of items
     *
     * @return  self
     */ 
    public function setItems($items)
    {
        $this->items = $items;

        return $this;
    }

    /**
     * Get the value of shipping
     */ 
    public function getShipping()
    {
        return $this->shipping;
    }

    /**
     * Set the value of shipping
     *
     * @return  self
     */ 
    public function setShipping(Shipping $shipping)
    {
        $this->shipping = $shipping;

        return $this;
    }

    /**
     * Get the value of notificationUrls
     */ 
    public function getNotificationUrls()
    {
        return $this->notificationUrls;
    }

    /**
     * Set the value of notificationUrls
     *
     * @return  self
     */ 
    public function setNotificationUrls($notificationUrls)
    {
        $this->notificationUrls = $notificationUrls;

        return $this;
    }

    /**
     * Get the value of charges
     */ 
    public function getCharges()
    {
        return $this->charges;
    }

    /**
     * Set the value of charges
     *
     * @return  self
     */ 
    public function setCharges($charges)
    {
        $this->charges = $charges;

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
    public function setCustomer(Customer $customer)
    {
        $this->customer = $customer;

        return $this;
    }

    /**
     * Get the value of qrCodes
     */ 
    public function getQrCodes()
    {
        return $this->qrCodes;
    }

    /**
     * Set the value of qrCodes
     *
     * @return  self
     */ 
    public function setQrCodes($qrCodes)
    {
        $this->qrCodes = $qrCodes;

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
}