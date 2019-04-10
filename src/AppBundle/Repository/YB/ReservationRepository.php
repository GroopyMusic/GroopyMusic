<?php

namespace AppBundle\Repository\YB;

use AppBundle\Entity\YB\YBContractArtist;

class OrganizationRepository extends \Doctrine\ORM\EntityRepository{

    public function getReservationsForEvent(YBContractArtist $campaign){
        $this->createQueryBuilder('rsv')
            ->where('rsv.campaign = :id')
            ->setParameter('id', $campaign->getId())
            ->getQuery()
            ->getResult();
    }
}