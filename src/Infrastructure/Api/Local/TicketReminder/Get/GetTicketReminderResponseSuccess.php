<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Local\TicketReminder\Get;

class GetTicketReminderResponseSuccess
{
    private $reminders;

    /**
     * Get the value of reminders
     */ 
    public function getReminders()
    {
        return $this->reminders;
    }

    /**
     * Set the value of reminders
     *
     * @return  self
     */ 
    public function setReminders($reminders)
    {
        $this->reminders = $reminders;

        return $this;
    }
}