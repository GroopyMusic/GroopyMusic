<?php

namespace AppBundle\Repository\YB;

use AppBundle\Entity\YB\YBContractArtist;

class ReservationRepository extends \Doctrine\ORM\EntityRepository{

    public function getReservationsFromBlockRowSeat($blk, $row, $seat){
        return $this->createQueryBuilder('rsv')
            ->join('rsv.block', 'blk')
            ->where('blk.id = :blk')
            ->andWhere('rsv.rowIndex = :row')
            ->andWhere('rsv.seatIndex = :seat')
            ->setParameter('blk', $blk)
            ->setParameter('row', $row)
            ->setParameter('seat', $seat)
            ->getQuery()
            ->getOneOrNullResult();
    }
}