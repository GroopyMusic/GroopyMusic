<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 25/04/2018
 * Time: 11:41
 */

namespace AppBundle\Repository;


use Doctrine\ORM\EntityRepository;

class SponsorshipInvitationRepository extends EntityRepository
{
    public function getNumberOfValidatedInvitation($user_id)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT count(si)
                  FROM AppBundle:SponsorshipInvitation si
                  LEFT JOIN si.host_invitation hi
                  LEFT JOIN si.target_invitation ti
                  WHERE hi.id = ?1
                  AND ti.id IS NOT NULL
                  ')
            ->setParameter(1, $user_id)
            ->getSingleScalarResult();
    }
}