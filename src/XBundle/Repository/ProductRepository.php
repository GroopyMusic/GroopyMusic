<?php

namespace XBundle\Repository;

/**
 * ProductRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProductRepository extends \Doctrine\ORM\EntityRepository
{
    public function getProductsForProject($project) {
        return $this->createQueryBuilder('prod')
            ->join('prod.project', 'proj')
            ->where('proj.id = :id')
            ->andWhere('prod.deleted = 0')
            ->orderBy('prod.name', 'ASC')
            ->setParameter('id', $project->getId())
            ->getQuery()
            ->getResult()
        ;
    }

    public function getVisibleProductsForProject($project) {
        return $this->createQueryBuilder('prod')
            ->join('prod.project', 'proj')
            ->where('proj.id = :id')
            ->andWhere('prod.validated = 1')
            ->orderBy('prod.name', 'ASC')
            ->setParameter('id', $project->getId())
            ->getQuery()
            ->getResult()
        ;
    }
}