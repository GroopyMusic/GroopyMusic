<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/03/2018
 * Time: 12:25
 */

namespace AppBundle\Repository;


class CategoryRepository  extends \Doctrine\ORM\EntityRepository
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
                  WHERE l.category = c 
                  AND ct.translatable = c
                  AND lt.translatable = l
                  AND ct.locale = :locale
                  AND lt.locale = :locale
                  ')
            ->setParameter('locale', 'fr')
            ->getResult();
    }
}