<?php

namespace AppBundle\Repository;


class InformationSessionRepository extends \Doctrine\ORM\EntityRepository
{
    public function queryBuilderVisible() {
        return $this->createQueryBuilder('i')
            ->where('i.date > :now')
            ->orderBy('i.date', 'ASC')
            ->setParameter('now', new \DateTime())
        ;
    }

    public function queryVisible() {
        return $this->queryBuilderVisible()->getQuery();
    }

    public function findVisible() {
        return $this->queryVisible()->getResult();
    }

}