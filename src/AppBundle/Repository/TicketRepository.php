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

    public function getPresale($event_id){
        return $this->createQueryBuilder('t')
            ->where('t.contractArtist = ?1')
            ->andWhere('t.isBoughtOnSite = 0')
            ->setParameter('1', $event_id)
            ->getQuery()
            ->getResult();
    }

    public function getNbPresaleScannedFromEvent($event_id){
        return $this->createQueryBuilder('t')
            ->where('t.contractArtist = ?1')
            ->andWhere('t.validated = 1')
            ->andWhere('t.isBoughtOnSite = 0')
            ->setParameter('1', $event_id)
            ->getQuery()
            ->getResult();
    }

    public function getNbPaidInCashFromEvent($event_id){
        return $this->createQueryBuilder('t')
            ->where('t.contractArtist = ?1')
            ->andWhere('t.paidInCash = 1')
            ->setParameter('1', $event_id)
            ->getQuery()
            ->getResult();
    }

    public function getNbBoughtOnSiteFromEvent($event_id){
        return $this->createQueryBuilder('t')
            ->where('t.contractArtist = ?1')
            ->andWhere('t.isBoughtOnSite = 1')
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