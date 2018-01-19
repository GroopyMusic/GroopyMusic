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
        $users = array_unique($users);

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

    public function notifyReminderArtistContract($users, ContractArtist $contract, $nb_days, $places) {
        $this->addNotifications($users, self::REMINDER_ARTIST_CONTRACT_TYPE, ['nbDays' => $nb_days, 'contract' => $contract, 'places' => $places]);
    }

    public function notifyKnownOutcomeContract($users, ContractArtist $contract, $artist, $success) {

        if($artist) {
            $type = $success ? self::SUCCESSFUL_CONTRACT_ARTIST_TYPE : self::FAILED_CONTRACT_ARTIST_TYPE;
        }
        else {
            $type = $success ? self::SUCCESSFUL_CONTRACT_FAN_TYPE : self::FAILED_CONTRACT_FAN_TYPE;
        }

        $hall_id = null; $hall_name = null; $date = null;

        if($contract->getReality() != null && $contract->getReality()->getDate() != null && $contract->getReality()->getHall() != null) {
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

    public function notifyTicket(User $user, ContractFan $contractFan) {
        $this->addNotification($user, self::TICKET_SENT_TYPE, ['contractFan' => $contractFan]);
    }

    public function notifyOngoingCart($users, ContractArtist $contract) {
        $this->addNotifications($users, self::ONGOING_CART_TYPE, ['contract' => $contract]);
    }
}