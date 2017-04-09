<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Payment;
use AppBundle\Entity\Purchase;
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
            $cart->setFan($fan);
            $em->persist($cart);
            $em->flush();
        }

        return $this->render('@App/Fan/cart.html.twig', array(
            'cart' => $cart,
        ));
    }

    /**
     * @Route("/cart/payment", name="fan_cart_payment")
     */
    public function payCartAction(Request $request, UserInterface $fan) {
        $em = $this->getDoctrine()->getManager();
        $cart =  $em->getRepository('AppBundle:Cart')->findCurrentForFan($fan);

        $payment = new Payment();
        $form = $this->createFormBuilder($payment);
        $form->add('accept_conditions', CheckboxType::class, array('required' => true))
            ->add('submit', SubmitType::class, array());

        $form = $form->getForm();

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $cart->setConfirmed(true);

            $em->persist($cart);
            $em->flush();

            $this->addFlash('notice', 'Bien reçu');
            return $this->redirectToRoute('fan_home');
        }

        if($cart == null || count($cart->getContracts()) == 0) {
            throw $this->createAccessDeniedException("Pas de panier, pas de paiement !");
        }

        return $this->render('@App/Fan/pay_cart.html.twig', array(
            'cart' => $cart,
            'form' => $form->createView(),
        ));
    }

    /**
     * @Route("/paid-carts", name="fan_paid_carts")
     */
    public function paidCartsAction(UserInterface $fan) {
        $em = $this->getDoctrine()->getManager();
        $carts = $em->getRepository('AppBundle:Cart')->findBy(array('fan' => $fan, 'confirmed' => true));

        return $this->render('@App/Fan/paid_carts.html.twig', array(
            'carts' => $carts,
        ));
    }

    // AJAX

    /**
     * @Route("/add-to-cart", name="fan_ajax_add_to_cart")
     */
    public function addToCartAction(Request $request) {

        $id_counterpart = $request->get('id_counterpart');
        $id_contract_artist = $request->get('id_contract_artist');
        $quantity = $request->get('quantity');

        $fan = $this->getUser();

        $em = $this->getDoctrine()->getManager();
        $counterpart = $em->getRepository('AppBundle:CounterPart')->find($id_counterpart);
        $contractArtist = $em->getRepository('AppBundle:ContractArtist')->find($id_contract_artist);

        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForFan($fan);

        if($cart == null) {
            $cart = new Cart();
            $cart->setFan($fan);
        }

        $fanContracts = $cart->getContracts();
        foreach($fanContracts as $fc) {
            if($fc->getContractArtist()->getId() == $id_contract_artist) {
                $contract = $fc;
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
            }
        }

        if(!isset($purchase)) {
            $purchase = new Purchase();
            $purchase->setCounterpart($counterpart);
            $purchase->setContractFan($contract);
        }

        $purchase->addQuantity($quantity);

        $em->persist($contract);
        $em->persist($purchase);
        $em->persist($cart);

        $em->flush();

        return new Response("OK");
    }

    /**
     * @Route("/remove-all-from-cart", name="fan_ajax_remove_all_from_cart")
     */
    public function removeAllFromCartAction(Request $request) {

        $em = $this->getDoctrine()->getManager();

        $fan = $this->getUser();

        $cart = $em->getRepository('AppBundle:Cart')->findCurrentForFan($fan);

        if($cart == null) {
            $cart = new Cart();
            $cart->setFan($fan);

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
     * @Route("/remove-purchase-from-contract", name="fan_ajax_remove_from_contract")
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

}
