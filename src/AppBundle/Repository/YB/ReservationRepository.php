<?php

namespace AppBundle\Repository\YB;

use AppBundle\Entity\YB\YBContractArtist;

class ReservationRepository extends \Doctrine\ORM\EntityRepository{

    public function getReservationsForEvent($campaignID){
        return $this->createQueryBuilder('rsv')
            ->join('rsv.counterpart', 'cp')
            ->join('cp.contractArtist', 'campaign')
            ->where('campaign.id = :id')
            ->setParameter('id', $campaignID)
            ->getQuery()
            ->getResult();
    }

    public function getReservationsForEventAndBlock($campaignID, $blockID){
        return $this->createQueryBuilder('rsv')
            ->join('rsv.counterpart', 'cp')
            ->join('cp.contractArtist', 'campaign')
            ->join('rsv.block', 'blk')
            ->where('campaign.id = :id')
            ->andWhere('blk.id = :blkid')
            ->setParameter('id', $campaignID)
            ->setParameter('blkid', $blockID)
            ->getQuery()
            ->getResult();
    }

    public function getTimedoutReservations(){
        return $this->createQueryBuilder('rsv')
            ->where('rsv.bookingDate < :delay')
            ->andWhere('rsv.isBooked = 0')
            ->setParameter('delay', (new \DateTime())->modify('-20 minutes'))
            ->getQuery()
            ->getResult();
    }
}