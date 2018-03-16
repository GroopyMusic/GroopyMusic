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

    public function countUserStatistic($user_id){
        return $this->getEntityManager()->createQuery(
            'SELECT COUNT(t.id),COUNT(ca.id)
                  FROM AppBundle:User u
                  LEFT JOIN u.payments p
                  LEFT JOIN p.contractFan cf 
                  LEFT JOIN p.contractArtist ca
                  LEFT JOIN cf.tickets t
                  WHERE u.id = ?1
                  AND ca.successful = TRUE
                  ')
            ->setParameter(1, $user_id)
            ->getScalarResult();
    }

}