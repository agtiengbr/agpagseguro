<?php

namespace AGTI\PagSeguro\Application\Service;

use AGTI\PagSeguro\Entity\AgpagseguroTicketReminder;
use AGTI\PagSeguro\Entity\AgpagseguroTransaction;
use Doctrine\ORM\EntityManagerInterface;

class GenerateRemindersForTransaction
{
    private $em;
    private $days;

    public function __construct(
        EntityManagerInterface $em,
        $days
    )
    {
        $this->em = $em;
        $this->days = $days;    
    }

    public function exec(
        AgPagseguroTransaction $transaction
    )
    {
        foreach ($this->days as $day) {
            //boleto
            if ($transaction->getType() == 0) {
                $ett = new AgpagseguroTicketReminder;
                $ett->setTransaction($transaction)
                    ->setScheduledDate(
                        (new \DateTime)
                            ->add(
                                new \DateInterval("P{$day}D")
                            )
                    );
                
                $this->em->persist($ett);
            }
        }

        $this->em->flush();
    }
}