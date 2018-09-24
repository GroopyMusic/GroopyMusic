<?php

namespace AppBundle\Repository\YB;


use AppBundle\Entity\User;

class YBContractArtistRepository extends \Doctrine\ORM\EntityRepository
{
    public function getCurrentYBCampaigns(User $user) {
        return $this->createQueryBuilder('c')
            ->join('c.handlers', 'u')
            ->where('u.id = :id')
            ->andWhere('c.date_closure >= :now')
            ->andWhere('c.failed = 0')
            ->setParameters([
                'id' => $user->getId(),
                'now' => new \DateTime(),
            ])
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