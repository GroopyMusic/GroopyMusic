<?php

namespace AppBundle\Repository;
use AppBundle\Entity\UserArtist;

/**
 * ContractArtistRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContractArtistRepository extends \Doctrine\ORM\EntityRepository
{
    public function findCurrentForArtist(UserArtist $artist) {
        return $this->createQueryBuilder('c')
            ->where('c.artist = :artist')
            ->andWhere('c.dateEnd > :now')
            ->setParameter('artist', $artist)
            ->setParameter('now', new \DateTime('now'))
            ->join('c.step', 's')
            ->addSelect('s')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function findCurrents() {
        return $this->createQueryBuilder('c')
            ->where('c.dateEnd > :now')
            ->join('c.artist', 'a')
            ->addSelect('a')
            ->join('c.step', 's')
            ->addSelect('s')
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult();
    }

}
