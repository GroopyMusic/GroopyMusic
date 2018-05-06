<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ConsomableReward;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\InvitationReward;
use AppBundle\Entity\Notification;
use AppBundle\Entity\ReductionReward;
use AppBundle\Entity\User;
use AppBundle\Entity\User_Reward;
use AppBundle\Form\ProfilePreferencesType;
use AppBundle\Form\ProfileType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\PDFWriter;
use AppBundle\Services\SponsorshipService;
use AppBundle\Services\TicketingManager;
use AppBundle\Services\UserRolesManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\Cart;
use AppBundle\Entity\Purchase;
use AppBundle\Form\ArtistType;
use AppBundle\Form\UserEmailType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\ContractFan;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Translation\TranslatorInterface;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class UserController extends Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    private function createCartForUser($user)
    {
        $cart = new Cart();
        $cart->setUser($user);
        $this->getDoctrine()->getManager()->persist($cart);
        return $cart;
    }

    /**
     * @Route("/inbox", name="user_notifications")
     */
    public function notifsAction(Request $request, UserInterface $user)
    {
        $firstResult = $request->get('first_result', 0);
        $nbPerPage = $request->get('nb_per_page', 5);
        $notifs = $this->getDoctrine()->getRepository('AppBundle:Notification')->paginateForUser($user, $firstResult, $nbPerPage);

        $got_to_max = $firstResult + $nbPerPage >= count($notifs);
        $max = count($notifs);

        if ($request->getMethod() == 'POST') {
            $template = '@App/User/render_notifications_previews.html.twig';
        } else {
            $template = '@App/User/notifications.html.twig';
        }

        return $this->render($template, array(
            'notifs' => $notifs,
            'got_to_max' => $got_to_max,
            'max' => $max,
        ));
    }

    /**
     * @Route("/inbox/notifications/{id}", name="user_notification")
     */
    public function notifAction(Notification $notif, Request $request, UserInterface $user)
    {
        if ($notif->getUser() != $user) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $notif->setSeen(true);
        $em->persist($notif);
        $em->flush();

        return new Response($this->renderView('@App/User/notification.html.twig', array(
            'notif' => $notif,
        )));
    }

    /**
     * @Route("/cart", name="user_cart")
     */
    public function cartAction(UserInterface $user)
    {

        $em = $this->getDoctrine()->getManager();
        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

        if ($cart == null) {
            $cart = $this->createCartForUser($user);
            $em->flush();
        }

        return $this->render('@App/User/cart.html.twig', array(
            'cart' => $cart,
        ));
    }

    /**
     * @Route("/paid-carts", name="user_paid_carts")
     */
    public function paidCartsAction(Request $request, UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();
        $carts = $em->getRepository('AppBundle:Cart')->findConfirmedForUser($user);

        return $this->render('@App/User/paid_carts.html.twig', array(
            'carts' => $carts,
            'is_payment' => $request->get('is_payment')
        ));
    }


    /**
     * @Route("/my-artists", name="user_my_artists")
     */
    public function myArtistsAction(UserInterface $user, EntityManagerInterface $em)
    {

        $artists = $em->getRepository('AppBundle:Artist')->findForUser($user);

        $available_artist = false;

        foreach ($artists as $artist) {
            if ($artist->isAvailable()) {
                $available_artist = true;
                break;
            }
        }

        return $this->render('@App/User/my_artists.html.twig', array(
            'artists' => $artists,
            'available_artist' => $available_artist,
        ));
    }

    /**
     * @Route("/new-artist", name="user_new_artist")
     */
    public function newArtistAction(Request $request, UserInterface $user, TranslatorInterface $translator, MailDispatcher $mailDispatcher, NotificationDispatcher $notificationDispatcher)
    {

        $em = $this->getDoctrine()->getManager();

        $phase = $em->getRepository('AppBundle:Phase')->findOneBy(array('num' => 1));

        $artist = new Artist($phase);

        $form = $this->createForm(ArtistType::class, $artist, ['edit' => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($artist);

            $au = new Artist_User();
            $au->setUser($user)->setArtist($artist);
            $em->persist($au);

            $em->flush();

            $mailDispatcher->sendAdminNewArtist($artist);
            $notificationDispatcher->notifyAdminNewArtist($artist);

            $this->addFlash('notice', $translator->trans('notices.artist_create', ['%artist%' => $artist->getArtistname()]));

            return $this->redirectToRoute('user_my_artists');
        }

        return $this->render('@App/User/new_artist.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/new-crowdfunding-contact-us", name="user_new_contract_artist_temp")
     */
    public function newContractTempAction()
    {
        $this->addFlash('info', 'infos.new_event_temp');

        return $this->redirectToRoute('suggestionBox');
    }

    /**
     * @Route("/new-crowdfunding", name="user_new_contract_artist")
     */
    public function newContractAction(UserInterface $user, Request $request)
    {

        $em = $this->getDoctrine()->getManager();

        $av_artists = $em->getRepository('AppBundle:Artist')->findAvailableForNewContract($user, $this->get(UserRolesManager::class));

        if (count($av_artists) == 0) {
            return $this->render('@App/User/Artist/new_contract.html.twig', array(
                'no_artist' => true,
            ));
        }

        // New contract creation
        $contract = new ContractArtist();

        $flow = $this->get('AppBundle\Form\ContractArtistFlow');
        $flow->bind($contract);

        $form = $flow->createForm();

        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                $form = $flow->createForm();
            } else {
                // flow finished

                if (!in_array($contract->getArtist()->getId(),
                    array_map(function (Artist $artist) {
                        return $artist->getId();
                    }, $av_artists))) {
                    throw $this->createAccessDeniedException("Cet artiste ne vous appartient pas...");
                }

                // We check that there doesn't exist another contract for that artist before DB insertion
                $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($contract->getArtist());
                if ($currentContract != null) {
                    throw $this->createAccessDeniedException("Interdit de s'inscrire à deux paliers en même temps !");
                }

                // "start date" calculation based on test period or not
                $contract->generateTestPeriodAndPromotion();
                $contract->generateDateEnd();

                $em = $this->getDoctrine()->getManager();
                $em->persist($contract);
                $em->flush();

                $flow->reset(); // remove step data from the session

                $this->addFlash('notice', 'notices.event_create');
                return $this->redirectToRoute('artist_contract', ['id' => $contract->getId()]); // redirect when done
            }
        }

        return $this->render('@App/User/Artist/new_contract.html.twig', array(
            'form' => $form->createView(),
            'flow' => $flow,
            'contract' => $contract,
        ));
    }

    /**
     * @Route("/change-email", name="user_change_email")
     */
    public function changeEmailAction(Request $request, UserInterface $user, TokenGeneratorInterface $token_gen)
    {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(UserEmailType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $email = $user->getAskedEmail();

            $error_detect = $em->getRepository('AppBundle:User')->findOneBy(['email' => $email]);
            if ($error_detect != null) {
                $this->addFlash('error', 'errors.change_email_already_used');
            } else {
                $user->setAskedEmailToken($token_gen->generateToken());
                $em->persist($user);
                $this->get('AppBundle\Services\MailDispatcher')->sendEmailChangeConfirmation($user);
                $em->flush();

                $this->addFlash('notice', 'notices.change_email_request');

                return $this->redirectToRoute('user_change_email');
            }
        }

        return $this->render('@App/User/Profile/change_email.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/advanced-options", name="user_advanced_options")
     */
    public function advancedAction(Request $request, UserInterface $user)
    {

        /** @var User $user */
        $form = $this->createFormBuilder(['submit' => false])
            ->add('submit', SubmitType::class, array(
                'label' => 'labels.user.advanced.confirm', // Supprimer mon compte
                'attr' => ['class' => 'btn btn-danger'],
            ))->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->get('submit')->isClicked()) {
            $em = $this->getDoctrine()->getManager();

            if ($user->getAddress() != null) {
                $em->remove($user->getAddress());
            }
            $user->anonymize();

            foreach ($em->getRepository('AppBundle:Artist_User')->findBy(['user' => $user]) as $a_u) {
                $artist = $a_u->getArtist();

                // Duplicated in ArtistController->Leave
                if ($artist->isAvailable() && count($artist->getArtistsUser()) == 1) {
                    $artist->setDeleted(true);
                    foreach ($em->getRepository('AppBundle:ArtistOwnershipRequest')->findBy(['artist' => $artist]) as $o_request) {
                        $em->remove($o_request);
                    }
                    $em->persist($artist);
                }
                // End duplicated

                $em->remove($a_u);
            }

            $em->persist($user);
            $em->flush();

            $session = $request->getSession();
            $session->clear();

            $this->addFlash('notice', 'notices.account_deletion');
            $response = $this->redirectToRoute('homepage');

            // Clearing the cookies.
            $cookieNames = [
                $this->container->getParameter('session.name'),
                $this->container->getParameter('session.remember_me.name'),
            ];
            foreach ($cookieNames as $cookieName) {
                $response->headers->clearCookie($cookieName);
            }

            $this->addFlash('notice', 'notices.account_deletion');

            return $response;
        }

        return $this->render('@App/User/Profile/advanced.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/disconnect-fb", name="user_disconnect_fb")
     */
    public function disconnectFBAction(Request $request, UserInterface $user)
    {
        /** @var User $user */
        $form = $this->createFormBuilder(['submit' => false])
            ->setAction($this->generateUrl('user_disconnect_fb'))
            ->add('submit', SubmitType::class, array(
                'label' => 'profile.show.facebook.disconnect_submit', // Supprimer mon compte
                'translation_domain' => 'FOSUserBundle',
                'attr' => ['class' => 'btn btn-danger'],
            ))->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->get('submit')->isClicked()) {
            $user->setFacebookId(null)->setFacebookAccessToken(null);
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('notice', 'notices.edition');
            return $this->redirectToRoute('fos_user_profile_show');
        }

        return new Response($this->renderView('@App/User/Profile/_disconnect_fb.html.twig', [
            'form' => $form->createView(),
        ]));
    }

    /**
     * @Route("/edit-profile", name="user_profile_edit")
     */
    public function editProfileAction(Request $request, UserInterface $user)
    {

        $form = $this->createForm(ProfileType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('notice', 'notices.edition');

            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        return $this->render('@FOSUser/Profile/edit.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/user/orders/{id}", name="user_get_order")
     */
    public function getOrderAction(Request $request, UserInterface $user, Cart $cart, PDFWriter $writer, EntityManagerInterface $em)
    {

        $contract = $cart->getFirst();
        if ($contract->isRefunded() || $contract->getUser() != $user) {
            throw $this->createAccessDeniedException();
        }

        if (empty($contract->getBarcodeText())) {
            $contract->generateBarCode();
        }

        $finder = new Finder();
        $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $contract->getPdfPath();
        $finder->files()->name($contract->getOrderFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $contract::ORDERS_DIRECTORY);

        if (count($finder) == 0) {
            $writer->writeOrder($contract);
            $em->persist($contract);
            $em->flush();
            $finder = new Finder();
            $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $contract->getPdfPath();
            $finder->files()->name($contract->getOrderFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $contract::ORDERS_DIRECTORY);
        }

        foreach ($finder as $file) {
            $response = new BinaryFileResponse($filePath);
            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-Type', 'PDF');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'um-order.pdf'
            ));

            return $response;
        }
    }

    /**
     * @Route("/user/tickets/{id}", name="user_get_tickets")
     */
    public function getTicketsAction(Request $request, UserInterface $user, Cart $cart, PDFWriter $writer, TicketingManager $ticketingManager, EntityManagerInterface $em)
    {

        $contract = $cart->getFirst();
        if ($contract->isRefunded() || $contract->getUser() != $user || !$contract->getContractArtist()->getCounterPartsSent()) {
            throw $this->createAccessDeniedException();
        }

        $finder = new Finder();
        $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $contract->getTicketsPath();
        $finder->files()->name($contract->getTicketsFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $contract::TICKETS_DIRECTORY);

        if (count($finder) == 0) {

            // TODO is this line necessary ?
            $writer->writeOrder($contract);

            $ticketingManager->generateTicketsForContractFan($contract);
            $writer->writeTickets($contract->getTicketsPath(), $contract->getTickets());
            $contract->setcounterpartsSent(true);

            $em->persist($contract);
            $em->flush();
            $finder = new Finder();
            $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $contract->getTicketsPath();
            $finder->files()->name($contract->getTicketsFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $contract::TICKETS_DIRECTORY);
        }

        foreach ($finder as $file) {
            $response = new BinaryFileResponse($filePath);
            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-Type', 'PDF');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                'um-tickets.pdf'
            ));
            return $response;
        }
    }

    /**
     * @Route("/rewards", name="user_rewards")
     */
    public function rewardsAction(Request $request, UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();
        $rewards = $em->getRepository("AppBundle:User_Reward")->getActiveUserRewards($user);
        $template = '@App/User/rewards.html.twig';
        return $this->render($template, array(
            'rewards' => $rewards
        ));
    }

    /**
     * @Route("/rewards/{id}", name="user_reward")
     */
    public function rewardAction(User_Reward $user_reward, Request $request, UserInterface $user)
    {
        $type = "";
        $reward = $user_reward->getReward();
        if ($user_reward->getUser() != $user) {
            throw $this->createAccessDeniedException();
        }
        if ($reward instanceof ReductionReward) {
            $type = "Reduction";
        } else if ($reward instanceof ConsomableReward) {
            $type = "Consomable";
        } else if ($reward instanceof InvitationReward) {
            $type = "Invitation";
        }
        $em = $this->getDoctrine()->getManager();
        return new Response($this->renderView('@App/User/reward.html.twig', array(
            'user_reward' => $user_reward,
            'type' => $type
        )));
    }

    // AJAX ----------------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/api/update-motivations/{id}", name="user_ajax_update_motivations")
     */
    public function updateMotivations(Request $request, UserInterface $user, ContractArtist $contract)
    {
        $artist = $contract->getArtist();
        if (!$user->owns($artist)) {
            throw $this->createAccessDeniedException("You don't own this artist!");
        }

        $em = $this->getDoctrine()->getManager();

        $motivations = $request->request->get('motivations');

        $contract->setMotivations($motivations);
        $em->persist($contract);
        $em->flush();

        return new Response($motivations);
    }

    /**
     * @Route("/api/send-sponsorship-invitation", name="user_ajax_send_sponsorship_invitation")
     */
    public function sendSponsorshipInvitation(Request $request, LoggerInterface $logger, UserInterface $user, SponsorshipService $sponsorshipService, TranslatorInterface $translator)
    {
        $em = $em = $this->getDoctrine()->getManager();
        try {
            if ($user == null) {
                return new Response($translator->trans('notices.sponsorship.send_sponsorship.not_connected', []), 500);
            }
            $contract = $em->getRepository('AppBundle:ContractArtist')->find(intval($request->get('contractArtist')));
            if ($contract == null
                || $contract->isSoldOut()
                || !$contract->isCrowdable()
                || $em->getRepository('AppBundle:ContractArtist')->isValidForSponsorship($contract->getId()) == null) {
                return new Response($translator->trans('notices.sponsorship.send_sponsorship.event_not_valid', []), 500);
            }
            $response = $sponsorshipService->sendSponsorshipInvitation($request->get('emails'), $request->get('content'), $contract, $user);
            return $this->redirectToRoute('user_ajax_display_sponsorship_invitation_modal', array(
                'success' => $response[0],
                'emails' => $response[1],
                'success_message' => $translator->trans('notices.sponsorship.send_sponsorship.success', []),
                'warning_message' => $translator->trans('notices.sponsorship.send_sponsorship.warning_message', []),
                'defined' => $request->get('defined')
            ));
        } catch (\Throwable $th) {
            $logger->warning('error', [$th->getMessage()]);
            return new Response($translator->trans('notices.sponsorship.send_sponsorship.error', []), 500);
        }
    }

    /**
     * @Route("/api/display-sponsorship-invitation-modal", name="user_ajax_display_sponsorship_invitation_modal")
     */
    public function displaySponsorshipModalAction(Request $request, UserInterface $user, SponsorshipService $sponsorshipService, LoggerInterface $logger)
    {
        $em = $this->getDoctrine()->getManager();
        $defined = $request->get('defined');
        $contracts = [];
        if ($defined == false || $defined == null || $defined == 'false') {
            $defined = false;
            $contracts = $em->getRepository('AppBundle:ContractArtist')->getUserContractArtists($user);
        }
        $result = $sponsorshipService->getSponsorshipSummaryForUser($user);
        $logger->warning('tst', [$defined]);
        return $this->render('@App/User/sponsorship_invitations_modal.html.twig', array(
            'event_is_define' => $defined,
            'contracts' => $contracts,
            'invited' => $result[0],
            'confirmed' => $result[1],
            'success' => $request->get('success'),
            'emails' => $request->get('emails'),
            'success_message' => $request->get('success_message'),
            'warning_message' => $request->get('warning_message')
        ));
    }
}
