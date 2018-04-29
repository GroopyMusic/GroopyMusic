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

    public function getSponsorshipInvitationByToken($token)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT si,hi,ti,ca
                  FROM AppBundle:SponsorshipInvitation si
                  LEFT JOIN si.host_invitation hi
                  LEFT JOIN si.target_invitation ti
                  LEFT JOIN si.contract_artist ca
                  WHERE si.token_sponsorship = ?1
                  ORDER BY si.date_invitation DESC
                  ')
            ->setParameter(1, $token)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }

    public function getSponsorshipInvitationByMail($email){
        return $this->getEntityManager()->createQuery(
            'SELECT si,hi,ti,ca
                  FROM AppBundle:SponsorshipInvitation si
                  LEFT JOIN si.host_invitation hi
                  LEFT JOIN si.target_invitation ti
                  LEFT JOIN si.contract_artist ca
                  WHERE si.email_invitation = ?1
                  AND si.last_date_acceptation IS NOT NULL 
                  ORDER BY si.last_date_acceptation DESC
                  ')
            ->setParameter(1, $email)
            ->setMaxResults(1)
            ->getOneOrNullResult();
    }
}