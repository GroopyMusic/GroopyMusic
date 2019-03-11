<?php

namespace AppBundle\Repository\YB;


use AppBundle\Entity\User;
use AppBundle\Entity\YB\Organization;

class MembershipRepository extends \Doctrine\ORM\EntityRepository {

    public function hasNameOfMember(Organization $org, $name){
        $this->createQueryBuilder('part')
            ->join('part.member', 'u')
            ->where('u.displayName = :name')
            ->andWhere('part.organization = :org')
            ->setParameters([
                'name' => $name,
                'org' => $org->getId(),
            ])
            ->getQuery()
            ->getResult();
    }

}