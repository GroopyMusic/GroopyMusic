<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Notification;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

// TODO avoid passing entire objects as parameters to notifications, but rather their ID or values

class NotificationDispatcher
{
    const PROBLEMATIC_CART_TYPE = 'problematic_cart';
    const REMINDER_ARTIST_CONTRACT_TYPE = 'reminder_contract_artist';
    const FAILED_CONTRACT_ARTIST_TYPE = 'failed_contract_artist';
    const FAILED_CONTRACT_FAN_TYPE = 'failed_contract_fan';
    const SUCCESSFUL_CONTRACT_ARTIST_TYPE = 'successful_contract_artist';
    const SUCCESSFUL_CONTRACT_FAN_TYPE = 'successful_contract_fan';
    const TICKET_SENT_TYPE = 'ticket_sent';
    const ONGOING_CART_TYPE = 'ongoing_cart';

    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function addNotification(User $user, $type, array $params = []) {
        $notif = new Notification();
        $notif->setUser($user)->setType($type)->setParams($params);

        $this->em->persist($notif);
        $this->em->flush();
    }

    public function addNotifications($users, $type, array $params = []) {
        $notifs = [];
        foreach($users as $user) {
            $notif = new Notification();
            $notif->setUser($user)->setType($type)->setParams($params);

            $notifs[] = $notif;

            $this->em->persist($notif);
        }
        $this->em->flush();
    }

    public function getUnseenNb(User $user) {
        return count($this->em->getRepository('AppBundle:Notification')->findBy(['user' => $user, 'seen' => false]));
    }

    public function notifyProblematicCart(Cart $cart) {
        $this->addNotification($cart->getUser(), self::PROBLEMATIC_CART_TYPE);
    }

    public function notifyReminderArtistContract($users, ContractArtist $contract, $nb_days) {
        $this->addNotifications($users, self::REMINDER_ARTIST_CONTRACT_TYPE, ['nbDays' => $nb_days, 'contract' => $contract]);
    }

    public function notifyArtistsKnownOutcomeContract($users, ContractArtist $contract, $success) {
        $type = $success ? self::SUCCESSFUL_CONTRACT_ARTIST_TYPE : self::FAILED_CONTRACT_ARTIST_TYPE;
        $this->addNotifications($users, $type, ['contract' => $contract]);
    }

    public function notifyFansKnownOutcomeContract($users, ContractArtist $contract, $success) {
        $type = $success ? self::SUCCESSFUL_CONTRACT_FAN_TYPE : self::FAILED_CONTRACT_FAN_TYPE;
        $this->addNotifications($users, $type, ['contract' => $contract]);
    }

    public function notifyTicket(User $user, ContractFan $contractFan) {
        $this->addNotification($user, self::TICKET_SENT_TYPE, ['contractFan' => $contractFan]);
    }

    public function notifyOngoingCart($users, ContractArtist $contract) {
        $this->addNotifications($users, self::ONGOING_CART_TYPE, ['contract' => $contract]);
    }
}