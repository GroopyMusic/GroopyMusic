<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Query;

class LevelRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Count the number of statistics in each level
     *
     * @return array : Key = level, value = number of statistics
     *
     */
    public function countMaximums()
    {
        return $this->getEntityManager()->createQuery(
            'SELECT l, COUNT( s.id) AS maxi
                  FROM AppBundle:Level l INDEX BY l.id
                  LEFT JOIN l.statistics s
                  GROUP BY l.id
                  ')
            ->getResult();
    }
}