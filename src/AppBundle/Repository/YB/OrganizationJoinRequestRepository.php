<?php

namespace AppBundle\Repository\YB;

use AppBundle\Entity\User;
use AppBundle\Entity\YB\Organization;

class OrganizationJoinRequestRepository extends \Doctrine\ORM\EntityRepository{

    public function findByUserAndOrga(Organization $organization, User $user){
        return $this->createQueryBuilder('request')
            ->where('request.email = :email')
            ->andWhere('request.organization = :org')
            ->setParameters([
                'email' => $user->getEmail(),
                'org' => $organization
            ])
            ->getQuery()
            ->getOneOrNullResult();
    }

}