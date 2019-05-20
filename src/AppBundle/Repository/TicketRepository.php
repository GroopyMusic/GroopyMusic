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

    public function getTicketsFromEventAndCp($event_id, $cp_id){
        return $this->createQueryBuilder('t')
            ->where('t.contractArtist = ?1')
            ->andWhere('t.counterPart = ?2')
            ->setParameter('1', $event_id)
            ->setParameter('2', $cp_id)
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

    public function getTicketsForCounterpart($cpID){
        return $this->createQueryBuilder('t')
            ->join('t.counterPart', 'cp')
            ->where('cp.id = :id')
            ->setParameter('id', $cpID)
            ->getQuery()
            ->getResult();
    }

    public function getDuplicates()
    {
        return $this->getEntityManager()->createQuery(
            'SELECT t
                  FROM AppBundle:Ticket t
                  WHERE t.barcode_text IN (SELECT t2.barcode_text FROM AppBundle:Ticket t2 WHERE t2.barcode_text = t.barcode_text AND t2.id <> t.id)
                  ');
    }

}