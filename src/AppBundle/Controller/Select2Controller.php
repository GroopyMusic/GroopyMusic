<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class Select2Controller extends BaseController
{
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

    /**
     * @Route("/counterpart-artists", name="select2_counterpart_artists")
     */
    public function counterpartArtistsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $counterPart_id = $request->get('counterpart');

        $counterPart = $em->getRepository('AppBundle:CounterPart')->find($counterPart_id);
        $artists = $counterPart->getPotentialArtists();
        $artistsArray = [];

        foreach ($artists as $artist) {
            $artistsArray[] = array(
                'id' => $artist->getId(),
                'text' => $artist->__toString(),
            );
        }
        return new Response(json_encode($artistsArray), 200, array('Content-Type' => 'application/json'));
    }


    /**
     * @Route("/counterpart-sub-events", name="select2_yb_sub_events")
     */
    public function counterpartSubEvents(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $campaign_id = $request->get('campaign');

        $campaign = $em->getRepository('AppBundle:YB\YBContractArtist')->find($campaign_id);
        $subEvents = $campaign->getSubEvents()->toArray();
        $searray = [];

        foreach ($subEvents as $se) {
            $searray[] = array(
                'id' => $se->getId(),
                'text' => $se->__toString(),
            );
        }
        return new Response(json_encode($searray), 200, array('Content-Type' => 'application/json'));
    }


    /**
     * @Route("/transactional-message-products", name="select2_transactional_message_products")
     */
    public function transactionalMessageProductsAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();

        $projectId = $request->get('project');

        $products = $em->getRepository('XBundle:Product')->getProductsSoldForProject(intval($projectId));
        $productsArray = [];

        foreach ($products as $product) {
            $productsArray[] = array(
                'id' => $product->getId(),
                'text' => $product->__toString(),
            );
        }
        return new Response(json_encode($productsArray), 200, array('Content-Type' => 'application/json'));
    }

}
