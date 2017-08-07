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
    public function findForString($q) {
        return $this->getEntityManager()->createQuery('SELECT g FROM AppBundle:Genre g WHERE g.name LIKE :q')
            ->setParameter('q', $q . '%')
            ->getResult();
    }

}
