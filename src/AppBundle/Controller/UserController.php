<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Notification;
use AppBundle\Entity\User;
use AppBundle\Form\ProfilePreferencesType;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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

    private function createCartForUser($user) {
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

        if($request->getMethod() == 'POST') {
            $template = '@App/User/render_notifications_previews.html.twig';
        }
        else {
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
    public function notifAction(Notification $notif, Request $request, UserInterface $user) {
        if($notif->getUser() != $user) {
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
    public function cartAction(UserInterface $user) {

        $em = $this->getDoctrine()->getManager();
        $cart =  $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

        if($cart == null) {
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
    public function paidCartsAction(UserInterface $user) {
        $em = $this->getDoctrine()->getManager();
        $carts = $em->getRepository('AppBundle:Cart')->findConfirmedForUser($user);

        return $this->render('@App/User/paid_carts.html.twig', array(
            'carts' => $carts,
        ));
    }


    /**
     * @Route("/my-artists", name="user_my_artists")
     */
    public function myArtistsAction(UserInterface $fan) {

        $artists_user = $fan->getArtistsUser();

        $artists = array_map(function(Artist_User $elem) {
            return $elem->getArtist();
        }, $artists_user->toArray());

        $available_artist = false;
        foreach($artists as $artist) {
            if($artist->isAvailable()) {
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
    public function newArtistAction(Request $request, UserInterface $user) {

        $em = $this->getDoctrine()->getManager();

        $phase = $em->getRepository('AppBundle:Phase')->findOneBy(array('num' => 1));

        $artist = new Artist($phase);

        $form = $this->createForm(ArtistType::class, $artist, ['edit' => false]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($artist);

            $au = new Artist_User();
            $au->setUser($user)->setArtist($artist);
            $em->persist($au);

            $em->flush();

            $this->addFlash('notice', "L'artiste " . $artist->getArtistname() . " a bien été créé.");

            return $this->redirectToRoute('user_my_artists');
        }

        return $this->render('@App/User/new_artist.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/new-crowdfunding", name="user_new_contract_artist")
     */
    public function newContractAction(UserInterface $user, Request $request) {

        $em = $this->getDoctrine()->getManager();

        $av_artists = $em->getRepository('AppBundle:Artist')->findNotCurrentlyBusy($user);

        if(count($av_artists) == 0) {
            return $this->render('@App/User/Artist/new_contract.html.twig', array(
                'no_artist' => true,
            ));
        }

        // New contract creation
        $contract = new ContractArtist();

        $flow = $this->get('AppBundle\Form\ContractArtistFlow');
        $flow->bind($contract);

        $form = $flow->createForm();

        if($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);

            if ($flow->nextStep()) {
                // form for the next step
                $th_date = new \DateTime;
                $th_date->modify('+ ' . $contract->getStep()->getDeadlineDuration() . ' days');
                $contract->setTheoriticalDeadline($th_date);

                $form = $flow->createForm();
            } else {
                // flow finished

                // We check that there doesn't exist another contract for that artist before DB insertion
                $currentContract = $em->getRepository('AppBundle:ContractArtist')->findCurrentForArtist($contract->getArtist());
                if ($currentContract != null) {
                    throw $this->createAccessDeniedException("Interdit de s'inscrire à deux paliers en même temps !");
                }

                $deadline = new \DateTime();
                $deadline->modify('+ ' . $contract->getStep()->getDeadlineDuration() . ' days');
                $contract->setDateEnd($deadline);

                $em = $this->getDoctrine()->getManager();
                $em->persist($contract);
                $em->flush();

                $flow->reset(); // remove step data from the session

                $this->addFlash('notice', 'Votre événement a bien été créé. Rassemblez dès maintenant des producteurs qui pourront vous soutenir dans l\'organisation de celui-ci !');
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
    public function changeEmailAction(Request $request, UserInterface $user, TokenGeneratorInterface $token_gen) {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(UserEmailType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $email = $user->getAskedEmail();

            $error_detect = $em->getRepository('AppBundle:User')->findOneBy(['email' => $email]);
            if($error_detect != null) {
                $form->addError(new FormError('Cette adresse e-mail est déjà associée à un compte Un-Mute.'));
            }

            else {
                $user->setAskedEmailToken($token_gen->generateToken());
                $em->persist($user);
                $this->get('AppBundle\Services\MailDispatcher')->sendEmailChangeConfirmation($user);
                $em->flush();

                $this->addFlash('notice', 'Votre demande a bien été reçue. Pour valider le changement d\'adresse e-mail, il vous faut cliquer sur un lien qui a été envoyé à la nouvelle adresse demandée.');

                return $this->redirectToRoute('user_change_email');
            }
        }

        return $this->render('@App/User/change_email.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/advanced-options", name="user_advanced_options")
     */
    public function advancedAction(Request $request, UserInterface $user) {

        $form = $this->createFormBuilder(['submit' => false])
            ->add('submit', SubmitType::class, array(
                'label' => 'Supprimer mon compte',
                'attr' => ['class' => 'btn btn-danger'],
            ))->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->get('submit')->isClicked()) {
            $em = $this->getDoctrine()->getManager();

            if($user->getAddress() != null) {
                $em->remove($user->getAddress());
            }
            $user->anonymize();

            foreach($em->getRepository('AppBundle:Artist_User')->findBy(['user' => $user]) as $a_u) {
                $artist = $a_u->getArtist();

                // Duplicated in ArtistController->Leave
                if($artist->isAvailable() && count($artist->getArtistsUser()) == 1) {
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

            $this->addFlash('notice', 'Votre compte Un-Mute a bien été supprimé. Sachez que vous pourrez toujours en créer un nouveau si le coeur vous en dit.
            Bonne continuation !');
            $response = $this->redirectToRoute('homepage');

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

        return $this->render('@App/User/advanced.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/preferences", name="user_preferences")
     */
    public function preferencesAction(Request $request, UserInterface $user) {

        $form = $this->createForm(ProfilePreferencesType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            $this->addFlash('notice', 'Vos préférences ont bien été mises à jour.');

            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }

        return $this->render('@App/User/preferences.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/user/orders/{id}", name="user_get_order")
     */
    public function getOrderAction(Request $request, UserInterface $user, Cart $cart) {

        $contract = $cart->getFirst();
        if($contract->getUser() != $user) {
            throw $this->createAccessDeniedException();
        }

        $finder = new Finder();
        $filePath = $this->get('kernel')->getRootDir() . '/../web/' . $contract->getPdfPath();
        $finder->files()->name($contract->getOrderFileName())->in($this->get('kernel')->getRootDir() . '/../web/'.$contract::ORDERS_DIRECTORY);

        foreach($finder as $file) {

            $response = new BinaryFileResponse($filePath);
            // Set headers
            $response->headers->set('Cache-Control', 'private');
            $response->headers->set('Content-Type', 'PDF');
            $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                $contract->getOrderFileName()
            ));

            return $response;
        }
    }

    // AJAX ----------------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/api/update-motivations/{id}", name="user_ajax_update_motivations")
     */
    public function updateMotivations(Request $request, UserInterface $user, ContractArtist $contract) {
        $artist = $contract->getArtist();
        if(!$user->owns($artist)) {
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
     * @Route("/api/add-to-cart", name="user_ajax_add_to_cart")
     */
    public function addToCartAction(Request $request, UserInterface $user) {

        $id_counterpart = $request->get('id_counterpart');
        $id_contract_artist = $request->get('id_contract_artist');
        $quantity = $request->get('quantity');

        $em = $this->getDoctrine()->getManager();
        $counterpart = $em->getRepository('AppBundle:CounterPart')->find($id_counterpart);
        $contractArtist = $em->getRepository('AppBundle:ContractArtist')->find($id_contract_artist);

        if($contractArtist->isUncrowdable()) {
            return new Response("KO");
        }

        if($contractArtist->getNbAvailable($counterpart) < $quantity) {
            return new Response("MAX_QTY");
        }

        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

        if($cart == null) {
            $cart = $this->createCartForUser($user);
        }

        $fanContracts = $cart->getContracts();
        foreach($fanContracts as $fc) {
            if($fc->getContractArtist()->getId() == $id_contract_artist) {
                $contract = $fc;
                break;
            }
        }

        if(!isset($contract)) {
            $contract = new ContractFan();
            $contract->setCart($cart);
            $contract->setContractArtist($contractArtist);
        }

        foreach($contract->getPurchases() as $p) {
            if($p->getCounterPart()->getId() == $id_counterpart) {
                $purchase = $p;
                break;
            }
        }

        if(!isset($purchase)) {
            $purchase = new Purchase();
            $purchase->setCounterpart($counterpart);
            $purchase->setContractFan($contract);
        }

        else {
            if($purchase->getQuantity() >= Purchase::MAX_QTY)
                return new Response("MAX_QTY");
        }

        $to_max_qty = $purchase->getQuantity() + $quantity >= Purchase::MAX_QTY;

        $purchase->addQuantity($quantity);

        $em->persist($contract);
        $em->persist($purchase);

        $em->flush();

        if($to_max_qty) {
            return new Response("TO_MAX_QTY");
        }

        return new Response("OK");
    }

    /**
     * @Route("/api/remove-all-from-cart", name="user_ajax_remove_all_from_cart")
     */
    public function removeAllFromCartAction(Request $request, UserInterface $user) {

        $em = $this->getDoctrine()->getManager();

        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

        if($cart == null) {
            $cart = $this->createCartForUser($user);
            $em->flush();
            return new Response("OK");
        }

        foreach($cart->getContracts() as $contract) {
            $em->remove($contract);
        }
        $em->flush();

        return new Response($this->renderView('@App/User/cart_content.html.twig', array(
            'cart' => $cart,
        )));
    }

    /**
     * @Route("/api/remove-purchase-from-contract", name="user_ajax_remove_from_contract")
     */
    public function removeFromContractAction(Request $request, UserInterface $user) {
        $em = $this->getDoctrine()->getManager();
        $id_purchase = $request->get('id_purchase');
        $purchase = $em->getRepository('AppBundle:Purchase')->find($id_purchase);

        if($purchase == null) {
            throw $this->createNotFoundException('Pas de purchase de numéro '. $id_purchase);
        }

        $contract = $purchase->getContractFan();
        $contract->removePurchase($purchase);
        $cart = $contract->getCart();

        $em->remove($purchase);
        $em->persist($contract);

        if(count($contract->getPurchases()) == 0) {
            $cart->removeContract($contract);
            $em->persist($cart);
            $em->remove($contract);
        }

        $em->flush();

        return new Response($this->renderView('@App/User/cart_content.html.twig', array(
            'cart' => $cart,
        )));
    }

    /**
     * @Route("/api/deblock-advantage", name="user_ajax_deblock_advantage")

    public function deblockAdvantageAction(Request $request, UserInterface $user) {
        $em = $this->getDoctrine()->getManager();

        $id_advantage = intval($request->get('id_advantage'));
        $quantity = intval($request->get('quantity'));

        $adv = $em->getRepository('AppBundle:SpecialAdvantage')->find($id_advantage);

        $purchase = new SpecialPurchase();
        $purchase->setUser($user)
            ->setQuantity($quantity)
            ->setSpecialAdvantage($adv);

        if($user->getCredits() < $purchase->getAmountCredits()) {
            return new Response("NOT_ENOUGH_CREDITS");
        }

        $user->removeCredits($purchase->getAmountCredits());

        $em->persist($user);
        $em->persist($purchase);
        $em->flush();

        return new Response($user->getCredits());
    }

    */

    /**
     * @Route("/special-advantages", name="user_special_advantages")

        public function specialAdvantagesAction() {
            $em = $this->getDoctrine()->getManager();
            $sa = $em->getRepository('AppBundle:SpecialAdvantage')->findCurrents();

            return $this->render('@App/User/special_advantages.html.twig', array(
                'advantages' => $sa,
            ));
        }
     */

    /**
     * @Route("/special-purchases", name="fan_special_purchases")

        public function specialPurchasesAction(UserInterface $user) {

            $em = $this->getDoctrine()->getManager();
            $sp = $em->getRepository('AppBundle:SpecialPurchase')->findBy(array('user' => $user), array('date' => 'DESC'));

            return $this->render('@App/User/special_purchases.html.twig', array(
                'purchases' => $sp,
            ));
        }
     */
}
