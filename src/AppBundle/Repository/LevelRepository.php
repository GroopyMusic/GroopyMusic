<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 12:24
 */

namespace AppBundle\Repository;

use Doctrine\ORM\Query;

class LevelRepository extends \Doctrine\ORM\EntityRepository
{
    
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