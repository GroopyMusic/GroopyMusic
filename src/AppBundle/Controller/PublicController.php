<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractArtistSales;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Hall;
use AppBundle\Entity\PropositionContractArtist;
use AppBundle\Entity\User;
use AppBundle\Entity\SuggestionBox;
use AppBundle\Form\CartType;
use AppBundle\Form\ContractFanType;
use AppBundle\Form\PropositionContractArtistType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\RewardSpendingService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Mailgun\Mailgun;
use Psr\Log\LoggerInterface;
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
use AppBundle\Services\ArrayHelper;

class PublicController extends Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    // Duplicated from UserController
    private function createCartForUser($user)
    {
        $cart = new Cart();
        $cart->setUser($user);
        $this->getDoctrine()->getManager()->persist($cart);
        return $cart;
    }

    private function cleanCart(Cart $cart, $em)
    {
        if ($cart->getPaid() && $cart->getConfirmed()) {
            return $this->createCartForUser($cart->getUser());
        } else {
            foreach ($cart->getContracts() as $contract) {
                $cart->removeContract($contract);
                $em->remove($contract);
            }
            return $cart;
        }
    }

    private function handleCheckout($cfs, $user, EntityManagerInterface $em, Request $request) {
        /** @var Cart $cart */
        if ($user != null) {
            $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);
        }

        if (!isset($cart) || $cart == null) {
            $cart = $this->createCartForUser($user);
        } else {
            $cart = $this->cleanCart($cart, $em);
        }

        foreach($cfs as $cf) {
            /** @var ContractFan $cf */
            $qty = 0;
            foreach ($cf->getPurchases() as $purchase) {
                $pqty = $purchase->getQuantity();
                if ($pqty == 0) {
                    $cf->removePurchase($purchase);
                }
                $qty += $pqty;
            }
            if($qty == 0) {
                if ($cart->hasContract($cf)) {
                    $cart->removeContract($cf);
                }
            }
            else {
                if(!$cart->hasContract($cf)) {
                    $cart->addContract($cf);
                }
            }
        }

        foreach($cart->getContracts() as $contract_fan) {
            if ($contract_fan->getContractArtist()->isUncrowdable()) {
                $this->addFlash('error', 'errors.event.uncrowdable');
            }
        }

        $em->flush();
        $request->getSession()->set('cart_id', $cart->getId());
    }

    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request, UserInterface $user = null)
    {
        $em = $this->getDoctrine()->getManager();

        $NB_MAX_NEWS = 4;
        $NB_MAX_CROWDS = 10000;

        $new_artists = $em->getRepository('AppBundle:Artist')->findNewArtists($NB_MAX_NEWS);

        $news = array_map(function ($artist) {
            return ['type' => 'artist', 'object' => $artist];
        }, $new_artists);

        $all_crowdfundings = $em->getRepository('AppBundle:ContractArtist')->findVisible();
        $potential_spotlights = $all_crowdfundings;

        // --------------- Order of crowdfundings determination : based on genres preferences
        $crowdfundings = [];

        if ($user != null && count($user->getGenres()) > 0) {
            $genres = $user->getGenres()->toArray();
            $genre = $genres[array_rand($genres, 1)];
        } else {
            $genre = null;
        }


        // Efficient shuffle
        if ($genre != null) {
            for ($i = 0; $i < $NB_MAX_CROWDS && count($all_crowdfundings) > 0; $i++) {

                $randomKey = array_rand($all_crowdfundings, 1);

                if ($i < 2) {
                    $genre_candidates = array_filter($all_crowdfundings, function ($elem, $key) use ($genre) {
                        return $elem->getArtist()->getGenres()->contains($genre);
                    }, ARRAY_FILTER_USE_BOTH);

                    if (count($genre_candidates) > 0) {
                        $randomKey = array_rand($genre_candidates, 1);
                    }
                }

                $crowdfundings[] = $all_crowdfundings[$randomKey];
                unset($all_crowdfundings[$randomKey]);
            }
        } else {
            $crowdfundings = $all_crowdfundings;
        }

        $sales = $em->getRepository('AppBundle:ContractArtistSales')->findVisible();
        $crowdfundings = array_merge($crowdfundings, $sales);

        // -------------------------------------------------

        // --------------- Spotlight determination

        /** @var ContractArtist $spotlight */
        $spotlight = null;
        shuffle($potential_spotlights);
        foreach ($potential_spotlights as $c) {
            /** @var ContractArtist $c */
            if ($spotlight == null) {
                $spotlight = $c;
                continue;
            }
            if ($c->isCrowdable()) {

                if (!$spotlight->isCrowdable()) {
                    $spotlight = $c;
                    continue;
                }

                // Best candidate
                if ($c->isInSuccessfulState()) {
                    if (!$spotlight->isInSuccessfulState()) {
                        $spotlight = $c;
                        continue;
                    }
                }

                if ($spotlight->isInSuccessfulState()) {
                    continue;
                }
            } else {
                if ($spotlight->isCrowdable()) {
                    continue;
                }
            }
            $spotlight = rand(0, 1) == 0 ? $spotlight : $c;
        }
        // -------------------------------------------------

        return $this->render('AppBundle:Public:home.html.twig', array(
            'news' => $news,
            'crowdfundings' => $crowdfundings,
            'spotlight' => $spotlight,
        ));
    }

    /**
     * @Route("/about", name="about")
     */
    public function aboutAction()
    {
        return $this->render('AppBundle:Public:about.html.twig');
    }

    /**
     * @Route("/faq", name="faq")
     */
    public function faqAction(EntityManagerInterface $em)
    {
        $steps = $em->getRepository('AppBundle:Step')->findOrderedStepsWithoutPhases();
        return $this->render('AppBundle:Public:faq.html.twig', array(
            'steps' => $steps,
        ));
    }

    /**
     * @Route("/team", name="team")
     */
    public function teamAction()
    {
        return $this->render('AppBundle:Public:team.html.twig');
    }

    /**
     * @Route("/suggestions", name="suggestionBox")
     */
    public function suggestionBoxAction()
    {
        return $this->render('AppBundle:Public:suggestionBox.html.twig');
    }

    /**
     * @Route("/suggestions/post", name="suggestionBox_form")
     */
    public function suggestionBoxFormAction(Request $request, UserInterface $user = null)
    {
        $suggestionBox = new SuggestionBox();

        if ($user != null) {
            $suggestionBox->setUser($user);
            $form = $this->createForm(UserSuggestionBoxType::class, $suggestionBox, ['attr' => ['class' => 'suggestionBoxForm'], 'action' => $this->generateUrl('suggestionBox_form')]);
        } else {
            $form = $this->createForm(SuggestionBoxType::class, $suggestionBox, ['attr' => ['class' => 'suggestionBoxForm'], 'action' => $this->generateUrl('suggestionBox_form')]);
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($suggestionBox);
            $em->flush();

            $mailDispatcher = $this->get(MailDispatcher::class);
            if ($suggestionBox->getMailCopy() && !empty($suggestionBox->getEmail())) {
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
     * @Route("/crowdfundings", name="catalog_crowdfundings")
     */
    public function artistContractsAction(UserInterface $user = null)
    {
        $em = $this->getDoctrine()->getManager();
        $current_contracts = $em->getRepository('AppBundle:ContractArtist')->findVisible();
        $prevalidation_contracts = $em->getRepository('AppBundle:ContractArtist')->findInPreValidationContracts($user, $this->get('user_roles_manager'));

        $sales_contracts = $em->getRepository('AppBundle:ContractArtistSales')->findVisible();

        /*$provinces = array_unique(array_map(function(ContractArtist $elem) {
            return $elem->getFestival();
        }, $current_contracts));
*/
        $genres = array_unique(ArrayHelper::flattenArray(array_map(function(ContractArtist $elem) {
            return $elem->getGenres();
        }, $current_contracts)));

        $steps = array_unique(array_map(function(ContractArtist $elem) {
            return $elem->getStep();
        }, $current_contracts));

        return $this->render('@App/Public/catalog_artist_contracts.html.twig', array(
            'current_contracts' => $current_contracts,
            'prevalidation_contracts' => $prevalidation_contracts,
            'sales_contracts' => $sales_contracts,
            //'provinces' => $provinces,
            'genres' => $genres,
            'steps' => $steps,
        ));
    }

    /**
     * @Route("/events/{id}-{slug}", name="artist_contract")
     */
    public function artistContractAction(Request $request, UserInterface $user = null, ContractArtist $contract, $slug = null)
    {
        if ($contract->getSlug() != $slug) {
            return $this->redirectToRoute('artist_contract', ['id' => $contract->getId(), 'slug' => $contract->getSlug()]);
        }

        $em = $this->getDoctrine()->getManager();

        $isParticipant = false;
        $nb_sponsorships = 0;
        $nb_validated_sponsorships = 0;
        if ($user != null) {
            $potential_user_rewards = $em->getRepository('AppBundle:User_Reward')->getPossibleActiveRewards($user, $contract);
            if ($em->getRepository('AppBundle:User')->isParticipant($contract->getId(), $user->getId()) != null) {
                $isParticipant = true;
                $nb_sponsorships = $user->getSponsorships()->count();
                $nb_validated_sponsorships = 0;
                $nb_validated_sponsorships = $em->getRepository('AppBundle:SponsorshipInvitation')->getNumberOfValidatedInvitation($user->getId());
            }
        } else {
            $potential_user_rewards = [];
        }

        $cf = new ContractFan($contract);
        $form = $this->createForm(ContractFanType::class, $cf, ['user_rewards' => $potential_user_rewards, 'entity_manager' => $em,]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $this->handleCheckout([$cf], $user, $em, $request);

            return $this->redirectToRoute('checkout');
        }

        return $this->render('@App/Public/artist_contract.html.twig', array(
            'contract' => $contract,
            'form' => $form->createView(),
            'potential_user_rewards' => $potential_user_rewards,
            'is_participant' => $isParticipant,
            'nb_sponsorships' => $nb_sponsorships,
            'nb_validated_sponsorships' => $nb_validated_sponsorships
        ));
    }

    /**
     * @Route("/sales/{id}-{slug}", name="artist_contract_sales")
     */
    public function artistContractSalesAction(Request $request, UserInterface $user = null, ContractArtistSales $contract, $slug = null)
    {
        if ($contract->getArtist()->getSlug() != $slug) {
            return $this->redirectToRoute('artist_contract_sales', ['id' => $contract->getId(), 'slug' => $contract->getArtist()->getSlug()]);
        }

        $em = $this->getDoctrine()->getManager();

        $cf = new ContractFan($contract);
        $form = $this->createForm(ContractFanType::class, $cf, ['entity_manager' => $em]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if ($contract->isUncrowdable()) {
                $this->addFlash('error', 'errors.sales.uncrowdable'); // TODO
            } elseif ($cf->getCounterPartsQuantityOrganic() > $contract->getTotalNbAvailable()) {
                $this->addFlash('error', 'errors.order_max');
            } else {
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

        return $this->render('@App/Public/artist_contract_sales.html.twig', array(
            'contract' => $contract,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/checkout", name="checkout")
     */
    public function checkoutAction(Request $request, UserInterface $user = null, RewardSpendingService $rewardSpendingService)
    {

        $cart_id = $request->getSession()->get('cart_id', null);
        /** @var $cart Cart */
        if ($cart_id == null) {
            $this->addFlash('error', 'errors.order_changed');
            return $this->redirectToRoute('catalog_crowdfundings');
        }

        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->find($cart_id);

        // When user logs in at this point, we could find another cart already related to him
        // -> that potential cart must be removed from DB as we should only use the $cart instance
        if ($user != null) {
            $other_potential_cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

            if ($other_potential_cart != null && $other_potential_cart->getId() != $cart_id) {
                $em->remove($other_potential_cart);
            }

            ##reward consume
            $rewardSpendingService->setBaseAmount($cart->getFirst());
            $cart->getFirst()->setUserRewards(new arrayCollection($rewardSpendingService->getApplicableReward($cart->getFirst())));
            $rewardSpendingService->applyReward($cart->getFirst());

            $cart->setUser($user);
            $em->persist($cart);
            $em->flush();
        }

        $form_view = null;

        // Registration form
        if (!$user) {
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
                } else {
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
    public function artistsAction(Request $request, UserInterface $user = null)
    {
        $em = $this->getDoctrine()->getManager();

        $artists = $em->getRepository('AppBundle:Artist')->findVisible();
        $genres = $em->getRepository('AppBundle:Genre')->findAll();
        $provinces = $em->getRepository('AppBundle:Province')->findAll();

        if ($user != null && count($user->getGenres()) > 0) {
            usort($artists, function (Artist $a, Artist $b) use ($user) {
                if ($a->getScore($user) == $b->getScore($user))
                    return 0;
                if ($a->getScore($user) > $b->getScore($user))
                    return 1;
                return -1;
            });
        }

        return $this->render('@App/Public/catalog_artists.html.twig', array(
            'artists' => $artists,
            'genres' => $genres,
            'provinces' => $provinces,
        ));
    }

    /**
     * @Route("/artists/{id}-{slug}", name="artist_profile")
     */
    public function artistProfileAction(Request $request, UserInterface $user = null, Artist $artist, $slug = null, EntityManagerInterface $em)
    {
        $current_sales = $em->getRepository('AppBundle:ContractArtistSales')->findCurrentsForArtist($artist);

        if ($slug !== null && $slug != $artist->getSlug()) {
            return $this->redirectToRoute('artist_profile', ['id' => $artist->getId(), 'slug' => $artist->getSlug()]);
        }

        return $this->render('@App/Public/artist_profile.html.twig', array(
            'artist' => $artist,
            'current_sales' => $current_sales,
        ));
    }

    /**
     * @Route("/validate-ownership-{id}/{code}", name="artist_validate_ownership")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function validateOwnershipAction(Request $request, UserInterface $user = null, Artist $artist, $code, TranslatorInterface $translator)
    {

        $em = $this->getDoctrine()->getManager();
        $req = $em->getRepository('AppBundle:ArtistOwnershipRequest')->findOneBy(['code' => $code]);

        if ($req == null) {
            throw $this->createNotFoundException('There is no request with such code');
        }

        if ($req->getAccepted() || $req->getRefused()) {
            throw $this->createAccessDeniedException('Request is already accepted or refused');
        }

        $mailUser = $em->getRepository('AppBundle:User')->findOneBy(['email' => $req->getEmail()]);
        if ($mailUser != null) {
            // Manually log out if another user is logged in, then redirect to here
            // see https://stackoverflow.com/questions/28827418/log-user-out-in-symfony-2-application-when-remember-me-is-enabled/28828377#28828377
            if ($mailUser->getId() != $user->getId()) {
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
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && !$req->getCancelled()) {
            if ($form->get('accept')->isClicked()) {
                $req->setAccepted(true);

                $artist_user = new Artist_User();
                $artist_user
                    ->setArtist($artist)
                    ->setUser($user);
                $em->persist($artist_user);
                $em->flush();
                $this->addFlash('notice', $translator->trans('notices.artist_ownership_request_accepted', ['%artist%' => $artist->getArtistname()]));
            } elseif ($form->get('refuse')->isClicked()) {
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
    public function changeEmailCheckAction(Request $request, UserInterface $current_user = null, $token)
    {

        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('AppBundle:User')->findOneBy(['asked_email_token' => $token]);

        if (!$user) {
            $this->addFlash('error', 'errors.change_email_token_expired');
            return $this->redirectToRoute('homepage');
        }

        $asked_email = $user->getAskedEmail();

        $error_detector = $em->getRepository('AppBundle:User')->findOneBy(['email' => $asked_email]);
        if ($error_detector != null) {
            $this->addFlash('error', 'errors.change_email_used_since');
            return $this->redirectToRoute('homepage');
        }

        // Everything ok -> let's change email
        $user->setEmail($asked_email);
        $user->setEmailCanonical($asked_email);

        $user->setAskedEmail(null);
        $user->setAskedEmailToken(null);

        // Logout (in case another user was logged in)
        if ($current_user != null && $current_user->getId() != $user->getId()) {
            $this->get('security.token_storage')->setToken(null);

            // Invalidating the session.
            $session = $request->getSession();
            $session->invalidate();

            $this->addFlash('notice', 'notices.change_email_logged_out');
        } else {
            $this->addFlash('notice', 'notices.change_email');
        }

        $em->persist($user);
        $em->flush();

        return $this->redirectToRoute('homepage');
    }

    /**
     * @Route("/proposition", name="proposition")
     */
    public function propositionAction(Request $request, MailDispatcher $mailDispatcher, NotificationDispatcher $notificationDispatcher)
    {
        $propositionContractArtist = new PropositionContractArtist();
        $form = $this->createForm(PropositionContractArtistType::class, $propositionContractArtist);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($propositionContractArtist);
            $em->flush();

            try {
                $mailDispatcher->sendAdminProposition($propositionContractArtist);
                $notificationDispatcher->notifyAdminProposition($propositionContractArtist);
            } catch (\Exception $e) {

            }
            $this->addFlash('notice', 'notices.proposition');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }
        return $this->render('AppBundle:Public:proposition.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/sponsorship-link-token-{token}", name="sponsorship_link")
     */
    public function sponsorshipLinkAction(Request $request, UserInterface $current_user = null, LoggerInterface $logger, TranslatorInterface $translator)
    {
        try {
            if ($current_user != null) {
                $this->get('security.token_storage')->setToken(null);
                $session = $request->getSession();
                $session->invalidate();
            }
            $em = $this->getDoctrine()->getManager();
            $token = $request->get('token');
            $sponsorship = $em->getRepository('AppBundle:SponsorshipInvitation')->getSponsorshipInvitationByToken($token);
            if ($sponsorship == null) {
                $this->addFlash('error', $translator->trans('notices.sponsorship.link.error', []));
                return $this->redirectToRoute('homepage');
            } else {
                $em->persist($sponsorship);
                $sponsorship->setLastDateAcceptation(new \DateTime());
                $this->addFlash('notice', $translator->trans('notices.sponsorship.link.success', []));
                return $this->redirectToRoute('artist_contract', array("id" => $sponsorship->getContractArtist()->getId()));
            }
        } catch (\Throwable $th) {
            $this->addFlash('error', $translator->trans('notices.sponsorship.link.error', []));
            return $this->redirectToRoute('homepage');
        }
    }

    private function populateCart(Cart $cart, $artistContracts) {
        foreach($artistContracts as $artistContract) {
            $fanContract = new ContractFan($artistContract);
            $cart->addContract($fanContract);
        }
        return $cart;
    }

    /**
     * @Route("/tickets", name="tickets_marketplace")
     */
    public function ticketsAction(Request $request, EntityManagerInterface $em, UserInterface $user = null) {
        $current_contracts = $em->getRepository('AppBundle:ContractArtist')->findVisible();

        if ($user != null) {
            $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);
        }
        if (!isset($cart) || $cart == null) {
            $cart = $this->createCartForUser($user);
        } else {
            $cart = $this->cleanCart($cart, $em);
        }

        $cart = $this->populateCart($cart, $current_contracts);

        $form = $this->createForm(CartType::class, $cart);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleCheckout($cart->getContracts()->toArray(), $user, $em, $request);
            return $this->redirectToRoute('checkout');
        }

        return $this->render('@App/Public/tickets.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}