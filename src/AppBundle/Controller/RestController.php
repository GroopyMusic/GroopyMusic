<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\Purchase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Rest\RestTicket;
use FOS\RestBundle\View\View;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\User;
use AppBundle\Entity\YB\YBContractArtist;
use FOS\UserBundle\Util\TokenGeneratorInterface;

class RestController extends BaseController {

    /**
     * @Rest\View()
     * @Rest\Get("/scanticket")
     */
    public function scanTicketAction(Request $request){
        $user_id = $request->get("user_id");
        $event_id = $request->get('event_id');
        $barcode = $request->get('barcode');
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository('AppBundle:Ticket')->findOneBy(['barcode_text' => $barcode]);
        $contract_artist = $em->getRepository('AppBundle:YB\YBContractArtist')->findById($event_id);
        if ($contract_artist === null){
            $contract_artist = $em->getRepository('AppBundle:ContractArtist')->find($event_id);
            $response = $this->handleTicketValidationUM($user_id, $event_id, $ticket, $contract_artist, $em);
        } else {
            $response = $this->handleTicketValidationYB($user_id, $event_id, $ticket, $contract_artist, $em);
        }
        return $response;
    }
    
    /**
     * @Rest\View()
     * @Rest\Get("/loginuser")
     */
    public function loginUserAction(Request $request){
        $username = $request->get('username');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findByUsername($username);
        $password = '';
        $id = '';
        $name = '';
        if (count($user) !== 0){
            $campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllEvents($user[0]);
            if (count($campaigns) === 0 && !$user[0]->isSuperAdmin()){
                $error = 'Vous ne pouvez pas utiliser l\'application. Vous devez être gestionnaire de campagnes.';
            } else {
                $error = '';
                $password = $user[0]->getPassword();
                $id = $user[0]->getId();
                $name = $user[0]->getDisplayName();
            }
        } else {
            $username = '';
            $error = 'Cet utilisateur n\'existe pas.';
        }
        $user_array = array(
            'id' => $id,
            'username' => $username,
            'name' => $name,
            'password' => $password,
            'error' => $error
        );
        return new JsonResponse($user_array);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/getevents")
     */
    public function getEventsAction(Request $request){
        $user_id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($user_id);
        $events = [];
        $unmute_events = [];
        if ($user !== null){
            if ($user->isSuperAdmin()){
                $events = $em->getRepository('AppBundle:YB\YBContractArtist')->findAll();
                $unmute_events = $this->getEventsUnMute($em);
            } else {
                $events = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllEvents($user);
            }
            if (count($events) === 0) {
                $error = 'Vous n\'avez pas d\'événements.';
            } else {
                $error = '';
            }
        } else {
            $error = 'Cet utilisateur n\'existe pas.';
        }
        $yb_events = $this->getArrayFromEvents($events, $error);
        $array_events = array_merge($yb_events, $unmute_events);
        return new JsonResponse($array_events);
    }

    /**
     * @Rest\View()
     * @Rest\Post("/addticket")
     */
    public function addTicketAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        if (!$this->isOrganizer($request->get('user_id'), $request->get('event_id'))) {
            $error = 'Vous n\'organisez pas cet événement !';
        } else {
            $quantity = $request->get('quantity');
            $modePayment = $request->get('mode');
            $contract_artist = $em->getRepository('AppBundle:YB\YBContractArtist')->find($request->get('event_id'));
            if ($contract_artist->isSoldOut()){
                $error = 'L\'événement est sold-out...';
            } else {
                $counterpart = $em->getRepository('AppBundle:CounterPart')->find($request->get('counterpart_id'));
                $price = $counterpart->getPrice();
                $anonym = new User();
                $anonym->setFirstname('_anonym - on site');
                $cf = new ContractFan($contract_artist);
                foreach ($cf->getPurchases() as $purchase){
                    if ($purchase->getCounterpart() === $counterpart){
                        $purchase->setQuantity($quantity);
                    } else {
                        $cf->removePurchase($purchase);
                    }
                }
                $cart = new Cart(false);
                $cf->initAmount();
                $cart->addContract($cf);
                $cart->setConfirmed(true);
                $cart->setPaid(true);
                $cart->generateBarCode();
                $em->persist($cart);
                for ($i = 0; $i < $quantity; $i++){
                    $ticket = new Ticket($cf, $counterpart, $i, $price, $anonym, $contract_artist);
                    $ticket->setIsBoughtOnSite(true);
                    $ticket->setValidated(true);
                    $ticket->setDateValidated(new \DateTime());
                    $ticket->setPaidInCash($modePayment === 'cash');
                    $em->persist($ticket);
                    $em->flush();
                }
                $error = 'Tout s\'est bien passé !';
            }
        }
        return new JsonResponse(array('error' => $error));
    }

    /**
     * @Rest\View()
     * @Rest\Get("/getcounterpart")
     */
    public function getCounterpartAction(Request $request){
        $event_id = $request->get('event_id');
        $em = $this->getDoctrine()->getManager();
        $contract_artist = $em->getRepository('AppBundle:YB\YBContractArtist')->find($event_id);
        $counterparts = [];
        if (!$this->isOrganizer($request->get('user_id'), $request->get('event_id'))){
            $error = 'Vous n\'organisez pas cet événement.';
        } elseif ($contract_artist === null){
            $error = 'Cet événement n\'existe pas.';
        } else {
            $counterparts = $contract_artist->getCounterParts();
            if ($counterparts !== null){
                $error = '';
            } else {
                $error = 'Il n\'y a pas de ticket.';
            }
        }
        $array_tix = $this->getTicketFromCounterpart($counterparts, $error);
        return new JsonResponse($array_tix);
    }

    /**
     * @Rest\View()
     * @Rest\Get("/getaudience")
     */
    public function getAudienceAction(Request $request){
        $tickets = [];
        if (!$this->isOrganizer($request->get('user_id'), $request->get('event_id'))){
            $error = 'Vous n\'organisez pas cet événement.';
        } else {
            $em = $this->getDoctrine()->getManager();
            $tickets = $em->getRepository('AppBundle:Ticket')->getTicketsFromEvent($request->get('event_id'));
            if (count($tickets) === 0){
                $error = 'Aucun ticket n\'a été vendus...';
            } else {
                $error = '';
            }
        }
        return $this->getJSONResponseAudience($error, $tickets);
    }

    // private functions

    public function getEventsUnMute(EntityManagerInterface $em){
        $events = $em->getRepository('AppBundle:ContractArtist')->findEventForApp();
        $unmuteEvents = [];
        foreach ($events as $event){
            if ($event->hasOnlyOneDate()){
                $unmuteEvents[] = array(
                    'id' => $event->getId(),
                    'name' => $event->__toString(),
                    'nbTotalTicket' => $event->getGlobalSoldout(),
                    'nbScannedTicket' => $this->getNbScannedTicket($event->getId()),
                    'nbSoldTicket' => $this->getNbPresales($event),
                    'nbBoughtOnSiteTicket' => $this->getNbTicketSoldOnSite($event->getId()),
                    'date' => $event->getOnlyDate(),
                    'nbTicketBoughtInCash' => $this->getTicketPaidByCash($event->getId()),
                    'error' => '',
                );
            } else if (count($event->getFestivalDates()) === 0) {
                $unmuteEvents[] = array(
                    'id' => $event->getId(),
                    'name' => $event->__toString(),
                    'nbTotalTicket' => $event->getGlobalSoldout(),
                    'nbScannedTicket' => $this->getNbScannedTicket($event->getId()),
                    'nbSoldTicket' => $this->getNbPresales($event),
                    'nbBoughtOnSiteTicket' => $this->getNbTicketSoldOnSite($event->getId()),
                    'date' => 'no date',
                    'nbTicketBoughtInCash' => $this->getTicketPaidByCash($event->getId()),
                    'error' => '',
                );
            } else {
                for ($i = 0; $i < count($event->getFestivalDates()); $i++){
                    $day = 'Day ' . ($i + 1);
                    $unmuteEvents[] = array(
                        'id' => $event->getId(),
                        'name' => $day . ' - '.$event->__toString(),
                        'nbTotalTicket' => $event->getGlobalSoldout(),
                        'nbScannedTicket' => $this->getNbScannedTicket($event->getId()),
                        'nbSoldTicket' => $this->getNbPresales($event),
                        'nbBoughtOnSiteTicket' => $this->getNbTicketSoldOnSite($event->getId()),
                        'date' => $event->getFestivalDates()[$i],
                        'nbTicketBoughtInCash' => $this->getTicketPaidByCash($event->getId()),
                        'error' => '',
                    );
                }
            }
        }
        return $unmuteEvents;
    }

    private function getArrayFromEvents($events, $error){
        $array_events = [];
        if ($error === ''){
            foreach($events as $event){
                $array_events[] = array(
                    'id' => $event->getId(),
                    'name' => $event->__toString(),
                    'nbTotalTicket' => $event->getGlobalSoldout(),
                    'nbScannedTicket' => $this->getNbScannedTicket($event->getId()),
                    'nbSoldTicket' => $this->getNbPresales($event),
                    'nbBoughtOnSiteTicket' => $this->getNbTicketSoldOnSite($event->getId()),
                    'date' => $event->getDateEvent(),
                    'nbTicketBoughtInCash' => $this->getTicketPaidByCash($event->getId()),
                    'error' => $error,
                );
            }
        } else {
            $array_events[] = array(
                'error' => $error
            );
        }
        return $array_events;
    }

    private function getTicketFromCounterpart($counterparts, $error){
        $array_tix = [];
        if ($error === ''){
            foreach ($counterparts as $cp){
                $array_tix[] = array(
                    'id' => $cp->getId(),
                    'name' => $cp->getName(),
                    'price' => $cp->getPrice(), 
                );
            }
        } else {
            $array_tix[] = array(
                'error' => $error
            );
        }
        return $array_tix;
    }

    private function validateTicket(Ticket $ticket, EntityManagerInterface $em){
        if (!$ticket->isValidated()){
            $ticket->setValidated(true);
            $ticket->setDateValidated(new \DateTime());
            $em->persist($ticket);
            $em->flush();
        }
    }

    private function setRestTicket($ticket, $error){
        if ($error === '' || $error === 'Ce ticket a déjà été scanné.'){
            $validation = $ticket->isValidated() ? 'vrai' : 'faux';
            return new RestTicket(
                $ticket->getName(),
                $ticket->getCounterPart()->__toString(),
                '',
                $ticket->getBarcodeText(),
                $error,
                $validation);
        } else {
            return new RestTicket('','','','',$error, '');
        }
    }

    private function getArrayFromTicket($rest_ticket){
        $array_ticket = array(
            'buyer' => $rest_ticket->getBuyer(),
            'ticket_type' => $rest_ticket->getTicketType(),
            'seat_type' => $rest_ticket->getSeatType(),
            'barcode' => $rest_ticket->getBarcode(),
            'error' => $rest_ticket->getError(),
            'is_validated' => $rest_ticket->isValidated(),
        );
        return $array_ticket;
    }

    private function getNbPresales($event){
        $em = $this->getDoctrine()->getManager();
        $tickets = $em->getRepository('AppBundle:Ticket')->getPresale($event->getId());
        return count($tickets);
    }

    private function getNbTicketSoldOnSite($event_id){
        $em = $this->getDoctrine()->getManager();
        $tickets = $em->getRepository('AppBundle:Ticket')->getNbBoughtOnSiteFromEvent($event_id);
        return count($tickets);
    }

    private function getTicketPaidByCash($event_id){
        $em = $this->getDoctrine()->getManager();
        $tickets = $em->getRepository('AppBundle:Ticket')->getNbPaidInCashFromEvent($event_id);
        return count($tickets);
    }

    private function getNbScannedTicket($event_id){
        $em = $this->getDoctrine()->getManager();
        $tickets = $em->getRepository('AppBundle:Ticket')->getNbPresaleScannedFromEvent($event_id);
        return count($tickets);
    }

    private function isOrganizer($user_id, $event_id){
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->find($user_id);
        if ($user->isSuperAdmin()){
            return true;
        }
        $contract_artist = $em->getRepository('AppBundle:YB\YBContractArtist')->find($event_id);
        if ($contract_artist === null){
            return false;
        } else {
            return in_array($user, $contract_artist->getOrganizers());
        }
    }

    private function getJSONResponseAudience($error, $tickets){
        if ($error === ''){
            $rest_tickets_array = [];
            foreach ($tickets as $ticket){
                $rest_tickets_array[] = $this->getArrayFromTicket($this->setRestTicket($ticket, $error));
            }
            return new JsonResponse($rest_tickets_array);
        } else {
            return new JsonResponse(array('error' => $error));
        }
    }

    private function handleTicketValidationYB($user_id, $event_id, $ticket, $contract_artist, EntityManagerInterface $em){
        $error = '';
        if (!$contract_artist->isToday()){
            $error = 'Cet événement n\'a pas lieu aujourd\'hui.';
        }
        return $this->handleTicketValidation($error, $user_id, $event_id, $ticket, $contract_artist, $em);
    }

    private function handleTicketValidationUM($user_id, $event_id, $ticket, $contract_artist, EntityManagerInterface $em){
        $error = '';
        if (!$contract_artist->isFestivalDayToday()){
            $error = 'Cet événement n\'a pas lieu aujourd\'hui.';
        }
        return $this->handleTicketValidation($error, $user_id, $event_id, $ticket, $contract_artist, $em);
    }

    private function handleTicketValidation($error, $user_id, $event_id, $ticket, $contract_artist, EntityManagerInterface $em){
        if ($error === ''){
            if (!$this->isOrganizer($user_id, $event_id)){
                $error = 'Vous n\'organisez pas cet événement';
            } elseif ($ticket === null){
                $error = 'Ce ticket n\'existe pas.';
            } elseif ($contract_artist === null) {
                $error = 'Cet événement n\'existe pas.';
            }else {
                if ($ticket->getContractArtist()->getId() != $contract_artist->getId()) {
                    $error = 'Ce ticket ne correspond pas à l\'évenement sélectionné';
                } elseif ($ticket->isRefunded()) {
                    $error = 'Ce ticket a été remboursé et n\'est donc plus valide.';
                } elseif ($ticket->isValidated()){
                    $error = 'Ce ticket a déjà été scanné.';
                } else {
                    $error = '';
                    $this->validateTicket($ticket, $em);
                }
            }
        }
        $rest_ticket = $this->setRestTicket($ticket, $error);
        return new JsonResponse($this->getArrayFromTicket($rest_ticket));
    }

}

