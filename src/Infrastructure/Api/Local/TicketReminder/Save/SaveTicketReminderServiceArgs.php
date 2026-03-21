<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Local\TicketReminder\Save;

use AGTI\PagSeguro\Entity\AgpagseguroTicketReminder;

class SaveTicketReminderServiceArgs
{
    private $reminder;

    /**
     * Get the value of reminder
     */ 
    public function getReminder()
    {
        return $this->reminder;
    }

    /**
     * Set the value of reminder
     *
     * @return  self
     */ 
    public function setReminder(AgpagseguroTicketReminder $reminder)
    {
        $this->reminder = $reminder;

        return $this;
    }
}