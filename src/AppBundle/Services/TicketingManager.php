<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\PhysicalPersonInterface;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\User;
use AppBundle\Entity\VIPInscription;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;


class TicketingManager
{
    const VIP_DIRECTORY = 'pdf/viptickets/';
    const PH_DIRECTORY = 'pdf/phtickets/';
    const VIP_PREFIX = 'vip';
    const PH_PREFIX = 'ph';

    private $writer;
    private $mailDispatcher;
    private $notificationDispatcher;
    private $logger;
    private $em;

    public function __construct(PDFWriter $writer, MailDispatcher $mailDispatcher, NotificationDispatcher $notificationDispatcher, LoggerInterface $logger, EntityManagerInterface $em)
    {
        $this->writer = $writer;
        $this->mailDispatcher = $mailDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->em = $em;
    }

    /**
     * Generates all tickets linked to a fan order
     * Each ticket being related to a counterpart of the order, its price and a unique (for this order) ticket number
     * Calling this function sets attribute $contractFan->tickets
     *
     * @param ContractFan $contractFan
     *
     */
    public function generateTicketsForContractFan(ContractFan $contractFan) {
        $contractFan->generateBarCode();

        foreach($contractFan->getTickets() as $ticket) {
            $contractFan->removeTicket($ticket);
        }

        if(!empty($contractFan->getTickets())) {
            foreach ($contractFan->getPurchases() as $purchase) {
                /** @var Purchase $purchase */
                $counterPart = $purchase->getCounterpart();

                $j = 1;
                while($j <= $purchase->getQuantityOrganic()) {
                    $contractFan->addTicket(new Ticket($contractFan, $counterPart, $j, $counterPart->getPrice()));
                    $j++;
                }
                for($i = 1; $i <= $purchase->getQuantityPromotional(); $i++) {
                    $contractFan->addTicket(new Ticket($contractFan, $counterPart, $j + $i, 0));
                    $i++;
                }
            }
        }
    }

    public function generateTicketsForPhysicalPerson(PhysicalPersonInterface $physicalPerson, ContractArtist $contractArtist, $counterPart, $nb) {
        $tickets = [];

        /** @var CounterPart $counterPart */
        $price = $counterPart == null ? 0 : $counterPart->getPrice();

        for($i = 1; $i <= $nb; $i++) {
            $ticket = new Ticket($cf = null, $counterPart, $i, $price, $physicalPerson, $contractArtist);
            $this->em->persist($ticket);
            $tickets[] = $ticket;
        }

        if($physicalPerson instanceof VIPInscription) {
            $prefix = self::VIP_PREFIX;
            $directory = self::VIP_DIRECTORY;
        }
        else {
            $prefix = self::PH_PREFIX;
            $directory = self::PH_DIRECTORY;
        }

        $slug = StringHelper::slugify($physicalPerson->getDisplayName()) . (new \DateTime())->format('ymdHis');

        $path = $directory . $prefix . $slug . '.pdf';

        // Write PDF file
        $this->writer->writeTickets($path, $tickets);
        // And send it
        $this->mailDispatcher->sendTicketsForPhysicalPerson($physicalPerson, $contractArtist, $path);

        // could be one final flush
        $this->em->flush();
    }

    /**
     * Generates arbitrary tickets and writes them on a flying PDF (sent to navigator, but not stored on server)
     *
     * @param ContractArtist $contractArtist
     * @param User $user
     */
    public function getTicketPreview(ContractArtist $contractArtist, User $user) {

        $cart = new Cart();
        $cart->setUser($user);
        $cf = new ContractFan($contractArtist);
        $cf->setCart($cart);
        $cf->generateBarCode();
        $counterpart = new CounterPart();
        $counterpart->setPrice(12);
        $cf->addTicket(new Ticket($cf, $counterpart, 1, 12));
        $cf->addTicket(new Ticket($cf, $counterpart, 2, 0));

        $this->writer->writeTicketPreview($cf);
    }

    /**
     * Sends tickets for one order only
     * & adds notification to user
     * To be used when tickets are already sent for an event & there is a new order for that event
     *
     * @param ContractFan $cf
     * @return \Exception|null
     */
    public function sendUnSentTicketsForContractFan(ContractFan $cf) {
        if(!$cf->getcounterpartsSent()) {
            try {
                $this->sendTicketsForContractFan($cf);
                $cf->setcounterpartsSent(true);
                $this->sendNotificationTicketsSent([$cf->getUser()], $cf->getContractArtist());
            } catch(\Exception $e) {
                $this->logger->error('Erreur lors de la génération de tickets pour le contrat fan ' . $cf->getId() . ' : ' . $e->getMessage());
                return $e;
            }
        }

        $this->em->flush();
        return null;
    }

    /**
     * Generates tickets for an event, grouped by order
     * & sends them
     * & notifies users
     *
     * @param ContractArtist $contractArtist
     * @return \Exception|null
     */
    public function sendUnSentTicketsForContractArtist(ContractArtist $contractArtist) {
        $users = [];

        foreach($contractArtist->getContractsFanPaid() as $cf) {
            /** @var ContractFan $cf */
            if(!$cf->getcounterpartsSent()) {
                try {
                    $this->sendTicketsForContractFan($cf);
                    $cf->setcounterpartsSent(true);
                    $users[] = $cf->getUser();
                } catch(\Exception $e) {
                    $this->logger->error('Erreur lors de la génération de tickets pour le contrat fan ' . $cf->getId() . ' : ' . $e->getMessage() . ' \n ' . $e->getTraceAsString());
                    return $e;
                }

            }
        }

        $this->em->flush();

        try {
            $this->sendNotificationTicketsSent($users, $contractArtist);
        } catch(\Exception $e) {
            $this->logger->error("Erreur lors de l'envoi de notifications pour les tickets du contrat d'artiste " . $contractArtist->getId());
        }

        return null;
    }

    public function sendUnSentVIPTicketsForContractArtist(ContractArtist $contractArtist)
    {
        foreach($contractArtist->getVipInscriptions() as $vipInscription) {
            /** @var $vipInscription VIPInscription */
            if(!$vipInscription->getCounterpartsSent()) {
                $this->generateTicketsForPhysicalPerson($vipInscription, $contractArtist, null, 1);
                $vipInscription->setCounterpartsSent(true);
                $this->em->persist($vipInscription);
            }
        }
        $this->em->flush();
    }

    /**
     * Generates & sends tickets for one order
     *
     * @param ContractFan $cf
     */
    protected function sendTicketsForContractFan(ContractFan $cf) {
        $this->generateTicketsForContractFan($cf);
        $this->writer->writeTickets($cf->getTicketsPath(), $cf->getTickets());
        $this->mailDispatcher->sendTicketsForContractFan($cf, $cf->getContractArtist());
        $this->em->persist($cf);
    }

    /**
     * Adds a notification to all $users that their tickets for $contractArtist are ready
     *
     * @param array $users
     * @param $contractArtist
     */
    protected function sendNotificationTicketsSent(array $users, $contractArtist) {
        $this->notificationDispatcher->notifyTickets($users, $contractArtist);
    }


    /**
     * Returns an array of data corresponding to $ticket
     * which can be used to generate some JSON response
     *
     * @param Ticket $ticket
     * @return array
     */
    public function getTicketsInfoArray(Ticket $ticket) {
        $arr = [
            'Identifiant du ticket' => $ticket->getId(),
            'Acheteur' => $ticket->getName(),
            'Type de ticket' => $ticket->getCounterPart()->__toString(),
            'Prix' => $ticket->getPrice(). ' €',
            'Event' => $ticket->getContractArtist()->__toString(),
            'validated' => $ticket->getValidated(),
            'refunded' => $ticket->isRefunded(),
            'user_rewards' => $ticket->getContractFan()->getUserRewards()
        ];

        if($ticket->getContractFan() != null) {
            $arr['CF associé'] = $ticket->getContractFan()->getBarcodeText();
        }
        else {
            $arr['VIP'] = 'Oui';
        }
        return $arr;
    }

    /**
     * Marks ticket as validated
     * @param Ticket $ticket
     */
    public function validateTicket(Ticket $ticket) {
        if(!$ticket->isValidated()) {
            $ticket->setValidated(true);
            $this->em->persist($ticket);
            $this->em->flush();
        }
    }
}