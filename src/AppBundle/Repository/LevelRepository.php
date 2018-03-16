<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 12:24
 */

namespace AppBundle\Repository;


class LevelRepository  extends \Doctrine\ORM\EntityRepository
{
    public function findCorrectLevel($category,$min_step){
        return $this->getEntityManager()->createQuery(
            'SELECT l
                  FROM AppBundle:Level l
                  WHERE l.category = ?1
                  AND l.step >= ?2
                  ORDER BY l.step ASC
                  ')
            ->setParameter(1,$category)
            ->setParameter(2, $min_step)
            ->setMaxResults(1)
            ->getResult();
    }
}