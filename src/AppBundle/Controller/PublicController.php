<?php
// src/AppBundle/Controller/PublicController.php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\User;
use AppBundle\Entity\SuggestionBox;
use AppBundle\Services\MailDispatcher;
use Mailgun\Mailgun;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Form\SuggestionBoxType;
use AppBundle\Form\UserSuggestionBoxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class PublicController extends Controller
{
    /**
     * @Route("/test-mail", name="testmail")
     * @Security("has_role('ROLE_SUPER_ADMIN')")
     */
    public function testMailAction() {
# Instantiate the client.
        $this->get(MailDispatcher::class)->sendTestEmail();
        return $this->render('@App/Public/about.html.twig');
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, UserInterface $user = null)
    {
        $em = $this->getDoctrine()->getManager();

        $NB_MAX_NEWS = 4;
        $NB_MAX_CROWDS = 4;

        $new_artists = $em->getRepository('AppBundle:Artist')->findBy(['deleted' => false], ['date_creation' => 'DESC'], $NB_MAX_NEWS);
        $new_crowdfundings = $em->getRepository('AppBundle:ContractArtist')->findBy(['successful' => false, 'failed' => false], ['date' => 'DESC'], $NB_MAX_NEWS);

        $news = [];
        $i = 0;
        $j = 0;

        while(count($news) < $NB_MAX_NEWS && ($i < count($new_artists) || $j < count($new_crowdfundings))) {
            if($i >= count($new_artists)) {
                $news[] = ['type' => 'contract', 'object' => $new_crowdfundings[$j]];
                $j++;
            }
            elseif($j >= count($new_crowdfundings)) {
                $news[] = ['type' => 'artist', 'object' => $new_artists[$i]];
                $i++;
            }
            elseif($new_artists[$i]->getDateCreation() > $new_crowdfundings[$j]->getDate()) {
                $news[] = ['type' => 'artist', 'object' => $new_artists[$i]];
                $i++;
            }
            else {
                $news[] = ['type' => 'contract', 'object' => $new_crowdfundings[$j]];
                $j++;
            }
        }

        $all_crowdfundings = $em->getRepository('AppBundle:ContractArtist')->findWithAvailableCounterParts();
        $crowdfundings = [];

        if($user != null && count($user->getGenres()) > 0) {
            $genres = $user->getGenres()->toArray();
            $genre = $genres[array_rand($genres, 1)];
        }

        else {
            $genre = null;
        }


        // Efficient shuffle
        for($i = 0; $i < $NB_MAX_CROWDS && count($all_crowdfundings) > 0; $i++) {

            $randomKey = array_rand($all_crowdfundings, 1);

            if($i < 2 && $genre != null) {
                $genre_candidates = array_filter($all_crowdfundings, function($elem, $key) use ($genre) {
                    return $elem->getArtist()->getGenres()->contains($genre);
                }, ARRAY_FILTER_USE_BOTH);

                if(count($genre_candidates) > 0) {
                    $randomKey = array_rand($genre_candidates, 1);
                }
            }

            $crowdfundings[] = $all_crowdfundings[$randomKey];
            unset($all_crowdfundings[$randomKey]);
        }

        return $this->render('AppBundle:Public:home.html.twig', array(
            'news' => $news,
            'crowdfundings' => $crowdfundings,
        ));
    }

    /**
     * @Route("/conditons", name="conditions")
     */
    public function conditionsAction() {
        return $this->render('AppBundle:Public:conditions.html.twig');
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction() {
        return $this->render('AppBundle:Public:about.html.twig');
    }

    /**
     * @Route("/suggestions", name="suggestionBox")
     */
    public function suggestionBoxAction(){
        return $this->render('AppBundle:Public:suggestionBox.html.twig');
    }

    /**
     * @Route("/suggestions/post", name="suggestionBox_form")
     */
    public function suggestionBoxFormAction(Request $request, UserInterface $user = null) {
        $suggestionBox = new SuggestionBox();

        if($user != null){
            $suggestionBox->setUser($user);
            $form = $this->createForm(UserSuggestionBoxType::class, $suggestionBox, ['action' => $this->generateUrl('suggestionBox_form')]);
        }else{
            $form = $this->createForm(SuggestionBoxType::class, $suggestionBox, ['action' => $this->generateUrl('suggestionBox_form')]);
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($suggestionBox);
            $em->flush();

            if($suggestionBox->getMailCopy()) {
                $this->get('AppBundle\Services\MailDispatcher')->sendSuggestionBoxCopy($suggestionBox);
            }

            return new Response($this->renderView('AppBundle:Public/Form:suggestionBox_ok.html.twig'));
        }
        return new Response($this->renderView('AppBundle:Public/Form:suggestionBox.html.twig', array(
            'form' => $form->createView(),
        )));
    }

    /**
     * @Route("/halls", name="catalog_halls")
     */
    public function hallsAction() {
        $em = $this->getDoctrine()->getManager();
        $halls = $em->getRepository('AppBundle:Hall')->findAll();

        return $this->render('@App/Public/catalog_halls.html.twig', array(
            'halls' => $halls,
        ));
    }

    /**
     * @Route("/crowdfundings", name="catalog_crowdfundings")
     */
    public function artistContractsAction() {

        $em = $this->getDoctrine()->getManager();
        $current_contracts = $em->getRepository('AppBundle:ContractArtist')->findWithAvailableCounterParts();
        $succesful_contracts = $em->getRepository('AppBundle:ContractArtist')->findSuccessful();

        return $this->render('@App/Public/catalog_artist_contracts.html.twig', array(
            'current_contracts' => $current_contracts,
            'successful_contracts' => $succesful_contracts,
        ));
    }

    /**
     * @Route("/artists", name="catalog_artists")
     */
    public function artistsAction(Request $request, UserInterface $user = null) {
        $em = $this->getDoctrine()->getManager();

        $artists = $em->getRepository('AppBundle:Artist')->findBy(['deleted' => false]);

        if($user != null && count($user->getGenres()) > 0) {
            usort($artists, function(Artist $a, Artist $b) use ($user) {
                if($a->getScore($user) == $b->getScore($user))
                    return 0;
                if($a->getScore($user) > $b->getScore($user))
                    return 1;
                return -1;
            });
        }

        return $this->render('@App/Public/catalog_artists.html.twig', array(
            'artists' => $artists,
        ));
    }

    /**
     * @Route("/artist-{id}", name="artist_profile")
     */
    public function artistProfileAction(Request $request, UserInterface $user, Artist $artist) {
        return $this->render('@App/Public/artist_profile.html.twig', array(
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/validate-ownership-{id}/{code}", name="artist_validate_ownership")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function validateOwnershipAction(Request $request, UserInterface $user, Artist $artist, $code) {

        $em = $this->getDoctrine()->getManager();
        $req = $em->getRepository('AppBundle:ArtistOwnershipRequest')->findOneBy(['code' => $code]);

        if($req == null) {
            throw $this->createNotFoundException('There is no request with such code');
        }

        if($req->getAccepted() || $req->getRefused()) {
            throw $this->createAccessDeniedException('Request is already accepted or refused');
        }

        $mailUser = $em->getRepository('AppBundle:User')->findOneBy(['email' => $req->getEmail()]);
        if($mailUser != null) {
            // Manually log out if another user is logged in, then redirect to here
            // see https://stackoverflow.com/questions/28827418/log-user-out-in-symfony-2-application-when-remember-me-is-enabled/28828377#28828377
            if($mailUser->getId() != $user->getId()) {
                // Logging user out.
                $this->get('security.token_storage')->setToken(null);

                // Invalidating the session.
                $session = $request->getSession();
                $session->invalidate();

                // Redirecting user to login page in the end.
                $response = $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));

                // Clearing the cookies.
                $cookieNames = [
                    $this->container->getParameter('session.name'),
                    $this->container->getParameter('session.remember_me.name'),
                ];
                foreach ($cookieNames as $cookieName) {
                    $response->headers->clearCookie($cookieName);
                }

                return $response;
            }
        }

        $form = $this->createFormBuilder()
            ->add('accept', SubmitType::class)
            ->add('refuse', SubmitType::class)
            ->getForm()
        ;

        $form->handleRequest($request);

        if($form->isSubmitted() && !$req->getCancelled()) {
            if($form->get('accept')->isClicked()) {
                $req->setAccepted(true);

                $artist_user = new Artist_User();
                $artist_user
                    ->setArtist($artist)
                    ->setUser($user);
                $em->persist($artist_user);
                $em->flush();
            }
            elseif($form->get('refuse')->isClicked()) {
                $req->setRefused(true);
            }

            $this->addFlash('notice', 'bien reçu');
            return $this->redirectToRoute('homepage');
        }
        return $this->render('@App/User/Artist/validate_ownership.html.twig', array(
            'form' => $form->createView(),
            'request' => $req,
        ));
    }

    /**
     * @Route("/change-email-token-{token}", name="user_change_email_check")
     */
    public function changeEmailCheckAction(Request $request, UserInterface $current_user = null, $token) {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(['asked_email_token' => $token]);

        if(!$user) {
            $this->addFlash('error', 'Ce jeton est expiré');
            return $this->redirectToRoute('homepage');
        }

        $asked_email = $user->getAskedEmail();

        $error_detector = $em->getRepository('AppBundle:User')->findOneBy(['email' => $asked_email]);
        if($error_detector != null) {
            $this->addFlash('error', "L'adresse e-mail demandée est déjà prise par un autre membre depuis votre demande.");
            return $this->redirectToRoute('homepage');
        }

        // Everything ok -> let's change email
        $user->setEmail($asked_email);
        $user->setEmailCanonical($asked_email);

        $user->setAskedEmail(null);
        $user->setAskedEmailToken(null);

        // Logout (in case another user was logged in)
        if($current_user != null && $current_user->getId() != $user->getId()) {
            $this->get('security.token_storage')->setToken(null);

            // Invalidating the session.
            $session = $request->getSession();
            $session->invalidate();

            $this->addFlash('notice', "Votre e-mail a bien été modifié ; apparemment, vous étiez connecté avec un autre compte donc nous nous sommes permis de vous déconnecter.");
        }

        else {
            $this->addFlash('notice', "Votre e-mail a bien été modifié.");
        }

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
}