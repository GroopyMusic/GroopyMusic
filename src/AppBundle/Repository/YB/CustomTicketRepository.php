<?php

namespace AppBundle\Repository\YB;


class CustomTicketRepository extends \Doctrine\ORM\EntityRepository{

    public function findByYBContractArtist($campaignID){
        return $this->createQueryBuilder('ct')
            ->join('ct.campaign', 'c')
            ->where('c.id = :id')
            ->setParameter('id', $campaignID)
            ->getQuery()
            ->getOneOrNullResult();
    }

}