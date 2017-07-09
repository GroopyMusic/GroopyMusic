<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use AppBundle\Entity\Payment;
use Symfony\Component\Security\Core\User\UserInterface;

class AdminController extends Controller
{
    /**
     * @Route("/payments", name="admin_payments")
     */
    public function paymentsAction()
    {
        $em = $this->getDoctrine()->getManager();
        $contracts = $em->getRepository('AppBundle:ContractArtist')->findAll();
        $payments = $em->getRepository('AppBundle:Payment')->findBy([], ['date' => 'desc']);

        return $this->render('@App/Admin/payments.html.twig', array(
            'contracts' => $contracts,
            'payments' => $payments,
        ));
    }

    /**
     * @Route("/contract-artist-{id}", name="admin_contract_artist")
     */
    public function contractArtistAction(ContractArtist $ca)
    {
        $payments = $ca->getPayments();

        return $this->render('@App/Admin/contract_artist.html.twig', array(
            'contract' => $ca,
            'payments' => $payments,
        ));
    }


    /* AJAX */
    /**
     * @Route("/api/refund-payment", name="admin_ajax_refund_payment")
     */
    public function refundPaymentAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $payment_id = $request->get('payment_id');
        $payment = $em->getRepository('AppBundle:Payment')->find($payment_id);

        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey("sk_test_b75odA2dm9Og4grQZyFdn9HP");

        \Stripe\Refund::create(array(
            "charge" => $payment->getChargeId(),
        ));

        $payment->setRefunded(true);
        $em->persist($payment);
        $em->flush();

        $payments = $em->getRepository('AppBundle:Payment')->findBy([], ['date' => 'desc']);
        return new Response($this->renderView('@App/Admin/tab_payments.html.twig', array(
            'payments' => $payments,
        )));
    }

    /**
     * @Route("/api/refund-contract", name="admin_ajax_refund_contract")
     */
    public function refundContractAction(Request $request) {
        $em = $this->getDoctrine()->getManager();

        $contract_id = $request->get('contract_id');
        $contract = $em->getRepository('AppBundle:ContractArtist')->find($contract_id);

        $payments = $contract->getPayments();

        foreach($payments as $payment) {
            if(!$payment->getRefunded()) {
                // Set your secret key: remember to change this to your live secret key in production
                // See your keys here: https://dashboard.stripe.com/account/apikeys
                \Stripe\Stripe::setApiKey("sk_test_b75odA2dm9Og4grQZyFdn9HP");

                \Stripe\Refund::create(array(
                    "charge" => $payment->getChargeId(),
                ));
                $payment->setRefunded(true);
                $em->persist($payment);
            }
        }

        $em->flush();

        return new Response($this->renderView('@App/Admin/tab_payments.html.twig', array(
            'payments' => $payments,
        )));
    }
}
