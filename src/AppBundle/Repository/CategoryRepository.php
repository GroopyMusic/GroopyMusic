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
            'SELECT c 
                  FROM AppBundle:Category c 
                  LEFT JOIN c.translations ct
                  LEFT JOIN c.level l
                  LEFT JOIN l.translations lt
                  WHERE l.category = c 
                  AND ct.translatable = c
                  AND lt.translatable = l
                  AND ct.locale = :locale
                  AND lt.locale = :locale')
            ->setParameter('locale', 'fr')
            ->getResult();
    }
}