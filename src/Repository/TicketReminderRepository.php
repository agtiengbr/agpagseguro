<?php

namespace AGTI\PagSeguro\Repository;

use Doctrine\ORM\EntityRepository;

class TicketReminderRepository extends EntityRepository
{
    public function getRemindersToMail()
    {
        $data = $this->createQueryBuilder('r');

        $data->andWhere('r.scheduledDate < :date')
            ->setParameter('date', new \DateTime)
            ->andWhere(
                $data->expr()->orX(
                    'r.canceled = 0',
                    'r.canceled IS NULL'
                )
            )
            ->andWhere(
                $data->expr()->orX(
                    'r.sentDate IS NULL',
                    'r.sentDate = :zerodate'
                )
            )
            ->setParameter('zerodate', '0000-00-00 00:00:00');

        $r = $data->getQuery()->setMaxResults(100)
            ->getResult();
        return $r;
    }
}