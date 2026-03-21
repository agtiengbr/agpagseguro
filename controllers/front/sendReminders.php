<?php

// use AGTI\PagSeguro\Repository\TicketReminderRepository;

use AGTI\PagSeguro\Application\Service\SendReminderEmails;
use AGTI\PagSeguro\Entity\AgpagseguroTicketReminder;

class agpagsegurosendRemindersModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        parent::initContent();

        $service = $this->get(SendReminderEmails::class);
        $service->exec();

        exit();
    }
}