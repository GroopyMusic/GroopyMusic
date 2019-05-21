<?php
namespace AppBundle\Controller;
use AppBundle\Entity\Cart;
use AppBundle\Entity\YB\YBOrder;
use AppBundle\Entity\YB\YBSubEvent;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Doctrine\ORM\EntityManagerInterface;
use AppBundle\Entity\Rest\RestTicket;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\User;
use AppBundle\Entity\YB\YBContractArtist;

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
        }
        $response = $this->handleTicketValidation('', $user_id, $event_id, $ticket, $contract_artist, $em);
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
                $events = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllSuccess();
                $unmute_events = $this->getEventsUnMute($em);
            } else {
                $events = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllEventsSuccess($user);
            }
            if (count($events) === 0) {
                $error = 'Vous n\'avez pas d\'événements.';
            } else {
                $error = '';
            }
        } else {
            $error = 'Cet utilisateur n\'existe pas.';
        }
        $events = $this->removeAllEventsBeingCreated($events);
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
            $emailAddress = $request->get('email');
            $firstName = $request->get('firstname');
            $lastName = $request->get('lastname');
            $contract_artist = $em->getRepository('AppBundle:YB\YBContractArtist')->find($request->get('event_id'));
            $available = $contract_artist->isSoldOutTicket($em);
            if ($available === 0){
                print_r("solout");
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
                if ($emailAddress !== ''){
                    $order = new YBOrder();
                    $order->setEmail($emailAddress)
                        ->setFirstName($firstName)
                        ->setLastName($lastName)
                        ->setCart($cart);
                    $cart->setYbOrder($order);
                }
                $em->persist($cart);
                for ($i = 0; $i < $quantity; $i++){
                    $ticket = new Ticket($cf, $counterpart, $i, $price, $anonym, $contract_artist, 'N/A');
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
    // ----------------- private methods -------------------
    private function getEventsUnMute(EntityManagerInterface $em){
        $events = $em->getRepository('AppBundle:ContractArtist')->findEventForApp();
        $unmuteEvents = [];
        foreach ($events as $event){
            if ($event->hasOnlyOneDate()){
                if ($event->isFestivalDayToday()){
                    $unmuteEvents[] = $this->createArray($event, $event->__toString(), $event->getOnlyDate(), $this->getAudienceForEvent($event), $this->getCounterpartsForEvent($event), $this->getNbSoldTicketPerCounterpart($em, $event));
                } else {
                    $unmuteEvents[] = $this->createArray($event, $event->__toString(), $event->getOnlyDate(), [], $this->getCounterpartsForEvent($event), $this->getNbSoldTicketPerCounterpart($em, $event));
                }
            } else if (count($event->getFestivalDates()) === 0) {
                $unmuteEvents[] = $this->createArray($event, $event->__toString(), 'no date', [], $this->getCounterpartsForEvent($event), $this->getNbSoldTicketPerCounterpart($em, $event));
            } else {
                for ($i = 0; $i < count($event->getFestivalDates()); $i++){
                    $day = 'Day ' . ($i + 1);
                    if ($event->getFestivalDates()[$i]->format('m/d/Y') === (new \DateTime())->format('m/d/Y')){
                        $name = $day . ' - '.$event->__toString();
                        $unmuteEvents[] = $this->createArray($event, $name, $event->getFestivalDates()[$i], $this->getAudienceForEvent($event), $this->getCounterpartsForEvent($event), $this->getNbSoldTicketPerCounterpart($em, $event));
                    } else {
                        $name = $day . ' - '.$event->__toString();
                        $unmuteEvents[] = $this->createArray($event, $name, $event->getFestivalDates()[$i], [], $this->getCounterpartsForEvent($event), $this->getNbSoldTicketPerCounterpart($em, $event));
                    }
                }
            }
        }
        return $unmuteEvents;
    }
    private function createArray($event, $eventName, $eventDate, $audience, $counterparts, $nbSoldTicketPerCp){
        $event = array (
            'id' => $event->getId(),
            'name' => $eventName,
            'nbTotalTicket' => $event->getGlobalSoldout(),
            'nbScannedTicket' => $this->getNbScannedTicket($event->getId()),
            'nbSoldTicket' => $this->getNbPresales($event),
            'nbBoughtOnSiteTicket' => $this->getNbTicketSoldOnSite($event->getId()),
            'date' => $eventDate,
            'nbTicketBoughtInCash' => $this->getTicketPaidByCash($event->getId()),
            'error' => '',
            'audience' => $audience,
            'counterparts' => $counterparts,
            'detailsTixPerCp' => $nbSoldTicketPerCp,
            'photoPath' => $event->getPhotoFileName(),
        );
        return $event;
    }
    private function getArrayFromEvents($events, $error){
        $em = $this->getDoctrine()->getManager();
        $array_events = [];
        if ($error === ''){
            foreach($events as $event){
                if ($event->isToday()){
                    $array_events[] = $this->createArray($event, $event->__toString(), new \DateTime(), $this->getAudienceForEvent($event), $this->getCounterpartsForEvent($event), $this->getNbSoldTicketPerCounterpart($em, $event));
                } else {
                    $array_events[] = $this->createArray($event, $event->__toString(), $event->getDateEvent(), [], $this->getCounterpartsForEvent($event), $this->getNbSoldTicketPerCounterpart($em, $event));
                }
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
    /**
     * @param $ticket Ticket
     * @param $error
     * @return RestTicket
     */
    private function setRestTicket(Ticket $ticket, $error){
        if ($error === '' || $error === 'Ce ticket a déjà été scanné.'){
            $validation = $ticket->isValidated() ? 'vrai' : 'faux';
            $cpName = $ticket->getCounterPart() === null ? '' : $ticket->getCounterPart()->__toString();
            return new RestTicket(
                $ticket->getName(),
                $cpName,
                $ticket->getSeat(),
                $ticket->getBarcodeText(),
                $error,
                $validation,
                $ticket->getContractFan()->getCart()->getBarcodeText()
            );
        } else {
            return new RestTicket('','','','',$error, '', '');
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
            'cart_number' => $rest_ticket->getCartNumber()
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
    private function getAudienceForEvent($event){
        $em = $this->getDoctrine()->getManager();
        $tickets = $em->getRepository('AppBundle:Ticket')->getTicketsFromEvent($event->getId());
        $rest_tickets_array = [];
        foreach ($tickets as $ticket){
            $rest_tickets_array[] = $this->getArrayFromTicket($this->setRestTicket($ticket, ''));
        }
        return $rest_tickets_array;
    }
    private function getCounterpartsForEvent($event){
        $counterparts = $event->getCounterParts();
        return $this->getTicketFromCounterpart($counterparts, '');
    }
    private function handleTicketValidation($error, $user_id, $event_id, $ticket, $contract_artist, EntityManagerInterface $em){
        if (!$this->isOrganizer($user_id, $event_id)){
            $error = 'Vous n\'organisez pas cet événement';
        } elseif ($ticket === null){
            $error = 'Ce ticket n\'existe pas.';
        } elseif ($contract_artist === null) {
            $error = 'Cet événement n\'existe pas.';
        } else {
            if ($ticket->getContractArtist()->getId() != $contract_artist->getId()) {
                $error = 'Ce ticket ne correspond pas à l\'évenement sélectionné';
            } elseif ($ticket->isRefunded()) {
                $error = 'Ce ticket a été remboursé et n\'est donc plus valide.';
            } elseif ($ticket->isValidated()){
                $error = 'Ce ticket a déjà été scanné.';
            } elseif ($contract_artist->hasSubEvents()){
                $validForToday = $this->validateTicketWhenSubEvents($contract_artist, $ticket);
                if ($validForToday){
                    $error = '';
                    $this->validateTicket($ticket);
                } else {
                    $error = 'Ce ticket n\'est pas valide aujourd\'hui';
                }
            } else {
                $error = '';
                $this->validateTicket($ticket, $em);
            }
        }
        $rest_ticket = $this->setRestTicket($ticket, $error);
        return new JsonResponse($this->getArrayFromTicket($rest_ticket));
    }
    private function getNbSoldTicketPerCounterpart(EntityManagerInterface $em, $event){
        $cps = $event->getCounterParts();
        $soldTicketsPerCp = [];
        foreach ($cps as $cp){
            $tickets = $em->getRepository('AppBundle:Ticket')->getTicketsForCounterpart($cp->getId());
            $nb = count($tickets);
            $soldTicketsPerCp[] = array(
                $cp->getId() => $nb
            );
        }
        return $soldTicketsPerCp;
    }
    private function validateTicketWhenSubEvents(YBContractArtist $event, Ticket $ticket){
        $ticketCp = $ticket->getCounterPart();
        /** @var YBSubEvent $subEvent */$subEvent = $event->getTodaySubEvent();
        if ($subEvent !== null){
            $subEventCps = $subEvent->getCounterparts();
            return in_array($ticketCp, $subEventCps->toArray());
        } else {
            return false;
        }
    }
    private function removeAllEventsBeingCreated(array $events){
        /** @var YBContractArtist $e */
        foreach ($events as $k => $e){
            if (count($e->getCounterParts()) === 0){
                unset($events[$k]);
            }
        }
        return $events;
    }
}