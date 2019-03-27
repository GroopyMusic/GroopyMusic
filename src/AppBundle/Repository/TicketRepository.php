<?php

namespace AppBundle\Repository;

class TicketRepository extends \Doctrine\ORM\EntityRepository {
    
    public function getTicketsFromEvent($event_id){
        return $this->createQueryBuilder('t')
            ->where('t.contractArtist = ?1')
            ->setParameter('1', $event_id)
            ->getQuery()
            ->getResult();
    }

    public function getScannedTicketsFromEvent($event_id){
        return $this->createQueryBuilder('t')
            ->where('t.contractArtist = ?1')
            ->andWhere('t.validated = 1')
            ->setParameter('1', $event_id)
            ->getQuery()
            ->getResult();
    }

    public function getYetToBeScannedTicketsFromEvent($event_id){
        return $this->createQueryBuilder('t')
            ->where('t.contractArtist = ?1')
            ->andWhere('t.validated = 0')
            ->setParameter('1', $event_id)
            ->getQuery()
            ->getResult();
    }

}