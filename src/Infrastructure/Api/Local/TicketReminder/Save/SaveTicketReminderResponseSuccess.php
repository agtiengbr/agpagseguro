<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Local\TicketReminder\Save;

class SaveTicketReminderResponseSuccess
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
    public function setReminder($reminder)
    {
        $this->reminder = $reminder;

        return $this;
    }
}