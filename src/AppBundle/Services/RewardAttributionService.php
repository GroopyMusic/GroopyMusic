<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 29/03/2018
 * Time: 11:33
 */

namespace AppBundle\Services;


use AppBundle\Entity\Artist;
use AppBundle\Entity\ConsomableReward;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\ReductionReward;
use AppBundle\Entity\Reward;
use AppBundle\Entity\RewardRestriction;
use AppBundle\Entity\Step;
use AppBundle\Entity\User_Reward;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RewardAttributionService
{
    /*
     * here we add the query names
     */
    public const MOST_CONFIRMED_CONCERT = 'Concert confirmé le plus récent';
    public const ONE_CONCERT_SELECTED = 'Un seul concert sélectionné';
    public const ONE_ARTIST_SELECTED = 'Un seul artiste sélectionné';
    public const ONE_COUNTERPART_SELECTED = 'Une seule contrepartie sélectionnée';
    public const ONE_STEP_SELECTED = 'Un seul palier de salle sélectionné';

    /*
     * here we add the expected parameter type
     */
    public const QUERRY_PARAM_TYPE = array(
        self::MOST_CONFIRMED_CONCERT => null,
        self::ONE_CONCERT_SELECTED => ContractArtist::class,
        self::ONE_ARTIST_SELECTED => Artist::class,
        self::ONE_COUNTERPART_SELECTED => CounterPart::class,
        self::ONE_STEP_SELECTED => Step::class
    );

    private $notificationDispatcher;

    private $mailDispatcher;

    private $em;

    private $logger;

    /**
     * constructor + array with querry names
     *
     * RewardAttributionService constructor.
     * @param NotificationDispatcher $notificationDispatcher
     * @param MailDispatcher $mailDispatcher
     * @param EntityManagerInterface $em
     * @param LoggerInterface $logger
     */
    public function __construct(NotificationDispatcher $notificationDispatcher, MailDispatcher $mailDispatcher, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->notificationDispatcher = $notificationDispatcher;
        $this->mailDispatcher = $mailDispatcher;
        $this->em = $em;
        $this->logger = $logger;
    }

    /**
     * Retrieves all users of @param $stats
     * Give reward reward to all users
     * Sends an email and/or notification to rewarded users
     *
     * @param $stats
     * @param Reward $reward
     * @param $notification
     * @param $email
     * @param $emailContent
     */
    public function giveReward($stats, Reward $reward, $notification, $email, $emailContent)
    {
        foreach ($stats as $stat) {
            $user = $stat->getUser();
            $user_reward = new User_Reward($reward, $user);
            foreach ($reward->getRestrictions()->toArray() as $restriction) {
                $this->defineRestriction($restriction, $user_reward);
            }
            $this->em->persist($user_reward);
        }
        $this->em->flush();
        if ($notification == "true") {
            $this->notificationDispatcher->notifyRewardAttribution($stats, $reward);
        }
        if ($email == "true") {
            $this->mailDispatcher->sendEmailRewardAttribution($stats, $emailContent, $reward);
        }
    }

    /**
     * Attaches the right restrictions to user rewards
     *
     * here we add in the box the query name, the querry call and the behavior
     *
     * @param RewardRestriction $restriction
     * @param User_Reward $user_reward
     */
    public function defineRestriction(RewardRestriction $restriction, User_Reward $user_reward)
    {
        $restrictionRepository = $this->em->getRepository("AppBundle:RewardRestriction");
        $id_parameter = intval(explode('|', $restriction->getQueryParameter())[0]);
        switch ($restriction->getQuery()) {
            case self::MOST_CONFIRMED_CONCERT;
                $baseContractArtist = $restrictionRepository->getMostRecentConfirmedConcert();
                $user_reward->addBaseContractArtist($baseContractArtist);
                break;
            case self::ONE_CONCERT_SELECTED;
                $baseContractArtist = $this->em->getRepository('AppBundle:ContractArtist')->find($id_parameter);
                $user_reward->addBaseContractArtist($baseContractArtist);
                break;
            case self::ONE_ARTIST_SELECTED;
                $artist = $this->em->getRepository('AppBundle:Artist')->find($id_parameter);
                $user_reward->addArtist($artist);
                break;
            case self::ONE_COUNTERPART_SELECTED;
                $counterPart = $this->em->getRepository('AppBundle:CounterPart')->find($id_parameter);
                $user_reward->addCounterPart($counterPart);
                break;
            case self::ONE_STEP_SELECTED;
                $baseStep = $this->em->getRepository('AppBundle:Step')->find($id_parameter);
                $user_reward->addBaseStep($baseStep);
                break;
        }
    }

    /**
     * Get the names of the querries and type of params
     *
     * @return array
     */
    public function getQuerryNamesParams()
    {
        return self::QUERRY_PARAM_TYPE;
    }

    /**
     * get the statistics of each id in @param ids
     *
     * @param $ids
     * @param $allStatistics
     * @return array
     */
    public function getSelectedStats($ids, $allStatistics)
    {
        $users = [];
        foreach ($ids as $id) {
            array_push($users, $allStatistics[$id]);
        }
        return $users;
    }

    /**
     * get only the first 5 lines of each category level
     *
     * @param $categories
     *
     */
    public function limitStatistics($categories)
    {
        foreach ($categories as $category) {
            foreach ($category->getLevels()->toArray() as $level) {
                $level->setStatistics(array_slice($level->getStatistics()->toArray(), 0, 5, true));
            }
        }
    }

    /**
     * Returns all unremoved rewards classified by type
     *
     * @param $local
     * @return array
     */
    public function constructRewardSelectWithType($local)
    {
        $rewards = $this->em->getRepository('AppBundle:Reward')->findNotDeletedRewards($local);
        $arrayToReturn = [];
        foreach ($rewards as $reward) {
            if ($reward instanceof ConsomableReward) {
                $key = "Consommation";
            } else if ($reward instanceof InvitationReward) {
                $key = "Invitation";
            } else if ($reward instanceof ReductionReward) {
                $key = "Reduction";
            } else {
                $key = "Autres";
            }
            if (!array_key_exists($key, $arrayToReturn)) {
                $arrayToReturn[$key] = [];
            }
            array_push($arrayToReturn[$key], $reward);
        }
        return $arrayToReturn;
    }
}