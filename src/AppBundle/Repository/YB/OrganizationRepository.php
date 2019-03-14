<?php

namespace AppBundle\Repository\YB;

use AppBundle\Entity\User;
use AppBundle\Entity\YB\Organization;

class OrganizationRepository extends \Doctrine\ORM\EntityRepository{

    public function findAllButDeleted(){
        return $this->createQueryBuilder('o')
            ->where('o.deleted_at IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function findByNamePattern($pattern){
        return $this->createQueryBuilder('o')
            ->where('o.name LIKE :pattern')
            ->setParameter('pattern', $pattern)
            ->getQuery()
            ->getResult();
    }

}