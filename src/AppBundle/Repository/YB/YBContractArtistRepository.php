<?php

namespace AppBundle\Repository\YB;


use AppBundle\Entity\User;

class YBContractArtistRepository extends \Doctrine\ORM\EntityRepository
{
    public function getCurrentYBCampaigns($user = null) {

        $qb = $this->createQueryBuilder('c');
        if($user instanceof User) {
            $qb
                ->join('c.handlers', 'u')
                ->addSelect('u')
                ->where('u.id = :id')
                ->setParameter('id', $user->getId());
        } else {
            $qb
                ->leftJoin('c.handlers', 'u')
                ->addSelect('u');
        }
        return $qb
            ->andWhere('c.date_closure >= :now')
            ->andWhere('c.failed = 0')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult()
        ;
    }

    public function getPassedYBCampaigns(User $user) {
        return $this->createQueryBuilder('c')
            ->join('c.handlers', 'u')
            ->where('u.id = :id')
            ->andWhere('c.date_closure < :now OR c.failed = 1')
            ->setParameters([
                'id' => $user->getId(),
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getResult()
            ;
    }
}