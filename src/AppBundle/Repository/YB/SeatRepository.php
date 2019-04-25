<?php

namespace AppBundle\Repository\YB;

use AppBundle\Entity\User;
use AppBundle\Entity\YB\Organization;

class SeatRepository extends \Doctrine\ORM\EntityRepository{

    public function getSeatFromBlock($blkID){
        return $this->createQueryBuilder('s')
            ->join('s.row', 'r')
            ->join('r.block', 'b')
            ->where('b.id = :id')
            ->setParameter('id', $blkID)
            ->getQuery()
            ->getResult();
    }

}