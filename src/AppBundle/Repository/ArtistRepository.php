<?php

namespace AppBundle\Repository;


use AppBundle\Entity\User;

class ArtistRepository extends \Doctrine\ORM\EntityRepository
{
    public function findForUser(User $user) {
        return $this->createQueryBuilder('a')
            ->innerJoin('a.artists_user', 'au')
            ->leftJoin('a.contracts', 'c')
            ->where('a.artists_user = :user')
            ->setParameter('user', $user)
            ->andWhere('a.deleted = 0')
            ->getQuery()
            ->getResult()
        ;
    }

    public function queryNotCurrentlyBusy(User $user) {
        $nots = $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.contracts', 'c')
            ->andWhere('c.dateEnd > ' . (new \DateTime('now'))->format('d/m/Y'))
        ;

        $qb = $this->createQueryBuilder('a2');

        return $qb
            ->innerJoin('a2.artists_user', 'au')
            ->where('au.user = :user')
            ->andWhere('a2.deleted = 0')
            ->setParameter('user', $user)
            ->andWhere($qb->expr()->notIn('a2.id', $nots->getDQL()));
    }

    public function findNotCurrentlyBusy(User $user) {
        return $this->queryNotCurrentlyBusy($user)->getQuery()->getResult();
    }

    public function findNotDeletedBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteria = array_merge(['deleted' => false], $criteria);
        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);

        return $persister->loadAll($criteria, $orderBy, $limit, $offset);
    }
}