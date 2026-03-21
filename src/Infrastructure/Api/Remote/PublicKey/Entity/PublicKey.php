<?php
namespace AGTI\PagSeguro\Infrastructure\Api\Remote\PublicKey\Entity;

class PublicKey
{
    private $publicKey;
    private $createdAt;

    /**
     * Get the value of createdAt
     */ 
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set the value of createdAt
     *
     * @return  self
     */ 
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get the value of publicKey
     */ 
    public function getPublicKey()
    {
        return $this->publicKey;
    }

    /**
     * Set the value of publicKey
     *
     * @return  self
     */ 
    public function setPublicKey($publicKey)
    {
        $this->publicKey = $publicKey;

        return $this;
    }
}
