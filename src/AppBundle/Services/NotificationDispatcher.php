<?php

namespace AppBundle\Services;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Notification;
use AppBundle\Entity\PropositionContractArtist;
use AppBundle\Entity\SponsorshipInvitation;
use AppBundle\Entity\SuggestionBox;
use AppBundle\Entity\User;
use AppBundle\Entity\User_Category;
use AppBundle\Entity\User_Reward;
use AppBundle\Entity\VIPInscription;
use Doctrine\ORM\EntityManagerInterface;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

// TODO avoid passing entire objects as parameters to notifications, but rather their ID or values

class NotificationDispatcher
{
    const PROBLEMATIC_CART_TYPE = 'problematic_cart';
    const REMINDER_ARTIST_CONTRACT_TYPE = 'reminder_contract_artist';
    const FAILED_CONTRACT_ARTIST_TYPE = 'failed_contract_artist';
    const FAILED_CONTRACT_FAN_TYPE = 'failed_contract_fan';
    const SUCCESSFUL_CONTRACT_ARTIST_TYPE = 'successful_contract_artist';
    const SUCCESSFUL_CONTRACT_FAN_TYPE = 'successful_contract_fan';
    const TICKET_SENT_TYPE = 'tickets_sent';
    const ONGOING_CART_TYPE = 'ongoing_cart';
    const REWARD_ATTRIBUTION_TYPE = 'reward_attribution';
    const SPONSORSHIP_REWARD = 'sponsorship_reward';

    const ADMIN_NEW_CONTACT_FORM_TYPE = 'Admin/new_contact_form';
    const ADMIN_NEW_VIP_INSCRIPTION_FORM_TYPE = 'Admin/new_vip_inscription';
    const ADMIN_NEW_PROPOSITION_FORM_TYPE = 'Admin/new_proposition';
    const ADMIN_STATISTIC_COMPUTATION_ERROR_TYPE = 'Admin/statistic_computation_error';
    const ADMIN_NEW_ARTIST_TYPE = 'Admin/new_artist';

    private $em;
    private $rolesManager;

    public function __construct(EntityManagerInterface $em, UserRolesManager $rolesManager)
    {
        $this->em = $em;
        $this->rolesManager = $rolesManager;
    }

    public function addNotification(User $user, $type, array $params = [])
    {
        $notif = new Notification();
        $notif->setUser($user)->setType($type)->setParams($params);

        $this->em->persist($notif);
        $this->em->flush();
    }

    public function addAdminNotification($type, array $params = [])
    {
        $admin_roles = $this->rolesManager->getParentRoles(['ROLE_ADMIN']);
        $admin_profiles = $this->em->getRepository('AppBundle:User')->findUsersWithRoles($admin_roles);
        $this->addNotifications($admin_profiles, $type, $params);
    }

    public function addNotifications($users, $type, array $params = [])
    {
        $users = array_unique($users);

        $notifs = [];
        foreach ($users as $user) {
            $notif = new Notification();
            $notif->setUser($user)->setType($type)->setParams($params);

            $notifs[] = $notif;

            $this->em->persist($notif);
        }
        $this->em->flush();
    }

    public function getUnseenNb(User $user)
    {
        return count($this->em->getRepository('AppBundle:Notification')->findBy(['user' => $user, 'seen' => false]));
    }

    public function notifyProblematicCart(Cart $cart)
    {
        $this->addNotification($cart->getUser(), self::PROBLEMATIC_CART_TYPE);
    }

    public function notifyReminderArtistContract($users, ContractArtist $contract, $nb_days, $places)
    {
        $this->addNotifications($users, self::REMINDER_ARTIST_CONTRACT_TYPE, [
            'nbDays' => $nb_days,
            // TODO handle this case where step name won't be translated... should it be handled with IDs ?
            'step' => $contract->getStep()->getName(),
            'date' => $contract->getDateConcert()->format('d/m/Y'),
            'places' => $places,
        ]);
    }

    public function notifyKnownOutcomeContract($users, ContractArtist $contract, $artist, $success)
    {

        if ($artist) {
            $type = $success ? self::SUCCESSFUL_CONTRACT_ARTIST_TYPE : self::FAILED_CONTRACT_ARTIST_TYPE;
        } else {
            $type = $success ? self::SUCCESSFUL_CONTRACT_FAN_TYPE : self::FAILED_CONTRACT_FAN_TYPE;
        }

        $hall_id = null;
        $hall_name = null;
        $date = null;

        if ($contract->getReality() != null && $contract->getReality()->getDate() != null && $contract->getReality()->getHall() != null) {
            $hall_id = $contract->getReality()->getHall()->getId();
            $hall_name = $contract->getReality()->getHall()->getName();
            $date = $contract->getReality()->getDate()->format('d/m/Y');
        }

        $this->addNotifications($users, $type, [
            'artist' => $contract->getArtist()->getArtistname(),
            'contract_id' => $contract->getId(),
            'step' => $contract->getStep()->getName(),
            'hall_id' => $hall_id,
            'hall_name' => $hall_name,
            'date' => $date,
        ]);
    }

    public function notifyTickets($users, ContractArtist $contractArtist)
    {
        $this->addNotifications($users, self::TICKET_SENT_TYPE, ['date' => $contractArtist->getDateConcert()->format('d/m/Y'), 'hall_name' => $contractArtist->getHallConcert()->getName()]);
    }

    public function notifyOngoingCart($users, ContractArtist $contract)
    {
        $this->addNotifications($users, self::ONGOING_CART_TYPE, ['contract' => $contract]);
    }

    public function notifyRewardAttribution($stats, $reward)
    {
        $users = array_map(function (User_Category $elem) {
            return $elem->getUser();
        }, $stats);
        $this->addNotifications($users, self::REWARD_ATTRIBUTION_TYPE, ['reward' => $reward]);
    }

    public function notifySponsorshipReward(SponsorshipInvitation $sponsorshipInvitation, User_Reward $user_reward)
    {
        $ticket_sent = $sponsorshipInvitation->getContractArtist()->getTicketsSent();
        $this->addNotification($sponsorshipInvitation->getHostInvitation(), self::SPONSORSHIP_REWARD, [
            'reward_name' => $user_reward->getReward()->getName(),
            'target_name' => $sponsorshipInvitation->getTargetInvitation()->getDisplayName(),
            'event' => $sponsorshipInvitation->getContractArtist()->__toString(),
            'ticket_sent' => $ticket_sent
        ]);
    }

    // --------------------
    // Admin notifs
    // --------------------
    public function notifyAdminNewArtist(Artist $artist)
    {
        $this->addAdminNotification(self::ADMIN_NEW_ARTIST_TYPE, ['artist_name' => $artist->getArtistname()]);
    }

    public function notifyAdminContact(SuggestionBox $suggestionBox)
    {
        $this->addAdminNotification(self::ADMIN_NEW_CONTACT_FORM_TYPE, ['object' => $suggestionBox->getObject()]);
    }

    public function notifyAdminVIPInscription(VIPInscription $inscription)
    {
        $this->addAdminNotification(self::ADMIN_NEW_VIP_INSCRIPTION_FORM_TYPE, ['inscription_string' => $inscription->__toString()]);
    }

    public function notifyAdminProposition(PropositionContractArtist $proposition)
    {
        $this->addAdminNotification(self::ADMIN_NEW_PROPOSITION_FORM_TYPE, []);
    }

    public function notifyAdminErrorStatisticComputation($message)
    {
        $this->addAdminNotification(self::ADMIN_STATISTIC_COMPUTATION_ERROR_TYPE, ['message' => $message]);
    }
}