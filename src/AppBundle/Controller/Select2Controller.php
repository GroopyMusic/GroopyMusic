<?php

namespace AppBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Select2Controller extends Controller
{
    protected $container;

    private $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @Route("/genres", name="select2_genres")
     */
    public function genresAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $q = $request->get('q');
        $genres = $em->getRepository('AppBundle:Genre')->findForString($q, $request->getLocale());

        $genresArray = [];

        foreach ($genres as $genre) {
            $genresArray[] = array(
                'id' => $genre->getId(),
                'text' => $genre->getName(),
            );
        }
        return new Response(json_encode($genresArray), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @Route("/artists", name="select2_artists")
     */
    public function artistsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $q = $request->get('q');
        $artists = $em->getRepository('AppBundle:Artist')->findNotDeleted($q);
        $artistsArray = [];
        foreach ($artists as $artist) {
            $artistsArray[] = array(
                'id' => $artist->getId(),
                'text' => $artist->getArtistname(),
            );
        }
        return new Response(json_encode($artistsArray), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @Route("/users", name="select2_users")
     */
    public function usersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $q = $request->get('q');
        $users = $em->getRepository('AppBundle:User')->findUsersNotDeletedForSelect(explode(" ", $q));

        $usersArray = [];

        foreach ($users as $user) {
            $usersArray[] = array(
                'id' => $user->getId(),
                'text' => $user->getDisplayName(),
            );
        }
        return new Response(json_encode($usersArray), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @Route("/newsletter_users", name="select2_newsletter_users")
     */
    public function newsletterUsersAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $q = $request->get('q');
        $users = $em->getRepository('AppBundle:User')->findNewsletterUsersNotDeletedForSelect(explode(" ", $q));

        $usersArray = [];

        foreach ($users as $user) {
            $usersArray[] = array(
                'id' => $user->getId(),
                'text' => $user->getDisplayName(),
            );
        }
        return new Response(json_encode($usersArray), 200, array('Content-Type' => 'application/json'));
    }

    /**
     * @Route("/contractArtists", name="select2_contractArtists")
     */
    public function contractArtistsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $q = $request->get('q');
        $contractArtists = $em->getRepository('AppBundle:ContractArtist')->findContractArtistsForSelect(explode(" ", $q));

        $contractArtistsArray = [];

        foreach ($contractArtists as $contractArtist) {
            $contractArtistsArray[] = array(
                'id' => $contractArtist->getId(),
                'text' => $contractArtist->__toString(),
            );
        }
        return new Response(json_encode($contractArtistsArray), 200, array('Content-Type' => 'application/json'));
    }

}
