<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Query;

class UserRepository extends \Doctrine\ORM\EntityRepository {

    public function baseQueryBuilder() {
        return $this->createQueryBuilder('u')
            ->leftJoin('u.artists_user', 'au')
            ->leftJoin('u.genres', 'g')
            ->leftJoin('u.notifications', 'n')
            ->leftJoin('u.user_conditions', 'uc')
            ->leftJoin('uc.conditions', 'conditions')
            ->addSelect('au')
            ->addSelect('g')
            ->addSelect('n')
            ->addSelect('uc')
            ->addSelect('conditions')
        ;
    }

    public function findWithPaymentLastXDays($X) {
        return $this->baseQueryBuilder()
            ->innerJoin('u.payments', 'p')
            ->where('p.date > :date')
            ->setParameter('date', new \DateTime('today -' . $X . 'days'))
            ->getQuery()
            ->getResult()
        ;
    }

    public function findUsersWithRoles(array $roles) {
        $qb = $this->baseQueryBuilder();

        foreach($roles as $role) {
            $qb->orWhere('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * Count all users' statistics results for the category
     *
     * @return array
     */
    public function countUsersStatistic(){
        return $this->getEntityManager()->createQuery(
            'SELECT u.id, SUM(p.quantity) AS pr, COUNT( DISTINCT ca.id) AS me
                  FROM AppBundle:User u INDEX BY u.id
                  LEFT JOIN u.carts c
                  LEFT JOIN c.contracts co
                  LEFT JOIN co.contractArtist ca
                  LEFT JOIN co.purchases p
                  LEFT JOIN u.user_conditions uc
                  LEFT JOIN uc.conditions cond
                  WHERE ca.successful = TRUE
                  AND u.deleted = FALSE
                  AND co.refunded = FALSE
                  AND c.paid = TRUE
                  GROUP BY u.id
                  ')
                ->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * get all users not deleted
     *
     * @return array
     *
     */
    public function findUsersNotDeleted(){
        return $this->getEntityManager()->createQuery(
            'SELECT u,s,c
                  FROM AppBundle:User u
                  LEFT JOIN u.category_statistics s
                  LEFT JOIN s.category c
                  WHERE u.deleted = 0
                  ')
            ->getResult();
    }
}