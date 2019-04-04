<?php

namespace AppBundle\Repository\YB;

use AppBundle\Entity\User;
use AppBundle\Entity\YB\Organization;

class VenueConfigRepository extends \Doctrine\ORM\EntityRepository{

    public function findAllOpened(){
        return $this->createQueryBuilder('vc')
            ->where('vc.venue IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

}