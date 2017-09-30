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
}