<?php

namespace AppBundle\Repository;


use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\User;
use AppBundle\Services\UserRolesManager;

class ArtistRepository extends \Doctrine\ORM\EntityRepository
{
    private function baseQueryBuilder() {
        return $this->createQueryBuilder('a')
            ->leftJoin('a.contracts', 'ca')
            ->leftJoin('a.genres', 'g')
            ->leftJoin('a.photos', 'p')
            ->leftJoin('a.profilepic', 'pp')
            ->leftJoin('a.province', 'province')
            ->addSelect('ca')
            ->addSelect('g')
            ->addSelect('p')
            ->addSelect('pp')
            ->addSelect('province')
        ;
    }

    public function findForUser(User $user) {
        return $this->baseQueryBuilder()
            ->innerJoin('a.artists_user', 'au')
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

        $nots = $this->createQueryBuilder('a1')
            ->innerJoin('a1.base_contracts', 'c', 'WITH','c.id in (:ids)' )
            ->select('a1.id')
            // TODO make the next line work
            // to handle the fact that sold out situations shouldn't be considered as making an artist busy
            // ->andWhere('(r.hall is not null AND c.tickets_sold < r.hall.capacity) OR (r.hall is null AND c.tickets_sold < s.max_tickets)')
        ;

        $qb = $this->baseQueryBuilder();

        // If he's not an admin, the user must own the artist
        if($user != null) {
            $qb
                ->innerJoin('a.artists_user', 'au')
                ->where('au.user = :user')
                ->setParameter('user', $user)
            ;
        }

         return $qb->setParameter('ids' ,$visible_contracts_ids)
                ->andWhere('a.deleted = 0')
                ->andWhere('a.visible = 1')
                ->andWhere($qb->expr()->notIn('a.id', $nots->getDQL()));
    }

    public function findNotCurrentlyBusy(User $user) {
        return $this->queryNotCurrentlyBusy($user)->getQuery()->getResult();
    }

    // Handles the case where an admin wants to create an event
    public function findAvailableForNewContract(User $user, UserRolesManager $rolesManager) {
        if($rolesManager->userHasRole($user, 'ROLE_ADMIN')) {
            return $this->queryNotCurrentlyBusy(null)->getQuery()->getResult();
        }
        else {
            return $this->findNotCurrentlyBusy($user);
        }
    }

    public function findVisible() {
        return $this->baseQueryBuilder()
            ->where('a.deleted = 0')
            ->andWhere('a.visible = 1')
            ->orderBy('a.artistname', 'ASC')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNewArtists($limit) {

        return $this->baseQueryBuilder()
            ->where('a.deleted = 0')
            ->andWhere('a.visible = 1')
            ->orderBy('a.date_creation', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }

    public function findNotDeleted($q)
    {
        return $this->getEntityManager()
            ->createQuery('SELECT a FROM AppBundle:Artist a WHERE a.deleted = false AND a.visible = 1 AND a.artistname LIKE :q')
            ->setParameter('q', $q['artistname'] . '%')
            ->getResult();
    }
}