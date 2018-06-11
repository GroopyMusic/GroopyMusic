<?php

namespace AppBundle\Repository;


class ContractArtistSalesRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Returns 0-$limit contracts for which there are tickets to buy & that are visible
     */
    public function findVisible($limit = null)
    {
        return $this->createQueryBuilder('c')
            ->join('c.artist', 'a')
            ->join('c.step', 's')
            ->leftJoin('s.counterParts', 'cp')
            ->leftJoin('a.genres', 'ag')
            ->leftJoin('a.photos', 'ap')
            ->leftJoin('ag.translations', 'agt')
            ->leftJoin('cp.translations', 'cpt')
            ->leftJoin('a.translations', 'at')
            ->addSelect('a')
            ->addSelect('s')
            ->addSelect('cp')
            ->addSelect('ag')
            ->addSelect('at')
            ->addSelect('agt')
            ->addSelect('cpt')
            ->addSelect('ap')
            ->orderBy('c.dateEnd', 'ASC')
            ->where('c.failed = 0')
            ->andWhere('c.dateEnd > :yesterday')
            ->setParameter('yesterday', new \DateTime('yesterday'))
            ->getQuery()
            ->getResult()
        ;
    }
}