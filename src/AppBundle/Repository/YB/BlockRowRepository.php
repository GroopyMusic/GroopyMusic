<?php

namespace AppBundle\Repository\YB;

use AppBundle\Entity\User;
use AppBundle\Entity\YB\Organization;

class BlockRowRepository extends \Doctrine\ORM\EntityRepository{

    public function getRowsFromVenue($id){
        return $this->createQueryBuilder('row')
            ->join('row.block', 'b')
            ->join('b.config', 'conf')
            ->join('conf.venue', 'v')
            ->where('v.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getResult();
    }

}