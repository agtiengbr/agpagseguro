<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Local\TicketReminder\Save;

class SaveTicketReminderResponseError
{
    private $error;
    private $success = false;

    /**
     * Get the value of error
     */ 
    public function getError()
    {
        return $this->error;
    }

    /**
     * Set the value of error
     *
     * @return  self
     */ 
    public function setError($error)
    {
        $this->error = $error;

        return $this;
    }

    /**
     * Get the value of success
     */ 
    public function getSuccess()
    {
        return $this->success;
    }
}