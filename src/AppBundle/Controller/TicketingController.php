<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\PhysicalPerson;
use AppBundle\Entity\Ticket;
use AppBundle\Form\PhysicalPersonTicketType;
use AppBundle\Form\PhysicalPersonType;
use AppBundle\Services\TicketingManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class TicketingController
 * @Security("is_granted('ROLE_TICKETING')")
 */
class TicketingController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/", name="ticketing_index")
     */
    public function indexAction(EntityManagerInterface $em)
    {
        $events = $em->getRepository('AppBundle:ContractArtist')->findSuccessful();
        $events = array_filter($events, function(ContractArtist $contractArtist) {
            return $contractArtist->getDateConcert()->diff((new \DateTime()))->days <= 1;
        });

        return $this->render('@App/Ticketing/index.html.twig', array(
            'events' => $events,
        ));
    }

    /**
     * @Route("/{id}/generate", name="ticketing_generate")
     * @Security("is_granted('ROLE_ADMIN')")
     */
    public function generateAction(Request $request, ContractArtist $contractArtist, TicketingManager $ticketingManager, EntityManagerInterface $em) {

        $vipinscriptions = $em->getRepository('AppBundle:VIPInscription')->findBy(['contract_artist' => $contractArtist, 'counterparts_sent' => false]);

        $data = [];
        $form = $this->createForm(PhysicalPersonTicketType::class, $data);

        $form->handleRequest($request);

        if($request->getMethod() == 'POST' && $form->isSubmitted() && $form->isValid()) {

            $data = $form->getData(); //$request->request->all()[$form->getName()];

            $physicalPerson = new PhysicalPerson($data['firstname'], $data['lastname'], $data['email'], $data['other_names']);
            $counterpart = $data['counterpart'];
            $nb = $data['nb'];

            $ticketingManager->generateTicketsForPhysicalPerson($physicalPerson, $contractArtist, $counterpart, $nb);

            return new Response('OK');
        }

        return $this->render('@App/Ticketing/generate.html.twig', array(
            'form' => $form->createView(),
            'contract' => $contractArtist,
            'vipinscriptions' => $vipinscriptions,
        ));
    }


    // API
    /**
     * @Route("/generate-and-send-vip", name="ticketing_generate_send_vip")
     */
    public function generateAndSendVIPAction(Request $request, TicketingManager $ticketingManager, EntityManagerInterface $em) {
        $event_id = intval($request->get('event_id'));
        $contractArtist = $em->getRepository('AppBundle:ContractArtist')->find($event_id);

        $ticketingManager->sendUnSentVIPTicketsForContractArtist($contractArtist);

        return new Response('OK');
    }

    /**
     * @Route("/validate-ticket", name="ticketing_validate_get")
     */
    public function getTicketJSONAction(Request $request, TicketingManager $manager, EntityManagerInterface $em) {

        $barcode = $request->get('barcode');
        $event_id = intval($request->get('event_id'));

        $ticket = $em->getRepository('AppBundle:Ticket')->findOneBy(['barcode_text' => $barcode]);
        $contractArtist = $em->getRepository('AppBundle:ContractArtist')->find($event_id);

        if($ticket === null) {
            $ticket_array = ['error' => 'Ce ticket n\'existe pas.'];
        }

        elseif($contractArtist === null) {
            $ticket_array = ['error' => 'Cet événement n\'existe pas.'];
        }

        elseif($contractArtist->getDateConcert()->diff((new \DateTime()))->days > 1) {
            $ticket_array = ['error' => "Cet événement n'a pas lieu aujourd'hui."];
        }

        else {
            $ticket_array = $manager->getTicketsInfoArray($ticket);
            if($ticket->getContractArtist()->getId() != $contractArtist->getId()) {
                $ticket_array['error'] = 'Ce ticket ne correspond pas à l\'évenement sélectionné';
            }
            elseif($ticket->isRefunded()) {
                $ticket_array['error'] = 'Ce ticket a été remboursé et n\'est donc plus valide.';
            }
            else {
                $ticket_array['error'] = null;
                $manager->validateTicket($ticket);
            }
        }

        return new JsonResponse($ticket_array);
    }

}
