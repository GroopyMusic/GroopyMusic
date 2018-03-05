<?php

namespace AppBundle\Repository;

class UserRepository extends \Doctrine\ORM\EntityRepository {

    public function findWithPaymentLastXDays($X) {
        return $this->createQueryBuilder('u')
            ->innerJoin('u.payments', 'p')
            ->where('p.date > :date')
            ->setParameter('date', new \DateTime('today -' . $X . 'days'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findUsersWithRoles(array $roles) {
        $qb = $this->createQueryBuilder('u');

        foreach($roles as $role) {
            $qb->orWhere('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        return $qb->getQuery()->getResult();
    }

}