<?php

namespace AppBundle\Repository;


use AppBundle\Entity\Artist;

class ContractArtistSalesRepository extends \Doctrine\ORM\EntityRepository
{
    private function queryVisible() {
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
        ;
    }

    /**
     * Returns 0-$limit contracts for which there are tickets to buy & that are visible
     */
    public function findVisible($limit = null)
    {
       return $this->queryVisible()
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCurrentsForArtist(Artist $artist) {
        return $this->queryVisible()
            ->andWhere('c.artist = :artist')
            ->setParameter('artist', $artist)
            ->getQuery()
            ->getResult()
        ;
    }
}