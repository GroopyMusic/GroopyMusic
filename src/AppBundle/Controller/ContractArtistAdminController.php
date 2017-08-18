<?php

namespace AppBundle\Controller;

use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class ContractArtistAdminController extends Controller
{
    public function refundAction(Request $request, UserInterface $user) {
        $em = $this->getDoctrine()->getManager();
        $contract = $this->admin->getSubject();

        if (!$contract) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $contract->getId()));
        }

        if($contract->isAskedRefundBy($user)) {
            $this->addFlash('sonata_flash_error', 'Tu as déjà demandé à rembourser ce crowdfunding');

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        $form = $this->createFormBuilder()
            ->add('confirm', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-warning')
            ))
            ->add('cancel', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-primary')
            ))
            ->getForm();

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if ($form->get('cancel')->isClicked()) {
                return new RedirectResponse($this->admin->generateUrl('list'));
            } elseif ($form->get('confirm')->isClicked()) {

                $contract->addAskingRefund($user);

                $message = 'Demande validée';

                if ($contract->isRefundReady()) {

                    $payments = $contract->getPayments();

                    foreach ($payments as $payment) {
                        if (!$payment->getRefunded()) {
                            \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));

                            \Stripe\Refund::create(array(
                                "charge" => $payment->getChargeId(),
                            ));
                            $payment->setRefunded(true);
                            $payment->setAskingRefund($contract->getAskingRefund());
                            $em->persist($payment);
                        }
                    }

                    $message = 'Crowdfunding remboursé !';
                    $contract->setRefunded(true);
                }

                $em->persist($contract);
                $em->flush();

                $this->addFlash('sonata_flash_success', $message);

                return new RedirectResponse($this->admin->generateUrl('list'));
            }
        }

        return $this->render('@App/Admin/action_refund_contractartist.html.twig', array(
            'form' => $form->createView(),
            'contract' => $contract,
        ));
    }
}
