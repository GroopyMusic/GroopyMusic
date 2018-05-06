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

    const MAXIMUM_UPCOMING_EVENTS_ON_TICKET = 10;

    private $writer;
    private $mailDispatcher;
    private $notificationDispatcher;
    private $logger;
    private $em;
    private $agenda = [];
    private $rewardSpendingService;

    public function __construct(PDFWriter $writer, MailDispatcher $mailDispatcher, NotificationDispatcher $notificationDispatcher, LoggerInterface $logger, EntityManagerInterface $em, RewardSpendingService $rewardSpendingService)
    {
        $this->writer = $writer;
        $this->mailDispatcher = $mailDispatcher;
        $this->notificationDispatcher = $notificationDispatcher;
        $this->logger = $logger;
        $this->em = $em;
        $this->rewardSpendingService = $rewardSpendingService;
    }

    /**
     * Generates all tickets linked to a fan order
     * Each ticket being related to a counterpart of the order, its price and a unique (for this order) ticket number
     * Calling this function sets attribute $contractFan->tickets
     *
     * @param ContractFan $contractFan
     *
     */
    public function generateTicketsForContractFan(ContractFan $contractFan)
    {
        $contractFan->generateBarCode();

        // TODO enhance this process, tickets shouldn't be removed & re-built (or should they ?)
        foreach ($contractFan->getTickets() as $ticket) {
            $contractFan->removeTicket($ticket);
        }

        //if(!empty($contractFan->getTickets())) {
        foreach ($contractFan->getPurchases() as $purchase) {
            /** @var Purchase $purchase */
            $counterPart = $purchase->getCounterpart();

            $j = 1;
            while ($j <= $purchase->getQuantityOrganic()) {
                $contractFan->addTicket(new Ticket($contractFan, $counterPart, $j, $counterPart->getPrice()));
                $j++;
            }
            for ($i = 1; $i <= $purchase->getQuantityPromotional(); $i++) {
                $contractFan->addTicket(new Ticket($contractFan, $counterPart, $j + $i, 0));
                $i++;
            }
        }
        $this->rewardSpendingService->giveRewardToTicket($contractFan);
        //}
    }

    public function generateTicketsForPhysicalPerson(PhysicalPersonInterface $physicalPerson, ContractArtist $contractArtist, $counterPart, $nb)
    {
        $tickets = [];

        /** @var CounterPart $counterPart */
        $price = $counterPart == null ? 0 : $counterPart->getPrice();

        for ($i = 1; $i <= $nb; $i++) {
            $ticket = new Ticket($cf = null, $counterPart, $i, $price, $physicalPerson, $contractArtist);
            $this->em->persist($ticket);
            $tickets[] = $ticket;
        }

        if ($physicalPerson instanceof VIPInscription) {
            $prefix = self::VIP_PREFIX;
            $directory = self::VIP_DIRECTORY;
        } else {
            $prefix = self::PH_PREFIX;
            $directory = self::PH_DIRECTORY;
        }

        $slug = StringHelper::slugify($physicalPerson->getDisplayName()) . (new \DateTime())->format('ymdHis');

        $path = $directory . $prefix . $slug . '.pdf';


        if(!empty($tickets)) {
            $agenda = $this->getAgenda($tickets[0]);
            // Write PDF file
            $this->writer->writeTickets($path, $tickets, $agenda);
            // And send it
            $this->mailDispatcher->sendTicketsForPhysicalPerson($physicalPerson, $contractArtist, $path);
        }

        // could be one final flush
        $this->em->flush();
    }

    /**
     * Generates arbitrary tickets and writes them on a flying PDF (sent to navigator, but not stored on server)
     *
     * @param ContractArtist $contractArtist
     * @param User $user
     */
    public function getTicketPreview(ContractArtist $contractArtist, User $user)
    {
        $cart = new Cart();
        $cart->setUser($user);
        $cf = new ContractFan($contractArtist);
        $cf->setCart($cart);
        $cf->generateBarCode();
        $counterpart = new CounterPart();
        $counterpart->setPrice(12);

        $ticket1 = new Ticket($cf, $counterpart, 1, 12);
        $ticket2 = new Ticket($cf, $counterpart, 2, 0);

        $cf->addTicket($ticket1);
        $cf->addTicket($ticket2);

        $agenda = $this->getAgenda($ticket1);

        $this->writer->writeTicketPreview($cf, $agenda);
    }

    /**
     * Sends tickets for one order only
     * & adds notification to user
     * To be used when tickets are already sent for an event & there is a new order for that event
     *
     * @param ContractFan $cf
     * @return \Exception|null
     */
    public function sendUnSentTicketsForContractFan(ContractFan $cf)
    {
        if (!$cf->getcounterpartsSent()) {
            try {
                $this->sendTicketsForContractFan($cf);
                $cf->setcounterpartsSent(true);
                $this->sendNotificationTicketsSent([$cf->getUser()], $cf->getContractArtist());
            } catch (\Exception $e) {
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
    public function sendUnSentTicketsForContractArtist(ContractArtist $contractArtist)
    {
        $users = [];

        foreach ($contractArtist->getContractsFanPaid() as $cf) {
            /** @var ContractFan $cf */
            if (!$cf->getcounterpartsSent()) {
                try {
                    $this->sendTicketsForContractFan($cf);
                    $cf->setcounterpartsSent(true);
                    $users[] = $cf->getUser();
                } catch (\Exception $e) {
                    $this->logger->error('Erreur lors de la génération de tickets pour le contrat fan ' . $cf->getId() . ' : ' . $e->getMessage() . ' \n ' . $e->getTraceAsString());
                    return $e;
                }

            }
        }

        $this->em->flush();

        try {
            $this->sendNotificationTicketsSent($users, $contractArtist);
        } catch (\Exception $e) {
            $this->logger->error("Erreur lors de l'envoi de notifications pour les tickets du contrat d'artiste " . $contractArtist->getId());
        }

        return null;
    }

    public function sendUnSentVIPTicketsForContractArtist(ContractArtist $contractArtist)
    {
        foreach ($contractArtist->getVipInscriptions() as $vipInscription) {
            /** @var $vipInscription VIPInscription */
            if (!$vipInscription->getCounterpartsSent()) {
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
    protected function sendTicketsForContractFan(ContractFan $cf)
    {
        $this->generateTicketsForContractFan($cf);
        $tickets = $cf->getTickets()->toArray();
        if(!empty($tickets)) {
            $agenda = $this->getAgenda($tickets[0]);
            $this->writer->writeTickets($cf->getTicketsPath(), $tickets, $agenda);
            $this->mailDispatcher->sendTicketsForContractFan($cf, $cf->getContractArtist());
            $this->em->persist($cf);
        }
    }

    /**
     * Adds a notification to all $users that their tickets for $contractArtist are ready
     *
     * @param array $users
     * @param $contractArtist
     */
    protected function sendNotificationTicketsSent(array $users, $contractArtist)
    {
        $this->notificationDispatcher->notifyTickets($users, $contractArtist);
    }


    /**
     * Returns an array of data corresponding to $ticket
     * which can be used to generate some JSON response
     *
     * @param Ticket $ticket
     * @return array
     */
    public function getTicketsInfoArray(Ticket $ticket)
    {
        $arr = [
            'Identifiant du ticket' => $ticket->getId(),
            'Acheteur' => $ticket->getName(),
            'Prix' => $ticket->getPrice() . ' €',
            'Event' => $ticket->getContractArtist()->__toString(),
            'validated' => $ticket->getValidated(),
            'refunded' => $ticket->isRefunded(),
            'rewards' => $ticket->getRewards()
        ];

        if ($ticket->getCounterPart() != null) {
            $arr['Type de ticket'] = $ticket->getCounterPart()->__toString();
        }

        if ($ticket->getContractFan() != null) {
            $arr['CF associé'] = $ticket->getContractFan()->getBarcodeText();
        } else {
            $arr['VIP'] = 'Oui';
        }
        return $arr;
    }

    /**
     * Marks ticket as validated
     * @param Ticket $ticket
     */
    public function validateTicket(Ticket $ticket)
    {
        if (!$ticket->isValidated()) {
            $ticket->setValidated(true);
            $this->em->persist($ticket);
            $this->em->flush();
        }
    }

    /**
     * Fetches upcoming events that will be displayd on the ticket
     */
    public function getAgenda(Ticket $ticket) {
        $event = $ticket->getContractArtist();
        if(isset($this->agenda[$event->getId()])) {
            return $this->agenda[$event->getId()];
        }
        else {
            $agenda = $this->em->getRepository('AppBundle:ContractArtist')->findVisibleExcept($event, self::MAXIMUM_UPCOMING_EVENTS_ON_TICKET);
            $this->agenda[$event->getId()] = $agenda;
            return $agenda;
        }
    }
}