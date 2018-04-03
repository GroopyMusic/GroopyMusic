<?php

namespace AppBundle\Repository;


use AppBundle\Entity\ContractArtist;
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

    // Dual of ContractArtistRepository::queryVisible()
    public function queryNotCurrentlyBusy(User $user = null) {
        $contract_repository = $this->getEntityManager()->getRepository('AppBundle:ContractArtist');
        $visible_contracts_ids = array_map(function(ContractArtist $ca) { return $ca->getId(); }, $contract_repository->findVisible());

        $nots = $this->createQueryBuilder('a')
            ->innerJoin('a.base_contracts', 'c', 'WITH','c.id in (:ids)' )
            ->select('a.id')
            // TODO make the next line work
            // to handle the fact that sold out situations shouldn't be considered as making an artist busy
            // ->andWhere('(r.hall is not null AND c.tickets_sold < r.hall.capacity) OR (r.hall is null AND c.tickets_sold < s.max_tickets)')
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

         return $qb->setParameter('ids' ,$visible_contracts_ids)
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

    public function findNotDeleted($q)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT a FROM AppBundle:Artist a WHERE a.deleted = false AND a.artistname LIKE :q')
            ->setParameter('q', $q['artistname'] . '%')
            ->getResult();
    }

    public function getArtistsForSelect()
    {
        return $this->getEntityManager()->createQuery(
            'SELECT a
                  FROM AppBundle:Artist a
                  WHERE a.deleted = FALSE
                  ')
            ->getResult();
    }
}