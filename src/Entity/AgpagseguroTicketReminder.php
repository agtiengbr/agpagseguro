<?php
namespace AGTI\PagSeguro\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
  * @ORM\Entity(repositoryClass="AGTI\PagSeguro\Repository\TicketReminderRepository")
 */
class AgpagseguroTicketReminder
{
    /**
     * @ORM\Id
     * @ORM\Column(name="id_agpagseguro_ticket_reminder", type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="AgpagseguroTransaction")
     * @ORM\JoinColumn(name="id_agpagseguro_transaction", referencedColumnName="id_agpagseguro_transaction")
     */
    private $transaction;

    /**
     * @ORM\Column(type="datetime")
     */
    private $scheduledDate;

    /**
     * @ORM\Column(type="datetime")
     */
    private $sentDate;

    /**
     * @ORM\Column(type="boolean")
     */
    private $canceled;

    /**
     * Get the value of canceled
     */ 
    public function getCanceled()
    {
        return $this->canceled;
    }

    /**
     * Set the value of canceled
     *
     * @return  self
     */ 
    public function setCanceled($canceled)
    {
        $this->canceled = $canceled;

        return $this;
    }

    /**
     * Get the value of sentDate
     */ 
    public function getSentDate()
    {
        return $this->sentDate;
    }

    /**
     * Set the value of sentDate
     *
     * @return  self
     */ 
    public function setSentDate($sentDate)
    {
        $this->sentDate = $sentDate;

        return $this;
    }

    /**
     * Get the value of scheduledDate
     */ 
    public function getScheduledDate()
    {
        return $this->scheduledDate;
    }

    /**
     * Set the value of scheduledDate
     *
     * @return  self
     */ 
    public function setScheduledDate($scheduledDate)
    {
        $this->scheduledDate = $scheduledDate;

        return $this;
    }

    /**
     * Get the value of transaction
     */ 
    public function getTransaction()
    {
        return $this->transaction;
    }

    /**
     * Set the value of transaction
     *
     * @return  self
     */ 
    public function setTransaction($transaction)
    {
        $this->transaction = $transaction;

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

    public function copyFrom(AgpagseguroTicketReminder $reminder)
    {
        $this->scheduledDate = $reminder->getScheduledDate();
        $this->sentDate = $reminder->getSentDate();
        $this->canceled = $reminder->getCanceled();
    }
}