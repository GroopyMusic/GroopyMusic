<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 28/03/2018
 * Time: 15:00
 */

namespace AppBundle\Repository;

/**
 * RewardRestrictionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class RewardRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * Recovers all unremoved rewards
     *
     * @return array reward array
     */
    public function findNotDeletedRewards($locale)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT r, rt, rw
                  FROM AppBundle:Reward r
                  LEFT JOIN r.restrictions rw
                  LEFT JOIN r.translations rt
                  WHERE r.deletedAt IS NULL
                  AND rt.locale = :locale
                  ORDER BY rt.name ASC
                  ')
            ->setParameter('locale', $locale)
            ->getResult();
    }

    /**
     * Retrieve the reward corresponding to the @param $id
     *
     * @param $id
     * @return mixed reward
     */
    public function getReward($id)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT r, rt, rw
                  FROM AppBundle:Reward r
                  LEFT JOIN r.restrictions rw
                  LEFT JOIN r.translations rt
                  WHERE r.id = ?1
                  ')
            ->setParameter(1, $id)
            ->getSingleResult();
    }
}