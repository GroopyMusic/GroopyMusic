<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\SpecialPurchase;
use AppBundle\Entity\User;
use AppBundle\Form\ArtistType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\ContractFan;

class FanController extends Controller
{
    /**
     * @Route("/home", name="fan_home")
     */
    public function homeAction(Request $request, UserInterface $user)
    {
        $em = $this->getDoctrine()->getManager();

        // Need a more personalised strategy (see algorithms)
        $currentContracts = $em->getRepository('AppBundle:ContractArtist')->findCurrents();

        return $this->render('@App/Fan/fan_home.html.twig', array(
            'currentContracts' => $currentContracts,
        ));
    }

    /**
     * @Route("/see-contract-{id}", name="fan_see_contract")
     */
    public function seeContractAction(ContractArtist $contract) {

        $current = new \DateTime();
        $done = $contract->getDateEnd() < $current;

        return $this->render('@App/Fan/artist_contract.html.twig', array(
            'contract' => $contract,
            'done' => $done,
        ));
    }

    /**
     * @Route("/artist-profile-{id}", name="fan_artist_profile")
     */
    public function seeArtistProfileAction(Request $request, UserInterface $user, Artist $artist) {
        return $this->render('@App/Fan/artist_profile.html.twig', array(
            'artist' => $artist,
        ));
    }

    /**
     * @Route("/artists", name="fan_artists")
     */
    public function artistsAction(Request $request, UserInterface $user) {
        $em = $this->getDoctrine()->getManager();

        // TODO algorithm
        $artists = $em->getRepository('AppBundle:Artist')->findAll();

        return $this->render('@App/Fan/artists.html.twig', array(

        ));
    }

    /**
     * @Route("/artist-contracts", name="fan_artist_contracts")
     */
    public function artistContractsAction() {

        $em = $this->getDoctrine()->getManager();
        $contracts = $em->getRepository('AppBundle:ContractArtist')->findCurrents();

        return $this->render('@App/Fan/artist_contracts.html.twig', array(
            'contracts' => $contracts,
        ));
    }

    /**
     * @Route("/cart", name="fan_cart")
     */
    public function cartAction(UserInterface $fan) {

        $em = $this->getDoctrine()->getManager();
        $cart =  $em->getRepository('AppBundle:Cart')->findCurrentForFan($fan);

        if($cart == null) {
            $cart = new Cart();
            $cart->setUser($fan);
            $em->persist($cart);
            $em->flush();
        }



        return $this->render('@App/Fan/cart.html.twig', array(
            'cart' => $cart,
        ));
    }

    /**
     * @Route("/paid-carts", name="fan_paid_carts")
     */
    public function paidCartsAction(UserInterface $fan) {
        $em = $this->getDoctrine()->getManager();
        $carts = $em->getRepository('AppBundle:Cart')->findBy(array('user' => $fan, 'confirmed' => true));

        return $this->render('@App/Fan/paid_carts.html.twig', array(
            'carts' => $carts,
        ));
    }

    /**
     * @Route("/special-advantages", name="fan_special_advantages")
     */
    public function specialAdvantagesAction() {

        $em = $this->getDoctrine()->getManager();
        $sa = $em->getRepository('AppBundle:SpecialAdvantage')->findCurrents();

        return $this->render('@App/Fan/special_advantages.html.twig', array(
            'advantages' => $sa,
        ));
    }

    /**
     * @Route("/special-purchases", name="fan_special_purchases")
     */
    public function specialPurchasesAction(UserInterface $fan) {

        $em = $this->getDoctrine()->getManager();
        $sp = $em->getRepository('AppBundle:SpecialPurchase')->findBy(array('user' => $fan), array('date' => 'DESC'));

        return $this->render('@App/Fan/special_purchases.html.twig', array(
            'purchases' => $sp,
        ));
    }

    /**
     * @Route("/my-bands", name="fan_my_artists")
     */
    public function myArtistsAction(UserInterface $fan) {

        $em = $this->getDoctrine()->getManager();

        $artists_user = $fan->getArtistsUser();

        return $this->render('@App/Fan/my_artists.html.twig', array(
            'artists_user' => $artists_user,
        ));
    }

    /**
     * @Route("/new-artist", name="fan_new_artist")
     */
    public function newArtistAction(Request $request, UserInterface $fan) {

        $em = $this->getDoctrine()->getManager();

        $phase = $em->getRepository('AppBundle:Phase')->findOneBy(array('num' => 1));

        $artist = new Artist($phase);

        $form = $this->createForm(ArtistType::class, $artist);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($artist);

            $au = new Artist_User();
            $au->setUser($fan)->setArtist($artist);
            $em->persist($au);

            $em->flush();

            $this->addFlash('notice', "Bien reçu");

            return $this->redirectToRoute('fan_artists');
        }

        return $this->render('@App/Fan/new_artist.html.twig', array(
            'form' => $form->createView(),
        ));
    }

    // AJAX ----------------------------------------------------------------------------------------------------------------------

    /**
     * @Route("/api/add-to-cart", name="fan_ajax_add_to_cart")
     */
    public function addToCartAction(Request $request) {

        $id_counterpart = $request->get('id_counterpart');
        $id_contract_artist = $request->get('id_contract_artist');
        $quantity = $request->get('quantity');

        $fan = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $counterpart = $em->getRepository('AppBundle:CounterPart')->find($id_counterpart);
        $contractArtist = $em->getRepository('AppBundle:ContractArtist')->find($id_contract_artist);

        if($contractArtist->getNbAvailable($counterpart) < $quantity) {
            return new Response("MAX_QTY");
        }

        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForFan($fan);

        if($cart == null) {
            $cart = new Cart();
            $cart->setUser($fan);
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
     * @Route("/api/remove-all-from-cart", name="fan_ajax_remove_all_from_cart")
     */
    public function removeAllFromCartAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $fan = $this->getUser();

        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForFan($fan);

        if($cart == null) {
            $cart = new Cart();
            $cart->setUser($fan);

            $em->persist($cart);
            $em->flush();
            return new Response("OK");
        }

        foreach($cart->getContracts() as $contract) {
            $em->remove($contract);
        }
        $em->flush();

        return new Response($this->renderView('@App/Fan/cart_content.html.twig', array(
            'cart' => $cart,
        )));
    }

    /**
     * @Route("/api/remove-purchase-from-contract", name="fan_ajax_remove_from_contract")
     */
    public function removeFromContractAction(Request $request) {
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

        return new Response($this->renderView('@App/Fan/cart_content.html.twig', array(
            'cart' => $cart,
        )));
    }

    /**
     * @Route("/api/deblock-advantage", name="fan_ajax_deblock_advantage")
     */
    public function deblockAdvantageAction(Request $request, UserInterface $fan) {
        $em = $this->getDoctrine()->getManager();

        $id_advantage = intval($request->get('id_advantage'));
        $quantity = intval($request->get('quantity'));

        $adv = $em->getRepository('AppBundle:SpecialAdvantage')->find($id_advantage);

        $purchase = new SpecialPurchase();
        $purchase->setUser($fan)
            ->setQuantity($quantity)
            ->setSpecialAdvantage($adv);

        if($fan->getCredits() < $purchase->getAmountCredits()) {
            return new Response("NOT_ENOUGH_CREDITS");
        }

        $fan->removeCredits($purchase->getAmountCredits());

        $em->persist($fan);
        $em->persist($purchase);
        $em->flush();

        return new Response($fan->getCredits());
    }

}
