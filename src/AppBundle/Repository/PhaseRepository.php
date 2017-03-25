<?php

namespace AppBundle\Repository;

/**
 * PhaseRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class PhaseRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllWithSteps() {
        return $this->createQueryBuilder('p')
            ->join('p.steps', 's')
            ->addSelect('s')
            ->orderBy('p.num', 'ASC')
            ->addOrderBy('s.num', 'ASC')
            ->getQuery()
            ->getResult();
    }

}