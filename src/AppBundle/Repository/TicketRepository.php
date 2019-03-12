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

}