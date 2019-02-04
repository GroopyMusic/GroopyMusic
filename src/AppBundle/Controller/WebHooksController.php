<?php

namespace AppBundle\Controller;

use AppBundle\Services\PaymentManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WebHooksController extends BaseController
{
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
        $endpoint_secret = "whsec_gvp510gYzBHMgUkqgUM0xsW6Ty55c3zb";

        $payload = @file_get_contents("php://input");
        $sig_header = $_SERVER["HTTP_STRIPE_SIGNATURE"];
        $event = null;

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload, $sig_header, $endpoint_secret
            );
        } catch(\UnexpectedValueException $e) {
            // Invalid payload
            throw $this->createAccessDeniedException();
        } catch(\Stripe\Error\SignatureVerification $e) {
            // Invalid signature
            throw $this->createAccessDeniedException();
        }

        $charge_id = $event->data->object->id;
        $payment = $em->getRepository('AppBundle:Payment')->findOneBy(['chargeId' => $charge_id]);

        if($payment != null) {
            $this->get(PaymentManager::class)->refundUMPayment($payment);
        }
        return new Response('OK');
    }
}
