<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Local\TicketReminder\Get;

use AGTI\PagSeguro\Entity\AgpagseguroTicketReminder;
use Doctrine\ORM\EntityManagerInterface;

class GetTicketReminderService
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function exec(GetTicketReminderServiceArgs $args)
    {
        $repo = $this->em->getRepository(AgpagseguroTicketReminder::class);
        $data = $repo->findBy([], ['scheduleDate' => 'DESC'], 1000);

        $ret = new GetTicketReminderResponseSuccess;
        $ret->setReminders($data);

        return $ret;
    }
}