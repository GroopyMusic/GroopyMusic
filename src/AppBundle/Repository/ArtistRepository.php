<?php

namespace AppBundle\Repository;


use AppBundle\Entity\User;

class ArtistRepository extends \Doctrine\ORM\EntityRepository
{
    public function findForUser(User $user) {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.artists_user', 'au')
            ->leftJoin('a.contracts', 'c')
            ->where('au.user = :user')
            ->setParameter('user', $user)
            ->andWhere('a.deleted = 0')
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryNotCurrentlyBusy(User $user = null) {
        $nots = $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.contracts', 'c')
            ->leftJoin('c.step', 's')
            ->leftJoin('c.preferences', 'p')
            ->leftJoin('c.reality', 'r')
            ->andWhere('c.failed = 0')
            ->andWhere('(r.date is not null AND r.date > :now) OR (p.date > :now)')
            ->andWhere('(c.dateEnd > :now) OR (c.tickets_sold >= s.min_tickets)')
            ->andWhere('c.tickets_sold < s.max_tickets')
        ;

        $qb = $this->createQueryBuilder('a2');

        // If he's not an admin, the user must own the artist
        if($user != null) {
            $qb
                ->innerJoin('a2.artists_user', 'au')
                ->where('au.user = :user')
                ->setParameter('user', $user)
            ;
        }
        return $qb
                ->setParameter('now', new \DateTime('now'))
                ->andWhere('a2.deleted = 0')
                ->andWhere($qb->expr()->notIn('a2.id', $nots->getDQL()));
    }

    public function findNotCurrentlyBusy(User $user) {
        return $this->queryNotCurrentlyBusy($user)->getQuery()->getResult();
    }

    // Handles the case where an admin wants to create an event
    public function findAvailableForNewContract(User $user) {
        // TODO ROLE_ADMIN
        if($user->hasRole('ROLE_SUPER_ADMIN')) {
            return $this->queryNotCurrentlyBusy(null)->getQuery()->getResult();
        }
        else {
            return $this->findNotCurrentlyBusy($user);
        }
    }

    public function findNotDeletedBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteria = array_merge(['deleted' => false], $criteria);
        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);

        return $persister->loadAll($criteria, $orderBy, $limit, $offset);
    }
}