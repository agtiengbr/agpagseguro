<?php

namespace AGTI\PagSeguro\Infrastructure\Api\Local\TicketReminder\Save;

use AGTI\PagSeguro\Entity\AgpagseguroTicketReminder;
use Doctrine\ORM\EntityManagerInterface;

class SaveTicketReminderService
{
    private $em;
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function exec(SaveTicketReminderServiceArgs $args)
    {
        $repo = $this->em->getRepository(AgpagseguroTicketReminder::class);
        $currentReminder = $repo->findOneBy(['id' => $args->getReminder()->getId()]);

        if (is_null($currentReminder)) {
            return (new SaveTicketReminderResponseError)->setError("Não foi possível localizar um lembrete com ID {$args->getReminder()->getId()}.");
        }

        $currentReminder->copyFrom($args->getReminder());
        $this->em->flush();

        $ret = new SaveTicketReminderResponseSuccess;
        $ret->setReminder($currentReminder);

        return $ret;
    }
}