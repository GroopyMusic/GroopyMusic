<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Payment;
use AppBundle\Services\PaymentManager;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class PaymentAdminController extends BaseAdminController
{
    public function refundAction(Request $request, UserInterface $user) {

        /** @var Payment $payment */
        $payment = $this->admin->getSubject();

        if (!$payment) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $payment->getId()));
        }

        if($payment->isAskedRefundBy($user)) {
            $this->addFlash('sonata_flash_error', 'Tu as déjà demandé à rembourser ce paiement');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, array(
                'label' => 'Rembourser',
                'attr' => array('class' => 'btn btn-warning')
            ))
            ->add('cancel', SubmitType::class, array(
                'label' => 'Annuler',
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if($form->get('cancel')->isClicked()) {
                return new RedirectResponse($this->admin->generateUrl('list'));
            }

            elseif($form->get('confirm')->isClicked()) {

                $payment->addAskingRefund($user);

                $message = 'Demande validée';

                if($payment->isRefundReady()) {
                    $this->get(PaymentManager::class)->refundStripeAndUMPayment($payment);
                    $message = 'Paiement remboursé !';
                }

                $this->addFlash('sonata_flash_success', $message);

                return new RedirectResponse($this->admin->generateUrl('list'));
            }
        }

        return $this->render('@App/Admin/Payment/action_refund.html.twig', array(
            'form' => $form->createView(),
            'payment' => $payment,
        ));
    }
}
