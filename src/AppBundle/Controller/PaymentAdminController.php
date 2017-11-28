<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Payment;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class PaymentAdminController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function refundAction(Request $request, UserInterface $user) {

        $em = $this->getDoctrine()->getManager();

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
                'attr' => array('class' => 'btn btn-warning')
            ))
            ->add('cancel', SubmitType::class, array(
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
                    \Stripe\Stripe::setApiKey($this->getParameter('stripe_api_secret'));

                    \Stripe\Refund::create(array(
                        "charge" => $payment->getChargeId(),
                    ));

                    $payment->setRefunded(true);

                    // Concert
                    if($payment->getContractArtist() instanceof ContractArtist) {
                        $payment->getContractArtist()->removeTicketsSold($payment->getContractFan()->getCounterPartsQuantity());
                    }

                    $message = 'Paiement remboursé !';
                }

                $em->persist($payment);
                $em->flush();

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
