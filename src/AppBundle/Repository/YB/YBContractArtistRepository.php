<?php

namespace AppBundle\Repository\YB;


use AppBundle\Entity\User;
use AppBundle\Entity\YB\Organization;

class YBContractArtistRepository extends \Doctrine\ORM\EntityRepository
{
    public function findById($id){
        return $this->createQueryBuilder('c')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @deprecated, use getOnGoingEvents
     */
    public function getCurrentYBCampaigns($user = null) {

        $qb = $this->createQueryBuilder('c');
        if($user instanceof User && !$user->isSuperAdmin()) {
            $qb
                ->join('c.handlers', 'u')
                ->addSelect('u')
                ->where('u.id = :id')
                ->setParameter('id', $user->getId());
        } else {
            $qb
                ->leftJoin('c.handlers', 'u')
                ->addSelect('u');
        }
        return $qb
            ->andWhere('c.date_closure >= :now')
            ->andWhere('c.failed = 0')
            ->orderBy('c.date_event', 'ASC')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @deprecated, use getPassedEvents
     */
    public function getPassedYBCampaigns(User $user) {
        return $this->createQueryBuilder('c')
            ->join('c.handlers', 'u')
            ->orderBy('c.date_event', 'DESC')
            ->where('u.id = :id')
            ->andWhere('c.date_closure < :now OR c.failed = 1')
            ->setParameters([
                'id' => $user->getId(),
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getResult()
        ;
    }

    public function getOnGoingEvents(User $user){
        return $this->createQueryBuilder('c')
            ->join('c.organization', 'org')
            ->join('org.participations', 'part')
            ->join('part.member', 'u')
            ->where('u.id = :id')
            ->andWhere('c.date_closure >= :now AND c.failed = 0')
            ->orderBy('c.date_event', 'ASC')
            ->setParameters([
                'id' => $user->getId(),
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getResult();
    }

    public function getOrganizationOnGoingEvents(Organization $organization){
        return $this->createQueryBuilder('c')
            ->join('c.organization', 'org')
            ->where('org.id = :id')
            ->andWhere('c.date_closure >= :now AND c.failed = 0')
            ->orderBy('c.date_event', 'ASC')
            ->setParameters([
                'id' => $organization->getId(),
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getResult();
    }
    
    public function getPassedEvents(User $user){
        return $this->createQueryBuilder('c')
            ->join('c.organization', 'org')
            ->join('org.participations', 'part')
            ->join('part.member', 'u')
            ->where('u.id = :id')
            ->andWhere('c.date_closure < :now OR c.failed = 1')
            ->orderBy('c.date_event', 'DESC')
            ->setParameters([
                'id' => $user->getId(),
                'now' => new \DateTime(),
            ])
            ->getQuery()
            ->getResult();
    }

    public function getAllEvents(User $user){
        return $this->createQueryBuilder('c')
            ->join('c.organization', 'org')
            ->join('org.participations', 'part')
            ->join('part.member', 'u')
            ->where('u.id = :id')
            ->orderBy('c.date_event', 'DESC')
            ->setParameter('id',$user->getId())
            ->getQuery()
            ->getResult();
    }

    public function getAllEventsSuccess(User $user){
        return $this->createQueryBuilder('c')
            ->join('c.organization', 'org')
            ->join('org.participations', 'part')
            ->join('part.member', 'u')
            ->where('u.id = :id')
            ->andWhere('c.failed = 0')
            ->orderBy('c.date_event', 'DESC')
            ->setParameter('id',$user->getId())
            ->getQuery()
            ->getResult();
    }

    public function getAllSuccess(){
        return $this->createQueryBuilder('c')
            ->where('c.failed = 0')
            ->getQuery()
            ->getResult();
    }

    public function getAllOnGoingEvents(){
        return $this->createQueryBuilder('c')
            ->where('c.date_closure >= :now AND c.failed = 0')
            ->orderBy('c.date_event', 'ASC')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    public function getAllPastEvents(){
        return $this->createQueryBuilder('c')
            ->where('c.date_closure < :now OR c.failed = 1')
            ->orderBy('c.date_event', 'DESC')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * Gets all the differents users that handle Ticked-it campaigns.
     *
     * @return array a list of the differents users that handle Ticked-it campaigns
     */
    public function getHandlersList(){
        return $this->createQueryBuilder('c')
            ->join('c.handlers', 'u')
            ->addSelect('u')
            ->groupBy('c.handlers')
            ->getQuery()
            ->getResult()
        ;
    }

    public function getAllYBCampaigns(User $user){
        $qb = $this->createQueryBuilder('c');
            if (!$user->isSuperAdmin()){
                $qb->join('c.handlers', 'u')
                    ->orderBy('c.date_event', 'DESC')
                    ->where('u.id = :id')
                    ->setParameter('id', $user->getId());
            }
            return $qb->getQuery()
            ->getResult();
    }
}