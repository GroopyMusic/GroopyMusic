<?php
/**
 * Created by PhpStorm.
 * User: jcochart
 * Date: 25/04/2018
 * Time: 22:47
 */

namespace AppBundle\Services;


use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\RewardTicketConsumption;
use AppBundle\Entity\SponsorshipInvitation;
use AppBundle\Entity\User;
use AppBundle\Entity\User_Reward;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Psr\Log\LoggerInterface;

class SponsorshipService
{
    const MAX_DAY_ACCEPTATION = 3;

    private $em;

    private $logger;

    private $mailDispatcher;

    private $token_gen;

    private $notificationDispatcher;

    public function __construct(MailDispatcher $mailDispatcher, EntityManagerInterface $em, LoggerInterface $logger, TokenGeneratorInterface $token_gen, NotificationDispatcher $notificationDispatcher)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailDispatcher = $mailDispatcher;
        $this->token_gen = $token_gen;
        $this->notificationDispatcher = $notificationDispatcher;
    }

    public function sendSponsorshipInvitation($emails, $content, ContractArtist $contractArtist, User $user)
    {
        if (count($emails) == 0) {
            throw new \Exception();
        }
        $verifiedEmails = $this->verifyEmails($emails);
        $emails = $verifiedEmails[0];
        if (count($emails) == 0) {
            return [false, $verifiedEmails[1]];
        }
        foreach ($emails as $email) {
            $sponsorship_invitation = new SponsorshipInvitation(new \DateTime(), $email, $content, $user,
                $contractArtist, $user->getId() . $this->token_gen->generateToken() . $contractArtist->getId());
            $this->em->persist($sponsorship_invitation);
            $this->mailDispatcher->sendSponsorshipInvitationEmail($sponsorship_invitation, $content);
        }
        $this->em->flush();
        return [true, $verifiedEmails[1]];
    }

    public function checkForSponsorship(User $user)
    {
        $sponsorship = $this->em->getRepository('AppBundle:SponsorshipInvitation')->getSponsorshipInvitationByMail($user->getEmail());
        if ($sponsorship != null) {
            if ($sponsorship->getDateInvitation()->add(new \DateInterval('P' . self::MAX_DAY_ACCEPTATION . 'D')) >= new \DateTime()) {
                $this->em->persist($sponsorship);
                $sponsorship->setTargetInvitation($user);
                return true;
            }
        }
        return false;
    }

    public function checkForRewardSponsorship($user, ContractArtist $contractArtist)
    {
        $sponsorship = $user->getSponsorshipInvitation();
        if ($sponsorship != null) {
            $host_user = $sponsorship->getHostInvitation();
            if ($contractArtist->getSponsorshipReward() != null) {
                $contractFan = $this->em->getRepository('AppBundle:ContractFan')->findSponsorshipContractFanToReward($host_user, $contractArtist);
                if ($contractFan != null && !$sponsorship->getRewardSent()) {
                    $user_reward = new User_Reward($contractArtist->getSponsorshipReward(), $host_user);
                    $ticket_reward = new RewardTicketConsumption($user_reward, null, false, false);
                    $user_reward->setRemainUse(0);
                    $user_reward->setActive(false);
                    $contractFan->addUserReward($user_reward);
                    $contractFan->addTicketReward($ticket_reward);
                    if ($contractFan->getContractArtist()->getTicketsSent() === true) {
                        $contractFan->getTickets()->first()->addReward($ticket_reward);
                    }
                    $this->em->persist($sponsorship);
                    $sponsorship->setRewardSent(true);
                    $this->notificationDispatcher->notifySponsorshipReward($sponsorship, $user_reward);
                    return true;
                }
            }
        }
        return false;
    }

    public function checkAllSponsorship(ContractArtist $contractArtist)
    {
        $payments = $contractArtist->getPayments()->toArray();
        foreach ($payments as $payment) {
            $this->checkForRewardSponsorship($payment->getUser(), $contractArtist);
        }
        //$this->em->flush();
    }

    public function getSponsorshipSummaryForUser($user)
    {
        $sponsorships = $this->em->getRepository('AppBundle:SponsorshipInvitation')->getSponsorshipSummary($user);
        $invited = [];
        $confirmed = [];
        foreach ($sponsorships as $sponsorship) {
            if ($sponsorship->getTargetInvitation() == null) {
                array_push($invited, $sponsorship->getEmailInvitation());
            } else {
                if ($sponsorship->getTargetInvitation()->getDeleted() === false) {
                    array_push($confirmed, $sponsorship->getTargetInvitation()->getEmail());
                }
            }
        }
        return [$invited, $confirmed];
    }

    private function verifyEmails($emails)
    {
        $userRepository = $this->em->getRepository('AppBundle:User');
        $clearedEmails = [];
        $knownEmail = [];
        foreach ($emails as $email) {
            if (strlen(trim($email)) == 0) {
                continue;
            }
            if ($userRepository->emailExists($email) == null) {
                if (!in_array($email, $clearedEmails)) {
                    array_push($clearedEmails, $email);
                }
            } else {
                if (!in_array($email, $knownEmail)) {
                    array_push($knownEmail, $email);
                }
            }
        }
        return [$clearedEmails, $knownEmail];
    }


}