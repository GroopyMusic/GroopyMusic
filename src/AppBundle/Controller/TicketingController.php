<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Ticket;
use AppBundle\Services\TicketingManager;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
            return true ; // $contractArtist->getDateConcert()->diff((new \DateTime()))->days == 0;
        });

        return $this->render('@App/Ticketing/index.html.twig', array(
            'events' => $events,
        ));
    }


    // API
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

        //elseif($contractArtist->getDateConcert()->diff((new \DateTime()))->days > 0) {
        //    $ticket_array = ['error' => "Cet événement n'a pas lieu aujourd'hui."];
        //}

        else {
            $ticket_array = $manager->getTicketsInfoArray($ticket);
            if($ticket->getContractArtist()->getId() != $contractArtist->getId()) {
                $ticket_array['error'] = 'Ce ticket ne correspond pas à l\'évenement sélectionné';
            }
            else {
                $ticket_array['error'] = null;
                $ticket->setValidated(true);
                $em->persist($ticket);
                $em->flush();
            }
        }

        return new JsonResponse($ticket_array);
    }

}
