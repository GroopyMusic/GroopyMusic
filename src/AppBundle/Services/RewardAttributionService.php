<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 29/03/2018
 * Time: 11:33
 */

namespace AppBundle\Services;


use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\Reward;
use AppBundle\Entity\RewardRestriction;
use AppBundle\Entity\User_Reward;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class RewardAttributionService
{
    private $notificationDispatcher;

    private $mailDispatcher;

    private $em;

    private $logger;

    private $querries;

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
        $this->querries = array(
            'Concert confirmé le plus récent',
            'Un seul concert sélectionné',
            'Un seul artiste sélectionné',
            'Une seule contrepartie sélectionnée',
            'Un seul palier de salle sélectionné'
        );
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
            $user_reward->setUser($user);
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
     * @param RewardRestriction $restriction
     * @param User_Reward $user_reward
     */
    public function defineRestriction(RewardRestriction $restriction, User_Reward $user_reward)
    {
        $restrictionRepository = $this->em->getRepository("AppBundle:RewardRestriction");
        switch ($restriction->getQuerry()) {
            case 'Concert confirmé le plus récent';
                $baseContractArtist = $restrictionRepository->getMostRecentConfirmedConcert();
                $user_reward->addBaseContractArtist($baseContractArtist);
                break;
            case 'Un seul concert sélectionné';
                $baseContractArtist = $this->em->getRepository('AppBundle:ContractArtist')->find($restriction->getQuerryParameter());
                $user_reward->addBaseContractArtist($baseContractArtist);
                break;
            case 'Un seul artiste sélectionné';
                $artist = $this->em->getRepository('AppBundle:Artist')->find($restriction->getQuerryParameter());
                $user_reward->addArtist($artist);
                break;
            case 'Une seule contrepartie sélectionnée';
                $counterPart = $this->em->getRepository('AppBundle:CounterPart')->find($restriction->getQuerryParameter());
                $user_reward->addCounterPart($counterPart);
                break;
            case 'Un seul palier de salle sélectionné';
                $baseStep = $this->em->getRepository('AppBundle:Step')->find($restriction->getQuerryParameter());
                $user_reward->addBaseStep($baseStep);
                break;

        }
    }

    /**
     * Get the names of the querries
     *
     * @return array
     */
    public function getQuerryNames()
    {
        return $this->querries;
    }
}