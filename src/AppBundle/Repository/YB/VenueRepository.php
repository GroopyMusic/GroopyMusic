<?php

namespace AppBundle\Repository\YB;


use AppBundle\Entity\YB\Venue;

class VenueRepository extends \Doctrine\ORM\EntityRepository
{
    public function findAllAddresses() {
        $venues = $this->createQueryBuilder('v')
            ->innerJoin('v.address', 'a')->getQuery()->getResult();

        $adrs = [];
        foreach($venues as $venue) {
            /** @var Venue $venue */
            $adrs[] = $venue->getAddress();
        }
        return $adrs;
    }

}