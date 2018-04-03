<?php

namespace AppBundle\Repository;

use Doctrine\ORM\QueryBuilder;

abstract class OptimizedRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @return QueryBuilder
     */
    public abstract function baseQueryBuilder();

    public function find($id, $lockMode = null, $lockVersion = null)
    {
        if($lockMode != null) {
            return parent::find($id, $lockMode, $lockVersion);
        }
        return $this->baseQueryBuilder()
            ->andWhere('a.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult()
            ;
    }
}