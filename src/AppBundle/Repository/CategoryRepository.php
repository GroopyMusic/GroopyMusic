<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 12:25
 */

namespace AppBundle\Repository;


class CategoryRepository extends \Doctrine\ORM\EntityRepository
{
    public function findForRaking() {
        return $this->getEntityManager()->createQuery(
            'SELECT c, l, ct, lt, s, u
                  FROM AppBundle:Category c
                  LEFT JOIN c.translations ct
                  LEFT JOIN c.levels l
                  LEFT JOIN l.translations lt
                  LEFT JOIN l.statistics s
                  LEFT JOIN s.user u 
                  ORDER BY c.id ASC, l.step DESC, s.statistic DESC 
                  ')
            ->getResult();
    }

    public function findLevelsByCategories() {
        return $this->getEntityManager()->createQuery(
            'SELECT c,l
                  FROM AppBundle:Category c
                  LEFT JOIN c.translations ct
                  LEFT JOIN c.levels l
                  LEFT JOIN l.translations lt
                  ORDER BY c.id ASC, l.step DESC 
                  ')
            ->getResult();
    }
}