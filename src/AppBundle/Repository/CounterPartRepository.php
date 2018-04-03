<?php

namespace AppBundle\Repository;

/**
 * CounterPartRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class CounterPartRepository extends \Doctrine\ORM\EntityRepository
{

    public function getCounterPartsForSelect()
    {
        return $this->getEntityManager()->createQuery(
            'SELECT c, ct
                  FROM AppBundle:CounterPart c
                  LEFT JOIN c.translations ct
                  ')
            ->getResult();
    }
}
