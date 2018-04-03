<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping;
use Doctrine\ORM\QueryBuilder;

abstract class OptimizedRepository extends \Doctrine\ORM\EntityRepository
{
    protected $short_name;

    /**
     * @return QueryBuilder
     */
    public abstract function baseQueryBuilder();

    /**
     * @return void
     */
    public abstract function initShortName();

    public function __construct(EntityManager $em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);
        $this->initShortName();
    }

    public function find($id, $lockMode = null, $lockVersion = null)
    {
        if($lockMode != null) {
            return parent::find($id, $lockMode, $lockVersion);
        }
        return $this->baseQueryBuilder()
            ->andWhere($this->short_name . '.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult()
            ;
    }

    public function findAll()
    {
        return $this->baseQueryBuilder()
            ->getQuery()
            ->getArrayResult()
        ;
    }

    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $qb = $this->baseQueryBuilder();

        foreach($criteria as $key => $criterion) {
            $qb->andWhere($this->short_name . '.' . $key . ' = ' . $criterion);
        }

        if($orderBy != null) {
            foreach ($orderBy as $key => $value) {
                $qb->addOrderBy($this->short_name . '.' . $key, $value);
            }
        }

        if($limit != null) {
            $qb->setMaxResults($limit);
        }
        if($offset != null) {
            $qb->setFirstResult($offset);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function findOneBy(array $criteria, array $orderBy = null)
    {
        $qb = $this->baseQueryBuilder();

        foreach($criteria as $key => $criterion) {
            $qb->andWhere($this->short_name . '.' . $key . ' = ' . $criterion);
        }

        if($orderBy != null) {
            foreach ($orderBy as $key => $value) {
                $qb->addOrderBy($this->short_name . '.' . $key, $value);
            }
        }

        return $qb->setMaxResults(1)
            ->getQuery()
            ->getSingleResult()
        ;
    }
}