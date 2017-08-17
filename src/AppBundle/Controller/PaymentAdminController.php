<?php

namespace AppBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class PaymentAdminController extends Controller
{
    public function refundAction() {

        $em = $this->getDoctrine()->getManager();
        $payment = $this->admin->getSubject();

        if (!$payment) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $payment->getId()));
        }

        \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));

        \Stripe\Refund::create(array(
            "charge" => $payment->getChargeId(),
        ));

        $payment->setRefunded(true);
        $em->persist($payment);
        $em->flush();

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
