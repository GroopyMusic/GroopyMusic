<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class TicketingManager
{
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

    public function generateTicketsForContractFan(ContractFan $contractFan) {
        $contractFan->generateBarCode();
        if(empty($contractFan->getTickets())) {
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

    protected function sendTicketsForContractFan(ContractFan $cf) {
        $this->generateTicketsForContractFan($cf);
        $this->writer->writeTickets($cf);
        $this->mailDispatcher->sendTickets($cf, $cf->getContractArtist());
        $this->em->persist($cf);
    }

    protected function sendNotificationTicketsSent(array $users, $contractArtist) {
        $this->notificationDispatcher->notifyTickets($users, $contractArtist);
    }

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
            try {
                $this->sendNotificationTicketsSent($users, $contractArtist);
            } catch(\Exception $e) {
                $this->logger->error("Erreur lors de l'envoi de notifications pour les tickets du contrat d'artiste ");
                return $e;
            }
        }
        $this->em->flush();
        return null;
    }

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
}