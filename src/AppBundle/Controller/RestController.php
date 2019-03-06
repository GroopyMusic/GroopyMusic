<?php

namespace AppBundle\Controller;

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
        $event_id = $request->get('event_id');
        $barcode = $request->get('barcode');
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository('AppBundle:Ticket')->findOneBy(['barcode_text' => $barcode]);
        $contract_artist = $em->getRepository('AppBundle:YB\YBContractArtist')->find($event_id);
        if ($ticket === null){
            $error = 'Ce ticket n\'existe pas.';
        } elseif ($contract_artist === null) {
            $error = 'Cet événement n\'existe pas.';
        } elseif($contract_artist->getDateEvent() < (new \DateTime())->modify('+1day')) {
            $error = 'Cet événement n\'a pas lieu aujourd\'hui.';
        } else {
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
        $rest_ticket = $this->setRestTicket($ticket, $error);
        return new JsonResponse($this->getArrayFromTicket($rest_ticket));
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
        if (count($user) !== 0){
            $campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllEvents($user[0]);
            if (count($campaigns) === 0){
                $error = 'Vous ne pouvez pas utiliser l\'application. Vous devez être gestionnaire de campagnes.';
            } else {
                $error = '';
                $password = $user[0]->getPassword();
                $id = $user[0]->getId();
            }
        } else {
            $username = '';
            $error = 'Cet utilisateur n\'existe pas.';
        }
        $user_array = array(
            'id' => $id,
            'username' => $username,
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
        if ($user !== null){
            $events = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllEvents($user);
            if (count($events) === 0) {
                $error = 'Vous n\'avez pas d\'événements.';
            } else {
                $error = '';
            }
        } else {
            $error = 'Cet utilisateur n\'existe pas.';
        }
        $array_events = $this->getArrayFromEvents($events, $error);
        return new JsonResponse($array_events);
    }

    /**
     * @Rest\View()
     * @Rest\Post("/addticket")
     */
    public function addTicketAction(Request $request){
        /* 
        Questions :
            Comment on ajoute les tickets ?
                2 options :
                    - RAF : on ajoute avec le prix mais on s'en tape de la contrepartie
                    - on ajoute par contrepartie, histoire que l'organisateur connaissent les vraies stats
        */
        $em = $this->getDoctrine()->getManager();
        $quantity = $request->get('quantity');
        $contract_artist = $em->getRepository('AppBundle:ContractArtist')->find($request->get('event_id'));
        $counterpart = $em->getRepository('AppBundle:CounterPart')->find($request->get('counterpart_id'));
        $price = $counterpart->getPrice();
        $anonym = new User();
        $anonym->setFirstname('anonym - on site');
        for ($i = 0; $i < $quantity; $i++){
            $ticket = new Ticket(null, $counterpart, $price, $anonym, $contract_artist);
            $ticket->setIsBoughtOnSite(true);
            // $em->persist($ticket);
            // $em->flush();
        }

        
        /* $rest_ticket = $this->setRestTicket($ticket, '');
        return new JsonResponse($this->getArrayFromTicket($rest_ticket)); */
    }

    /**
     * @Rest\View()
     * @Rest\Get("/getcounterpart")
     */
    public function getCounterpartAction(Request $request){
        $event_id = $request->get('id');
        $em = $this->getDoctrine()->getManager();
        $contract_artist = $em->getRepository('AppBundle:YB\YBContractArtist')->find($event_id);
        $counterparts = [];
        if ($contract_artist === null){
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

    // private functions

    private function getArrayFromEvents($events, $error){
        $array_events = [];
        if ($error === ''){
            foreach($events as $event){
                $array_events[] = array(
                    'id' => $event->getId(),
                    'name' => $event->__toString(),
                    'nbTotalTicket' => $event->getGlobalSoldout(),
                    'nbScannedTicket' => $this->getNbScannedTicket($event->getId()),
                    'nbSoldTicket' => $event->getCounterpartsSold(),
                    'nbBoughtOnSiteTicket' => $this->getNbTicketSoldOnSite($event->getId()),
                    'date' => $event->getDateEvent(),
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
        if ($error !== ''){
            return new RestTicket('','','','',$error);
        } else {
            return new RestTicket(
                $ticket->getName(),
                $ticket->getCounterPart()->__toString(),
                '',
                $ticket->getBarcodeText(),
                $error);
        } 
    }

    private function getArrayFromTicket($rest_ticket){
        $array_ticket = array(
            'buyer' => $rest_ticket->getBuyer(),
            'ticket_type' => $rest_ticket->getTicketType(),
            'seat_type' => $rest_ticket->getSeatType(),
            'barcode' => $rest_ticket->getBarcode(),
            'error' => $rest_ticket->getError(),
        );
        return $array_ticket;
    }

    private function getNbTicketSoldOnSite($event_id){
        $em = $this->getDoctrine()->getManager();
        $tickets = $em->getRepository('AppBundle:Ticket')->getTicketsFromEvent($event_id);
        $nbTicketSoldOnSite = 0;
        foreach ($tickets as $ticket) {
            if ($ticket->isBoughtOnSite()){
                $nbTicketSoldOnSite++;
            }
        }
        return $nbTicketSoldOnSite;
    }

    public function getNbScannedTicket($event_id){
        $em = $this->getDoctrine()->getManager();
        $tickets = $em->getRepository('AppBundle:Ticket')->getTicketsFromEvent($event_id);
        $nbScannedTicket = 0;
        foreach ($tickets as $ticket){
            if ($ticket->isValidated()){
                $nbScannedTicket++;
            }
        }
        return $nbScannedTicket;
    }

}

