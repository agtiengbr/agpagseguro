<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Entity\AgpagseguroTicketReminder;
use AGTI\PagSeguro\Infrastructure\Service\Mail\DelayedTicketMail;
use Doctrine\ORM\EntityManagerInterface;

class SendReminderEmails
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }
    
    public function exec()
    {
        $repo = $this->em->getRepository(AgpagseguroTicketReminder::class);
        $reminders = $repo->getRemindersToMail();

        foreach ($reminders as $reminder) {
            DelayedTicketMail::sendOrderMail($reminder->getTransaction());
            $reminder->setSentDate(new \DateTime());
            $this->em->flush();
        }

    }
}