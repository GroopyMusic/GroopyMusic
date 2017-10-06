<?php

namespace AppBundle\Repository;


class ArtistRepository extends \Doctrine\ORM\EntityRepository
{
    public function queryNotCurrentlyBusy() {
        $nots = $this->createQueryBuilder('a')
            ->select('a.id')
            ->innerJoin('a.contracts', 'c')
            ->where('c.dateEnd > ' . (new \DateTime('now'))->format('d/m/Y'));

        $qb = $this->createQueryBuilder('a2');

        return $qb->where($qb->expr()->notIn('a2.id', $nots->getDQL()));
    }

    public function findNotDeletedBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $criteria = array_merge(['deleted' => false], $criteria);
        $persister = $this->_em->getUnitOfWork()->getEntityPersister($this->_entityName);

        return $persister->loadAll($criteria, $orderBy, $limit, $offset);
    }
}