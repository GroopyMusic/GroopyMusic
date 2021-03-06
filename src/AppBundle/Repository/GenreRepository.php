<?php

namespace AppBundle\Repository;

/**
 * GenreRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GenreRepository extends \Doctrine\ORM\EntityRepository
{
    public function findForString($q, $locale) {
        return $this->getEntityManager()
            ->createQuery('SELECT g 
                                FROM AppBundle:Genre g 
                                LEFT JOIN g.translations t  
                                WHERE t.translatable = g 
                                AND t.name 
                                LIKE :q 
                                AND t.locale = :locale')
            ->setParameter('q', $q . '%')
            ->setParameter('locale', $locale)
            ->getResult();
    }
}
