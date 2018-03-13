<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Hall;
use AppBundle\Entity\PropositionContractArtist;
use AppBundle\Entity\User;
use AppBundle\Entity\SuggestionBox;
use AppBundle\Form\ContractFanType;
use AppBundle\Form\PropositionContractArtistType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Mailgun\Mailgun;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Form\SuggestionBoxType;
use AppBundle\Form\UserSuggestionBoxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;

class PublicController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // Duplicated from UserController
    private function createCartForUser($user) {
        $cart = new Cart();
        $cart->setUser($user);
        $this->getDoctrine()->getManager()->persist($cart);
        return $cart;
    }

    private function cleanCart(Cart $cart, $em) {
        if($cart->getPaid() && $cart->getConfirmed()) {
            return $this->createCartForUser($cart->getUser());
        }
        else {
            foreach($cart->getContracts() as $contract) {
                $cart->removeContract($contract);
                $em->remove($contract);
            }
            return $cart;
        }
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, UserInterface $user = null)
    {

        $em = $this->getDoctrine()->getManager();

        $NB_MAX_NEWS = 4;
        $NB_MAX_CROWDS = 3;

        $new_artists = $em->getRepository('AppBundle:Artist')->findBy(['deleted' => false], ['date_creation' => 'DESC'], $NB_MAX_NEWS);
        $new_crowdfundings = $em->getRepository('AppBundle:ContractArtist')->findNewContracts($NB_MAX_CROWDS);

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

        $all_crowdfundings = $em->getRepository('AppBundle:ContractArtist')->findVisible();
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
     * @Route("/faq", name="faq")
     */
    public function faqAction(EntityManagerInterface $em) {
        $steps = $em->getRepository('AppBundle:Step')->findOrderedStepsWithoutPhases();
        return $this->render('AppBundle:Public:faq.html.twig', array(
            'steps' => $steps,
        ));
    }

    /**
     * @Route("/team", name="team")
     */
    public function teamAction() {
        return $this->render('AppBundle:Public:team.html.twig');
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
            $form = $this->createForm(UserSuggestionBoxType::class, $suggestionBox, ['attr' => ['class' => 'suggestionBoxForm'], 'action' => $this->generateUrl('suggestionBox_form')]);
        }else{
            $form = $this->createForm(SuggestionBoxType::class, $suggestionBox, ['attr' => ['class' => 'suggestionBoxForm'], 'action' => $this->generateUrl('suggestionBox_form')]);
        }

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($suggestionBox);
            $em->flush();

            $mailDispatcher = $this->get(MailDispatcher::class);
            if($suggestionBox->getMailCopy() && !empty($suggestionBox->getEmail())) {
                $mailDispatcher->sendSuggestionBoxCopy($suggestionBox);
            }

            $mailDispatcher->sendAdminContact($suggestionBox);
            $notifDispatcher = $this->get(NotificationDispatcher::class);
            $notifDispatcher->notifyAdminContact($suggestionBox);

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
        $halls = $em->getRepository('AppBundle:Hall')->findBy(array('visible' => true));
        shuffle($halls);

        return $this->render('@App/Public/catalog_halls.html.twig', array(
            'halls' => $halls,
        ));
    }

    /**
     * @Route("/halls/{id}-{slug}", name="hall")
     */
    public function hallAction(Hall $hall, $slug = null) {

        if($slug !== null && $slug != $hall->getSlug()) {
            return $this->redirectToRoute('hall', ['id' => $hall->getId(), 'slug' => $hall->getSlug()]);
        }

        if(!$hall->getVisible()) {
            throw $this->createNotFoundException('Hall not visible');
        }

        return $this->render('@App/Public/hall.html.twig', array(
            'hall' => $hall,
        ));
    }

    /**
     * @Route("/crowdfundings", name="catalog_crowdfundings")
     */
    public function artistContractsAction(UserInterface $user = null) {

        $em = $this->getDoctrine()->getManager();
        $current_contracts = $em->getRepository('AppBundle:ContractArtist')->findNotSuccessfulYet();
        $succesful_contracts = $em->getRepository('AppBundle:ContractArtist')->findSuccessful();
        $prevalidation_contracts = $em->getRepository('AppBundle:ContractArtist')->findInPreValidationContracts($user, $this->get('user_roles_manager'));

        return $this->render('@App/Public/catalog_artist_contracts.html.twig', array(
            'current_contracts' => $current_contracts,
            'successful_contracts' => $succesful_contracts,
            'prevalidation_contracts' => $prevalidation_contracts,
        ));
    }

    /**
     * @Route("/events/{id}-{slug}", name="artist_contract")
     */
    public function artistContractAction(Request $request, UserInterface $user = null, ContractArtist $contract, $slug = null)
    {
        if ($contract->getArtist()->getSlug() != $slug) {
            return $this->redirectToRoute('artist_contract', ['id' => $contract->getId(), 'slug' => $contract->getArtist()->getSlug()]);
        }

        $em = $this->getDoctrine()->getManager();
        $potential_halls = $em->getRepository('AppBundle:Hall')->findPotential($contract->getStep(), $contract->getProvince());

        $cf = new ContractFan($contract);
        $form = $this->createForm(ContractFanType::class, $cf);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($contract->isUncrowdable()) {
                $this->addFlash('error', 'errors.event.uncrowdable');
            } elseif ($cf->getCounterPartsQuantityOrganic() > $contract->getTotalNbAvailable()) {
                $this->addFlash('error', 'errors.order_max');
            } elseif ($cf->getCounterPartsQuantity() > $contract->getTotalNbAvailable() + ContractArtist::MAXIMUM_PROMO_OVERFLOW) {
                $this->addFlash('error', 'errors.order_max_promo');
            }

            // elseif($user == null) {
            //     throw $this->createAccessDeniedException();
            // }

            else {
                /** @var Cart $cart */
                if ($user != null) {
                    $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);
                }

                if (!isset($cart) || $cart == null) {
                    $cart = $this->createCartForUser($user);
                } else {
                    $cart = $this->cleanCart($cart, $em);
                }

                foreach ($cf->getPurchases() as $purchase) {
                    if ($purchase->getQuantity() == 0) {
                        $cf->removePurchase($purchase);
                    }
                }
                $cart->addContract($cf);

                $em->flush();
                $request->getSession()->set('cart_id', $cart->getId());
                return $this->redirectToRoute('checkout');
            }
        }

        return $this->render('@App/Public/artist_contract.html.twig', array(
            'contract' => $contract,
            'form' => $form->createView(),
            'potential_halls' => $potential_halls,
        ));
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function checkoutAction(Request $request, UserInterface $user = null) {

        $cart_id = $request->getSession()->get('cart_id', null);
        /** @var $cart Cart */
        if($cart_id == null) {
            $this->addFlash('error', 'errors.order_changed');
            return $this->redirectToRoute('catalog_crowdfundings');
        }

        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->find($cart_id);

        // When user logs in at this point, we could find another cart already related to him
        // -> that potential cart must be removed from DB as we should only use the $cart instance
        if($user != null) {
            $other_potential_cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

            if($other_potential_cart != null && $other_potential_cart->getId() != $cart_id) {
                $em->remove($other_potential_cart);
            }

            $cart->setUser($user);
            $em->persist($cart);
            $em->flush();
        }

        $form_view = null;

        // Registration form
        if(!$user) {
            /** @var $formFactory FactoryInterface */
            $formFactory = $this->get('fos_user.registration.form.factory');
            /** @var $userManager UserManagerInterface */
            $userManager = $this->get('fos_user.user_manager');
            /** @var $dispatcher EventDispatcherInterface */
            $dispatcher = $this->get('event_dispatcher');

            $user = $userManager->createUser();
            $user->setEnabled(true);

            $event = new GetResponseUserEvent($user, $request);
            $dispatcher->dispatch(FOSUserEvents::REGISTRATION_INITIALIZE, $event);

            if (null !== $event->getResponse()) {
                return $event->getResponse();
            }

            $form = $formFactory->createForm();
            $form->setData($user);

            $form->handleRequest($request);

            $form_view = $form->createView();

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $event = new FormEvent($form, $request);
                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_SUCCESS, $event);

                    $userManager->updateUser($user);

                    if (null === $response = $event->getResponse()) {
                        $url = $this->generateUrl('fos_user_registration_confirmed');
                        $response = new RedirectResponse($url);
                    }

                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_COMPLETED, new FilterUserResponseEvent($user, $request, $response));

                    $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
                    $this->get('security.token_storage')->setToken($token);
                    $this->get('session')->set('_security_main', serialize($token));
                    $event = new InteractiveLoginEvent($request, $token);
                    $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);

                    $form_view = null;

                    $this->addFlash('notice', 'notices.registration');
                    return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
                }
                else {
                    $event = new FormEvent($form, $request);
                    $dispatcher->dispatch(FOSUserEvents::REGISTRATION_FAILURE, $event);
                }

            }
        }

        return $this->render('@App/User/pay_cart.html.twig', array(
            'cart' => $cart,
            'error_conditions' => false,
            'contract_fan' => $cart->getFirst(),
            'form' => $form_view,
        ));
    }



    /**
     * @Route("/artists", name="catalog_artists")
     */
    public function artistsAction(Request $request, UserInterface $user = null) {
        $em = $this->getDoctrine()->getManager();

        $artists = $em->getRepository('AppBundle:Artist')->findBy(['deleted' => false], ['artistname' => 'ASC']);

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
     * @Route("/artists/{id}-{slug}", name="artist_profile")
     */
    public function artistProfileAction(Request $request, UserInterface $user = null, Artist $artist, $slug = null) {

        if($slug !== null && $slug != $artist->getSlug()) {
            return $this->redirectToRoute('artist_contract', ['id' => $artist->getId(), 'slug' => $artist->getSlug()]);
        }

        return $this->render('@App/Public/artist_profile.html.twig', array(
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/validate-ownership-{id}/{code}", name="artist_validate_ownership")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function validateOwnershipAction(Request $request, UserInterface $user = null, Artist $artist, $code, TranslatorInterface $translator) {

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
            ->add('accept', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-primary'],
                'label' => 'labels.ownershiprequest.accept',
            ))
            ->add('refuse', SubmitType::class, array(
                'attr' => ['class' => 'btn btn-secondary'],
                'label' => 'labels.ownershiprequest.refuse',
            ))
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
                $this->addFlash('notice', $translator->trans('notices.artist_ownership_request_accepted', ['%artist%' => $artist->getArtistname()]));
            }
            elseif($form->get('refuse')->isClicked()) {
                $req->setRefused(true);
                $em->flush();
                $this->addFlash('notice', 'notices.artist_ownership_request_refused');
            }

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
            $this->addFlash('error', 'errors.change_email_token_expired');
            return $this->redirectToRoute('homepage');
        }

        $asked_email = $user->getAskedEmail();

        $error_detector = $em->getRepository('AppBundle:User')->findOneBy(['email' => $asked_email]);
        if($error_detector != null) {
            $this->addFlash('error', 'errors.change_email_used_since');
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

            $this->addFlash('notice', 'notices.change_email_logged_out');
        }

        else {
            $this->addFlash('notice', 'notices.change_email');
        }

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }
  
    /**
     * @Route("/proposition", name="proposition")
     */
    public function propositionAction(Request $request, MailDispatcher $mailDispatcher, NotificationDispatcher $notificationDispatcher){
        $propositionContractArtist = new PropositionContractArtist();
        $form = $this->createForm(PropositionContractArtistType::class, $propositionContractArtist);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($propositionContractArtist);
            $em->flush();

            try {
                $mailDispatcher->sendAdminProposition($propositionContractArtist);
                $notificationDispatcher->notifyAdminProposition($propositionContractArtist);
            } catch(\Exception $e) {

            }
            $this->addFlash('notice', 'notices.proposition');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }
        return $this->render('AppBundle:Public:proposition.html.twig', array(
            'form' => $form->createView(),
        ));
    }
}