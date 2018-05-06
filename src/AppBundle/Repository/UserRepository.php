<?php

namespace AppBundle\Repository;

use Doctrine\ORM\Query;

class UserRepository extends \Doctrine\ORM\EntityRepository
{

    public function baseQueryBuilder()
    {
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
            ->addSelect('conditions');
    }

    public function findWithPaymentLastXDays($X)
    {
        return $this->baseQueryBuilder()
            ->innerJoin('u.payments', 'p')
            ->where('p.date > :date')
            ->setParameter('date', new \DateTime('today -' . $X . 'days'))
            ->getQuery()
            ->getResult();
    }

    public function findUsersWithRoles(array $roles)
    {
        $qb = $this->baseQueryBuilder();

        foreach ($roles as $role) {
            $qb->orWhere('u.roles LIKE :role')
                ->setParameter('role', '%' . $role . '%');
        }

        return $qb->getQuery()->getResult();
    }


    /**
     * Count all users' statistics results for the category
     * Mecenat + Productorat
     *
     * @return array statistics array
     */
    public function countUsersStatistic()
    {
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
     * count ambassadorat users statistic
     *
     * @return array ambassadorat statistic array
     */
    public function countUserAmbassadoratStatistic(){
        return $this->getEntityManager()->createQuery(
            'SELECT u.id, COUNT(s.id) as amb
                  FROM AppBundle:User u INDEX BY u.id
                  LEFT JOIN u.sponsorships s
                  LEFT JOIN s.target_invitation st
                  LEFT JOIN s.contract_artist sca
                  LEFT JOIN st.carts stc
                  LEFT JOIN stc.contracts stco
                  WHERE sca.successful = TRUE
                  AND u.deleted = FALSE
                  AND st.deleted = FALSE
                  AND stco.refunded = FALSE
                  AND stc.paid = TRUE 
                  GROUP BY u.id
                  ')
            ->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * count sponsorship validated user statistic
     *
     * @return array sponsorship validated users statistics array
     */
    public function countValidateSponsorshipInvitation(){
        return $this->getEntityManager()->createQuery(
            'SELECT u.id, COUNT(s.id) as v
                  FROM AppBundle:User u INDEX BY u.id
                  LEFT JOIN u.sponsorships s
                  WHERE s.target_invitation IS NOT NULL
                  GROUP BY u.id
                  ')
            ->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * count sponsorship user statistic
     *
     * @return array sponsorship users statistics array
     */
    public function countSponsorshipInvitation(){
        return $this->getEntityManager()->createQuery(
            'SELECT u.id, COUNT(si.id) as s
                  FROM AppBundle:User u INDEX BY u.id
                  LEFT JOIN u.sponsorships si
                  GROUP BY u.id
                  ')
            ->getResult(Query::HYDRATE_ARRAY);
    }

    /**
     * find user for select with search
     *
     * @param $q
     * @return array users array
     *
     */
    public function findUsersNotDeletedForSelect($q)
    {
        $querry = 'SELECT u FROM AppBundle:User u WHERE u.deleted = 0';
        foreach ($q as $index => $string) {
            if ($index == 0) {
                $querry = $querry . " AND (u.lastname LIKE '%" . $string . "%' OR u.firstname LIKE '%" . $string . "%'";
            } else {
                $querry = $querry . " OR u.lastname LIKE '%" . $string . "%' OR u.firstname LIKE '%" . $string . "%'";
            }
        }
        if (count($q) > 0) {
            $querry = $querry . ")";
        }
        return $this->getEntityManager()
            ->createQuery($querry)
            ->getResult();
    }

    /**
     * find user subscribed to newsletter for select with search
     *
     * @param $q
     * @return array users array
     */
    public function findNewsletterUsersNotDeletedForSelect($q)
    {
        $querry = 'SELECT u FROM AppBundle:User u WHERE u.deleted = 0 AND u.newsletter = 1';
        foreach ($q as $index => $string) {
            if ($index == 0) {
                $querry = $querry . " AND (u.lastname LIKE '%" . $string . "%' OR u.firstname LIKE '%" . $string . "%'";
            } else {
                $querry = $querry . " OR u.lastname LIKE '%" . $string . "%' OR u.firstname LIKE '%" . $string . "%'";
            }
        }
        if (count($q) > 0) {
            $querry = $querry . ")";
        }
        return $this->getEntityManager()
            ->createQuery($querry)
            ->getResult();
    }

    /**
     * get all users not deleted with stat
     *
     * @return array users array
     *
     */
    public function findUsersNotDeleted()
    {
        return $this->getEntityManager()->createQuery(
            'SELECT u,s,c
                  FROM AppBundle:User u
                  LEFT JOIN u.category_statistics s
                  LEFT JOIN s.category c
                  WHERE u.deleted = 0
                  ')
            ->getResult();
    }

    /**
     * get all user participants of contract_artist
     *
     * @param $contract_artist_id
     * @return array users array
     */
    public function getParticipants($contract_artist_id)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT u
                  FROM AppBundle:User u
                  LEFT JOIN u.carts c
                  LEFT JOIN c.contracts cf
                  LEFT JOIN cf.contractArtist ca
                  WHERE ca.id = ?1
                  ')
            ->setParameter(1, $contract_artist_id)
            ->getResult();
    }

    /**
     * check if user is participant of contract_artist
     *
     * @param $contract_artist_id
     * @param $user_id
     * @return mixed user_participant or null
     */
    public function isParticipant($contract_artist_id, $user_id)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT u
                  FROM AppBundle:User u
                  LEFT JOIN u.carts c
                  LEFT JOIN c.contracts cf
                  LEFT JOIN cf.contractArtist ca
                  WHERE ca.id = ?1
                  AND u.id = ?2
                  ')
            ->setParameter(1, $contract_artist_id)
            ->setParameter(2, $user_id)
            ->getOneOrNullResult();
    }

    /**
     * check if email is already subsribed to the plateforme
     *
     * @param $email
     * @return mixed user's email or null
     */
    public function emailExists($email)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT u
                  FROM AppBundle:User u
                  WHERE u.email = ?1
                  ')
            ->setParameter(1, $email)
            ->getOneOrNullResult();
    }
}