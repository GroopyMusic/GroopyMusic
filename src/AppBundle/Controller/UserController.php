<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Step;
use AppBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\Cart;
use AppBundle\Entity\Province;
use AppBundle\Entity\Purchase;
use AppBundle\Form\ArtistType;
use AppBundle\Form\ContractArtistType;
use AppBundle\Form\UserEmailType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Response;
use AppBundle\Entity\ContractFan;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class UserController extends Controller
{
    /**
     * @Route("/inbox", name="user_notifications")
     */
    public function notifsAction(Request $request, UserInterface $user)
    {
        $notifs = $this->getDoctrine()->getRepository('AppBundle:Notification')->findBy(array('user' => $user));

        return $this->render('@App/User/notifications.html.twig', array(
            'notifs' => $notifs,
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

        return $this->render('@App/User/notification.html.twig', array(
            'notif' => $notif,
        ));
    }

    /**
     * @Route("/cart", name="user_cart")
     */
    public function cartAction(UserInterface $user) {

        $em = $this->getDoctrine()->getManager();
        $cart =  $em->getRepository('AppBundle:Cart')->findCurrentForFan($user);

        if($cart == null) {
            $cart = new Cart();
            $cart->setUser($user);
            $em->persist($cart);
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
        $carts = $em->getRepository('AppBundle:Cart')->findBy(array('user' => $user, 'confirmed' => true));

        return $this->render('@App/User/paid_carts.html.twig', array(
            'carts' => $carts,
        ));
    }


    /**
     * @Route("/my-artists", name="user_my_artists")
     */
    public function myArtistsAction(UserInterface $fan) {

        $em = $this->getDoctrine()->getManager();

        $artists_user = $fan->getArtistsUser();

        return $this->render('@App/User/my_artists.html.twig', array(
            'artists_user' => $artists_user,
        ));
    }

    /**
     * @Route("/new-artist", name="user_new_artist")
     */
    public function newArtistAction(Request $request, UserInterface $user) {

        $em = $this->getDoctrine()->getManager();

        $phase = $em->getRepository('AppBundle:Phase')->findOneBy(array('num' => 1));

        $artist = new Artist($phase);

        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($artist);

            $au = new Artist_User();
            $au->setUser($user)->setArtist($artist);
            $em->persist($au);

            $em->flush();

            $this->addFlash('notice', "Bien reçu");

            return $this->redirectToRoute('user_my_artists');
        }

        return $this->render('@App/User/new_artist.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/new-crowdfunding", name="user_new_contract_artist")
     * @Security("is_granted('IS_AUTHENTICATED_REMEMBERED')")
     */
    public function newContractAction(UserInterface $user, Request $request) {

        // TODO autres subtilités
        if(count($user->getArtistsUser()) == 0) {
            // erreur, faut avoir un artiste
        }

        $em = $this->getDoctrine()->getManager();

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

                $this->addFlash('notice', 'Bien reçu');
                return $this->redirectToRoute('user_new_contract_artist'); // redirect when done
            }
        }

        return $this->render('@App/User/Artist/new_contract.html.twig', array(
            'form' => $form->createView(),
            'flow' => $flow,
            'contract' => $contract,
        ));
    }

    /**
     * @Route("change-email", name="user_change_email")
     */
    public function changeEmailAction(Request $request, UserInterface $user) {

        $em = $this->getDoctrine()->getManager();

        $form = $this->createForm(UserEmailType::class, $user);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $email = $user->getAskedEmail();

            $error_detect = $em->getRepository('AppBundle:User')->findOneBy(['email' => $email]);
            if($error_detect != null) {
                $form->get('email')->addError(new FormError('user.askemail.alreadyinuse'));
            }

            else {
                $user->setAskedEmailToken($this->get('fos_user.util.token_generator')->generateToken());
                $em->persist($user);
                $this->get('AppBundle\Services\MailDispatcher')->sendEmailChangeConfirmation($user);
                $em->flush();

                $this->addFlash('notice', 'Bien reçu votre demande, regardez vos mails');

                return $this->redirectToRoute('user_change_email');
            }
        }

        return $this->render('@App/User/change_email.html.twig', array(
            'form' => $form->createView(),
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

    // AJAX ----------------------------------------------------------------------------------------------------------------------

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

        if($contractArtist->getNbAvailable($counterpart) < $quantity) {
            return new Response("MAX_QTY");
        }

        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForUser($user);

        if($cart == null) {
            $cart = new Cart();
            $cart->setUser($user);
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
        $em->persist($cart);

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
            $cart = new Cart();
            $cart->setUser($user);

            $em->persist($cart);
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
