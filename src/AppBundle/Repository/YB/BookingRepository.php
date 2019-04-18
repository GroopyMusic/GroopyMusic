<?php

namespace AppBundle\Repository\YB;


class BookingRepository extends \Doctrine\ORM\EntityRepository{

    public function getBookingForEventAndBlock($campaignId, $blockId){
        return $this->createQueryBuilder('b')
            ->join('b.reservation', 'rsv')
            ->join('rsv.block', 'blk')
            ->join('b.purchase', 'p')
            ->join('p.contractFan', 'cf')
            ->join('cf.contractArtist', 'ca')
            ->where('ca.id = :campaign')
            ->andWhere('blk.id = :blk')
            ->setParameter('campaign', $campaignId)
            ->setParameter('blk', $blockId)
            ->getQuery()
            ->getResult();
    }

    public function getBookingForEvent($campaignId){
        return $this->createQueryBuilder('b')
            ->join('b.purchase', 'p')
            ->join('p.contractFan', 'cf')
            ->join('cf.contractArtist', 'ca')
            ->where('ca.id = :campaign')
            ->setParameter('campaign', $campaignId)
            ->getQuery()
            ->getResult();
    }

    public function getTimedoutReservations(){
        return $this->createQueryBuilder('b')
            ->where('b.bookingDate < :delay')
            ->andWhere('b.isBooked = 0')
            ->setParameter('delay', (new \DateTime())->modify('-15 minutes'))
            ->getQuery()
            ->getResult();
    }

    public function getBookingSeatsForConfig($config){
        return $this->createQueryBuilder('b')
            ->join('b.reservation', 'rsv')
            ->join('rsv.block', 'blk')
            ->join('blk.config', 'config')
            ->where('config = :id')
            ->setParameter('id', $config)
            ->getQuery()
            ->getResult();
    }

    public function getBookingOfPurchase($cartID){
        return $this->createQueryBuilder('b')
            ->join('b.purchase', 'p')
            ->join('p.contractFan', 'cf')
            ->join('cf.cart', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $cartID)
            ->getQuery()
            ->getResult();
    }
}