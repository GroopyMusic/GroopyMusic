<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\User_Reward;
use AppBundle\Form\ProfileType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\PDFWriter;
use AppBundle\Services\SponsorshipService;
use AppBundle\Services\TicketingManager;
use AppBundle\Services\UserRolesManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\Cart;
use AppBundle\Form\ArtistType;
use AppBundle\Form\UserEmailType;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\ContractFan;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
 */
class UserController extends BaseController
{
    /**
     * Paid carts : lists all past orders for user (based on "confirmed" carts)
     * @Route("/paid-carts", name="user_paid_carts")
     */
    public function paidCartsAction(Request $request, UserInterface $user, $ref = 0)
    {
        $refresh = $ref == '1';
        $em = $this->getDoctrine()->getManager();
        $carts = $em->getRepository('AppBundle:Cart')->findConfirmedForUser($user);
        $sponsorship_event = $em->getRepository('AppBundle:ContractArtist')->getUserContractArtists($user);

        return $this->render('@App/User/paid_carts.html.twig', array(
            'carts' => $carts,
            'possible_sponsorship_event' => $sponsorship_event,
            'refresh' => $refresh,
        ));
    }

    /**
     * My artists: shows user artists + link to create one
     * @Route("/my-artists", name="user_my_artists")
     */
    public function myArtistsAction(UserInterface $user, EntityManagerInterface $em)
    {
        $artists = $em->getRepository('AppBundle:Artist')->findForUser($user);

        return $this->render('@App/User/my_artists.html.twig', array(
            'artists' => $artists,
        ));
    }

    /**
     * New artist: creation of a new artist
     * @Route("/new-artist", name="user_new_artist")
     */
    public function newArtistAction(Request $request, UserInterface $user, TranslatorInterface $translator, MailDispatcher $mailDispatcher, NotificationDispatcher $notificationDispatcher)
    {
        $em = $this->getDoctrine()->getManager();

        // This is needed due to legacy "phase" class
        $phase = $em->getRepository('AppBundle:Phase')->findOneBy(array('num' => 1));
        $artist = new Artist($phase);

        // Fetching potential information sessions - new artists must subscribe to one
        $iss = count($em->getRepository('AppBundle:InformationSession')->findVisible()) > 0;

        $form = $this->createForm(ArtistType::class, $artist, ['edit' => false, 'iss' => $iss]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($artist);

            $au = new Artist_User();
            $au->setUser($user)->setArtist($artist);
            $em->persist($au);

            $em->flush();

            // Mails
            $mailDispatcher->sendAdminNewArtist($artist);

            $this->addFlash('notice', $translator->trans('notices.artist_create', ['%artist%' => $artist->getArtistname()]));

            return $this->redirectToRoute('user_my_artists');
        }

        return $this->render('@App/User/new_artist.html.twig', array(
            'form' => $form->createView(),
            'iss' => $iss,
        ));
    }

    /**
     * Change Email: allows users to define a new email address that will need to be confirmed
     * @Route("/change-email", name="user_change_email")
     */
    public function changeEmailAction(Request $request, UserInterface $user, TokenGeneratorInterface $token_gen)
    {
        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(UserEmailType::class, $user);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            // Manual verification of email availability
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
     * Advanced options: at the moment, only allows to delete user account, which actually anonymizes account instead of deleting it from DB
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

                # If user was the last owner of the artist, we delete said artist
                if (count($artist->getArtistsUser()) == 1) {
                    $this->suppressArtist($artist);
                }

                $em->remove($a_u);
            }

            $em->persist($user);
            $em->flush();

            // Logging out
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
     * Disconnect FB: removes link between FB api & UM account
     * @Route("/disconnect-fb", name="user_disconnect_fb")
     */
    public function disconnectFBAction(Request $request, UserInterface $user)
    {
        /** @var User $user */
        $form = $this->createFormBuilder(['submit' => false])
            ->setAction($this->generateUrl('user_disconnect_fb'))
            ->add('submit', SubmitType::class, array(
                'label' => 'profile.show.facebook.disconnect_submit',
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
     * Edit Profile: profile information form
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
     * get Order: return the order with given id as a PDF attachment
     * @Route("/user/orders/{id}", name="user_get_order")
     */
    public function getOrderAction(UserInterface $user, Cart $cart, PDFWriter $writer, EntityManagerInterface $em, UserRolesManager $rolesManager)
    {
        # Order must exist, not be refunded, and user must have access
        if ($cart->isRefunded() || ($cart->getUser() != $user && !$rolesManager->userHasRole($user, 'ROLE_ADMIN'))) {
            throw $this->createAccessDeniedException();
        }

        # If the order doesn't have a barcode id yet, create one
        if (empty($cart->getBarcodeText())) {
            $cart->generateBarCode();
        }

        # We try finding the document in file system
        $finder = new Finder();
        $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $cart->getPdfPath();
        $finder->files()->name($cart->getOrderFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $cart::ORDERS_DIRECTORY);

        # If it doesn't exist, we create it
        if (count($finder) == 0) {
            $writer->writeOrder($cart);
            $em->persist($cart);
            $em->flush();
            $finder = new Finder();
            $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $cart->getPdfPath();
            $finder->files()->name($cart->getOrderFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $cart::ORDERS_DIRECTORY);
        }

        # This foreach loop actually serves to make sure that we found a file. Variable $file is not used.
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
     * get Tickets: same process as getOrder above, but with tickets
     * @Route("/user/tickets/{id}", name="user_get_tickets")
     */
    public function getTicketsAction(Request $request, UserInterface $user, ContractFan $contract, PDFWriter $writer, TicketingManager $ticketingManager, EntityManagerInterface $em, UserRolesManager $rolesManager)
    {
        if ($contract->isRefunded() || ($contract->getUser() != $user && !$rolesManager->userHasRole($user, 'ROLE_ADMIN')) || !$contract->getContractArtist()->getCounterPartsSent()) {
            throw $this->createAccessDeniedException();
        }

        $finder = new Finder();
        $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $contract->getTicketsPath();
        $finder->files()->name($contract->getTicketsFileName())->in($this->get('kernel')->getRootDir() . '/../web/' . $contract::TICKETS_DIRECTORY);

        if (count($finder) == 0) {
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
     * Rewards: displays all rewards that a user has in his possession
     * @Route("/rewards", name="user_rewards")
     */
    public function rewardsAction(UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();
        $rewards = $em->getRepository("AppBundle:User_Reward")->getActiveUserRewards($user);
        $template = '@App/User/rewards.html.twig';
        return $this->render($template, array(
            'rewards' => $rewards
        ));
    }

    /**
     * Reward: displays one particular reward
     * @Route("/rewards/{id}", name="user_reward")
     */
    public function rewardAction(User_Reward $user_reward, UserInterface $user)
    {
        if ($user_reward->getUser() !== $user) {
            throw $this->createAccessDeniedException();
        }
        return new Response($this->renderView('@App/User/reward_modal.html.twig', array(
            'user_reward' => $user_reward
        )));
    }

    // AJAX ----------------------------------------------------------------------------------------------------------------------

    /**
     * Send Sponsorship Invitation: to be used with AJAX / Sends an invitation to recipients
     * @Route("/api/send-sponsorship-invitation", name="user_ajax_send_sponsorship_invitation")
     */
    public function sendSponsorshipInvitation(Request $request, UserInterface $user, SponsorshipService $sponsorshipService, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        try {
            if ($user == null) {
                return new Response($translator->trans('notices.sponsorship.send_sponsorship.not_connected', []), 500);
            }
            // Assert that the event is valid
            $contract = $em->getRepository('AppBundle:ContractArtist')->find(intval($request->get('contractArtist')));
            if ($contract == null
                || $contract->isSoldOut()
                || !$contract->isCrowdable()
                || $em->getRepository('AppBundle:ContractArtist')->isValidForSponsorship($contract->getId()) == null) {
                return new Response($translator->trans('notices.sponsorship.send_sponsorship.event_not_valid', []), 500);
            }
            // Create Response based on service answer to our POST parameters
            $response = $sponsorshipService->sendSponsorshipInvitation($request->get('emails'), $request->get('content'), $contract, $user);
            return $this->redirectToRoute('user_ajax_display_sponsorship_invitation_modal', array(
                'success' => $response[0],
                'emails' => $response[1],
                'success_message' => $translator->trans('notices.sponsorship.send_sponsorship.success', []),
                'warning_message' => $translator->trans('notices.sponsorship.send_sponsorship.warning_message', []),
                'defined' => $request->get('defined')
            ));
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500);
            //return new Response($translator->trans('notices.sponsorship.send_sponsorship.error', []), 500);
        }
    }

    /**
     * Display Sponorship Modal: depending on user, displays possible invitations for friends to join event
     * @Route("/api/display-sponsorship-invitation-modal", name="user_ajax_display_sponsorship_invitation_modal")
     */
    public function displaySponsorshipModalAction(Request $request, UserInterface $user, SponsorshipService $sponsorshipService, TranslatorInterface $translator)
    {
        $em = $this->getDoctrine()->getManager();
        $result = [[], []];
        $defined = $request->get('defined');
        $contracts = [];
        if ($user != null) {
            if ($defined === false || $defined === null || $defined === 'false') {
                $defined = false;
                $contracts = $em->getRepository('AppBundle:ContractArtist')->getUserContractArtists($user);
            }
            $result = $sponsorshipService->getSponsorshipSummaryForUser($user);
        } else {
            return new Response($translator->trans('notices.sponsorship.send_sponsorship.not_connected', []), 500);
        }
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
