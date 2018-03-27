<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Routing\Annotation\Route;

class WebHooksController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @Route("/stripe-refund", name="wh_stripe_refund")
     */
    public function stripeRefundAction()
    {
        $em = $this->getDoctrine()->getManager();

        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));

        // You can find your endpoint's secret in your webhook settings
        $endpoint_secret = "whsec_lLtpkc1tsO9U18ELntV6EQiUFc9jEEHg";

        $payload = @file_get_contents("php://input");
        $sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            http_response_code(400); // PHP 5.4 or greater
            exit();
        } catch(\Stripe\Error\SignatureVerification $e) {
            // Invalid signature
            http_response_code(400); // PHP 5.4 or greater
            exit();
        }

        $charge_id = $event->data->object->id;
        $payment = $em->getRepository('AppBundle:Payment')->findOneBy(['chargeId' => $charge_id]);

        // TODO SOA
        $payment->setRefunded(true);
        $payment->getContractFan()->setRefunded(true);

        $em->persist($payment);
        $em->persist($payment->getContractFan());

        $em->flush();

        http_response_code(200); // PHP 5.4 or greater
    }
}
