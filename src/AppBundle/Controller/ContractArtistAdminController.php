<?php

namespace AppBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ContractArtistAdminController extends Controller
{
    public function refundAction() {
        $em = $this->getDoctrine()->getManager();
        $contract = $this->admin->getSubject();

        if (!$contract) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $contract->getId()));
        }

        $payments = $contract->getPayments();

        foreach($payments as $payment) {
            if(!$payment->getRefunded()) {
                \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));

                \Stripe\Refund::create(array(
                    "charge" => $payment->getChargeId(),
                ));
                $payment->setRefunded(true);
                $em->persist($payment);
            }
        }

        $contract->setRefunded(true);

        $em->flush();

        return new RedirectResponse($this->admin->generateUrl('list'));
    }
}
