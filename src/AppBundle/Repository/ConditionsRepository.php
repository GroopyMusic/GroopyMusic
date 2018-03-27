<?php

namespace AppBundle\Repository;

class ConditionsRepository extends \Doctrine\ORM\EntityRepository
{
    public function findLast() {
        return $this
            ->createQueryBuilder('c')
            ->orderBy('c.date', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
}
