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

    public function __construct(NotificationDispatcher $notificationDispatcher, MailDispatcher $mailDispatcher, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->notificationDispatcher = $notificationDispatcher;
        $this->mailDispatcher = $mailDispatcher;
        $this->em = $em;
        $this->logger = $logger;
        $this->querries = array(
            'Concert confirmé le plus récent',
            'Un seul concert sélectionné',
            'Un seul artiste sélectionné'
        );
    }

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

    public function defineRestriction(RewardRestriction $restriction, User_Reward $user_Reward)
    {
        $restrictionRepository = $this->em->getRepository("AppBundle:RewardRestriction");
        switch ($restriction->getQuerry()) {
            case 'Concert confirmé le plus récent';
                $baseContractArtist = $restrictionRepository->getMostRecentConfirmedConcert();
                $user_Reward->addBaseContractArtist($baseContractArtist);
                break;
            case 'Un seul concert sélectionné';
                $baseContractArtist = $this->em->getRepository('AppBundle:ContractArtist')->find($restriction->getQuerryParameter());
                $user_Reward->addBaseContractArtist($baseContractArtist);
                break;
            case 'Un seul artiste sélectionné';
                $artist = $this->em->getRepository('AppBundle:Artist')->find($restriction->getQuerryParameter());
                $user_Reward->addArtist($artist);
                break;
        }
    }

    public function getQuerryNames()
    {
        return $this->querries;
    }
}