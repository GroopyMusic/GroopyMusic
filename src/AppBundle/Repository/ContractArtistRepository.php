<?php

namespace AppBundle\Repository;
use AppBundle\Command\FailedContractCommand;
use AppBundle\Command\KnownOutcomeContractCommand;
use AppBundle\Entity\Artist;

/**
 * ContractArtistRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContractArtistRepository extends \Doctrine\ORM\EntityRepository
{
    public function findSuccessful() {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.contractsFan', 'cf')
            ->join('c.reality', 'r')
            ->addSelect('cf')
            ->addSelect('r')
            ->where('c.successful = 1')
            ->andWhere('r.date > :now')
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findCurrentForArtist(Artist $artist) {
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

    /**
     * Returns 0-$limit contracts for which the deadline is not passed AND not enough money is raised at the moment
     */
    public function findNotSuccessfulYet($limit = null) {
        $qb = $this->createQueryBuilder('c')
            ->where('c.dateEnd > :now')
            ->andWhere('c.collected_amount < s.requiredAmount')
            ->join('c.artist', 'a')
            ->addSelect('a')
            ->join('c.step', 's')
            ->addSelect('s')
            ->setParameter('now', new \DateTime('now'))
        ;

        if($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns 0-$limit contracts for which there are tickets to buy
     */
    public function findWithAvailableCounterParts($limit = null) {
        $qb = $this->createQueryBuilder('c')
            ->join('c.artist', 'a')
            ->join('c.step', 's')
            ->leftJoin('c.reality', 'r')
            ->addSelect('a')
            ->addSelect('s')
            ->addSelect('r')
            ->where('r.date is null OR r.date > :now')
            ->andWhere('c.tickets_sold < s.max_tickets')
        ;

        if($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @see KnownOutcomeContractCommand
     */
    public function findNewlyFailed() {
        return $this->createQueryBuilder('c')
            ->join('c.artist', 'a')
            ->join('a.artists_user', 'au')
            ->join('c.step', 's')
            ->addSelect('a')
            ->addSelect('au')
            ->addSelect('s')
            ->where('c.dateEnd < :now')
            ->andWhere('c.successful = 0')
            ->andWhere('c.failed = 0') // Not marked as failed yet
            ->andWhere('c.collected_amount < s.requiredAmount')
            ->setParameter('now', new \DateTime('now'))
            ->getQuery()
            ->getResult();
    }

    /**
     * @see KnownOutcomeContractCommand
     */
    public function findNewlySuccessful() {
        return $this->createQueryBuilder('c')
            ->join('c.artist', 'a')
            ->join('a.artists_user', 'au')
            ->join('c.step', 's')
            ->addSelect('a')
            ->addSelect('au')
            ->addSelect('s')
            ->andWhere('c.failed = 0')
            ->andWhere('c.successful = 0') // Not marked as successful yet
            ->andWhere('c.collected_amount >= s.requiredAmount')
            ->getQuery()
            ->getResult();
    }

}
