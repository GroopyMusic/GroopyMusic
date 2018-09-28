<?php

namespace AppBundle\Repository;

use AppBundle\Command\KnownOutcomeContractCommand;
use AppBundle\Entity\Artist;
use AppBundle\Entity\ContractArtist;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ContractArtistRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ContractArtistRepository extends OptimizedRepository implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function __construct(EntityManager $em, Mapping\ClassMetadata $class)
    {
        parent::__construct($em, $class);

    }

    public function initShortName()
    {
        $this->short_name = 'c';
    }

    public function baseQueryBuilder()
    {
        return $this->createQueryBuilder('c')
            ->leftJoin('c.step', 's')
            ->leftJoin('c.festivaldays', 'fd')
            ->leftJoin('fd.hall', 'h')
            ->leftJoin('fd.performances', 'perf')
            ->leftJoin('perf.artist', 'a')
            ->leftJoin('s.counterParts', 'cp')
            ->leftJoin('a.genres', 'ag')
            ->leftJoin('a.photos', 'ap')
            ->leftJoin('ag.translations', 'agt')
            ->leftJoin('cp.translations', 'cpt')
            ->leftJoin('a.translations', 'at')
            ->addSelect('s')
            ->addSelect('cp')
            ->addSelect('ag')
            ->addSelect('ap')
            ->addSelect('at')
            ->addSelect('agt')
            ->addSelect('cpt')
            ->addSelect('fd')
            ->addSelect('h')
            ->addSelect('perf')
            ->addSelect('a')
        ;
    }

    public function queryVisible($prevalidation = false, $strict = false)
    {
        $prevalidation_operator = $strict ? '=' : '<=';

        return $this->createQueryBuilder('c')
            ->join('c.festivaldays', 'fd')
            ->leftJoin('fd.hall', 'h')
            ->leftJoin('fd.performances', 'perf')
            ->leftJoin('perf.artist', 'a')
            ->join('c.step', 's')
            ->leftJoin('s.counterParts', 'cp')
            ->leftJoin('a.genres', 'ag')
            ->leftJoin('a.photos', 'ap')
            ->leftJoin('ag.translations', 'agt')
            ->leftJoin('cp.translations', 'cpt')
            ->leftJoin('a.translations', 'at')
            ->addSelect('s')
            ->addSelect('cp')
            ->addSelect('ag')
            ->addSelect('at')
            ->addSelect('agt')
            ->addSelect('cpt')
            ->addSelect('ap')
            ->addSelect('fd')
            ->addSelect('h')
            ->addSelect('perf')
            ->addSelect('a')
            ->where('c.failed = 0')
            ->andWhere('fd.date > :yesterday')
            ->andWhere('c.test_period ' . $prevalidation_operator . ' :prevalidation')
            ->setParameter('prevalidation', $prevalidation)
            ->setParameter('yesterday', new \DateTime('yesterday'))
        ;
    }

    public function findEligibleForTicketGeneration()
    {
        return $this->queryVisible()
            ->andWhere('c.tickets_sent = 1')
            ->andWhere('c.successful = 1')
            ->getQuery()
            ->getResult();
    }

    // Don't type-hint user here as it creates a bug
    public function findInPreValidationContracts($user = null, $rolesManager = null)
    {
        if ($user == null || $rolesManager == null) {
            return [];
        }

        return array_filter(
            $this->queryVisible(true, true)->getQuery()->getResult(),

            function (ContractArtist $contractArtist) use ($user, $rolesManager) {
                return $rolesManager->userHasRole($user, 'ROLE_ADMIN') ||
                    $user->owns($contractArtist->getArtist());
            }
        );
    }

    public function findNewContracts($max)
    {
        return $this->queryVisible()
            ->orderBy('fd.date', 'desc')
            ->setMaxResults($max)
            ->getQuery()
            ->getResult();
    }

    public function findSuccessful()
    {
        return $this->queryVisible()
            ->leftJoin('c.contractsFan', 'cf')
            ->addSelect('cf')
            ->andWhere('c.successful = 1 OR c.tickets_sold >= c.threshold')
            ->andWhere('c.failed = 0')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns 0-$limit contracts for which the deadline is not passed AND not enough money is raised at the moment
     */
    public function findNotSuccessfulYet($limit = null)
    {
        $qb = $this->queryVisible()
            ->andWhere('c.dateEnd > :now')
            ->andWhere('c.tickets_sold < c.min_tickets')
            ->andWhere('c.successful = 0')
            ->orderBy('fd.date', 'asc')
        ;

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns 0-$limit contracts for which there are tickets to buy & that are visible (i.e. not in prevalidation)
     */
    public function findVisible($limit = null)
    {
        $qb = $this->queryVisible();

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->orderBy('fd.date', 'asc')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns contracts for which there are tickets to buy
     */
    public function findVisibleIncludingPreValidation()
    {
        $qb = $this->queryVisible(true, false);

        return $qb
            ->orderBy('fd.date', 'asc')
            ->getQuery()
            ->getResult();
    }

    /**
     * Returns 0-$limit upcoming events except $event in parameter
     */
    public function findVisibleExcept(ContractArtist $event, $limit = null)
    {
        $qb = $this->queryVisible()
            ->andWhere($this->short_name . '.id <> :excluded_id')
            ->setParameter('excluded_id', $event->getId());

        if ($limit != null) {
            $qb->setMaxResults($limit);
        }

        return $qb
            ->orderBy('fd.date', 'asc')
            ->getQuery()
            ->getResult();
    }

    public function findCurrentForArtist(Artist $artist)
    {
        return $this->queryVisible()
            ->andWhere('a = :artist')
            ->setParameter('artist', $artist)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @see KnownOutcomeContractCommand
     */
    public function findPending()
    {
        return $this->queryVisible()
            ->andWhere('c.dateEnd <= :now')
            ->andWhere('c.successful = 0')
            ->setParameter('now', new \DateTime())
            ->getQuery()
            ->getResult();
    }

    /**
     * @see KnownOutcomeContractCommand
     */
    public function findNewlySuccessful()
    {
        return $this->queryVisible()
            ->andWhere('c.successful = 0')// Not marked as successful yet
            ->andWhere('c.tickets_sold >= s.min_tickets')
            ->getQuery()
            ->getResult();
    }


    /**
     * get all sucessful contract artists
     *
     * @return array contract artist array
     */
    public function getContactArtistsForSelect()
    {
        return $this->getEntityManager()->createQuery(
            'SELECT c,s,a
                  FROM AppBundle:ContractArtist c
                  LEFT JOIN c.step s
                  LEFT JOIN s.translations st
                  LEFT JOIN c.artist a
                  WHERE c.successful = TRUE 
                  ')
            ->getResult();
    }

    /**
     * get all artist particpant of contract artist
     *
     * @param $contract_artist_id
     * @return mixed contract artist
     */
    public function getArtistParticipants($contract_artist_id)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT ca,a,caa,au,u,a2,au2,u2
                  FROM AppBundle:ContractArtist ca
                  LEFT JOIN ca.artist a
                  LEFT JOIN ca.coartists_list caa
                  LEFT JOIN a.artists_user au
                  LEFT JOIN au.user u
                  LEFT JOIN caa.artist a2
                  LEFT JOIN a2.artists_user au2
                  LEFT JOIN au2.user u2
                  WHERE ca.id = ?1
                  ')
            ->setParameter(1, $contract_artist_id)
            ->getSingleResult();
    }

    /**
     * find all contract artist for select with search
     *
     * @param $q
     * @return array contract artist array
     */
    public function findContractArtistsForSelect($q)
    {
        $querry = 'SELECT ca,a FROM AppBundle:ContractArtist ca LEFT JOIN ca.artist a ';
        foreach ($q as $index => $string) {
            if ($index == 0) {
                $querry = $querry . " WHERE a.artistname LIKE '%" . $string . "%' ";
            } else {
                $querry = $querry . " OR a.artistname LIKE '% " . $string . " %' ";
            }
        }
        return $this->getEntityManager()
            ->createQuery($querry)
            ->getResult();
    }

    /**
     * check if contract artist is valid for sponsorship
     *
     * @param $contract_id
     * @return mixed contract artist or null
     */
    public function isValidForSponsorship($contract_id)
    {
        return $this->queryVisible()
            ->andWhere('c.failed = 0')
            ->andWhere('c.refunded = 0')
            ->andWhere('c.id = :cid')
            ->setParameter('cid', $contract_id)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * retrieves all upcoming contract artist in which a user will participate
     *
     * @param $user
     * @return array contract artist array
     */
    public function getUserContractArtists($user)
    {
        return $this->getEntityManager()->createQuery(
            'SELECT ca,fd,h, perf, cf, c, a,u
                  FROM AppBundle:ContractArtist ca
                  JOIN ca.festivaldays fd
                  LEFT JOIN fd.hall h
                  LEFT JOIN fd.performances perf
                  LEFT JOIN perf.artist a
                  LEFT JOIN ca.contractsFan cf
                  LEFT JOIN cf.cart c
                  LEFT JOIN c.user u
                  WHERE cf.refunded = 0 AND (fd.date > ?2)
                  AND ca.refunded = 0
                  AND ca.failed = 0
                  AND u.id = ?1
                  ')
            ->setParameter(1, $user->getId())
            ->setParameter(2, new \DateTime())
            ->getResult();
    }
}
