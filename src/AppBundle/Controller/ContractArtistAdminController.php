<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractArtist;
use AppBundle\Form\ContractArtistPreValidationType;
use AppBundle\Form\ContractArtistValidationType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PaymentManager;
use AppBundle\Services\TicketingManager;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;

class ContractArtistAdminController extends BaseAdminController
{
    public function refundAction(Request $request, UserInterface $user) {
        $em = $this->getDoctrine()->getManager();
        $contract = $this->admin->getSubject();

        if (!$contract) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $contract->getId()));
        }

        /** @var ContractArtist $contract */
        if($contract->isAskedRefundBy($user)) {
            $this->addFlash('sonata_flash_error', 'Tu as déjà demandé à rembourser ce festival');

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

            if ($form->get('cancel')->isClicked()) {
                return new RedirectResponse($this->admin->generateUrl('list'));
            } elseif ($form->get('confirm')->isClicked()) {

                $contract->addAskingRefund($user);

                $message = 'Demande validée';

                if ($contract->isRefundReady()) {
                    $contract->setRefunded(true)->setFailed(true);
                    $this->get(PaymentManager::class)->refundStripeAndUMContractArtist($contract);

                    $message = 'Les paiements ont été remboursés !';
                }

                $em->persist($contract);
                $em->flush();

                $this->addFlash('sonata_flash_success', $message);

                return new RedirectResponse($this->admin->generateUrl('list'));
            }
        }

        return $this->render('@App/Admin/ContractArtist/action_refund.html.twig', array(
            'form' => $form->createView(),
            'contract' => $contract,
        ));
    }

    public function preValidateAction(Request $request, UserInterface $user) {
        $em = $this->getDoctrine()->getManager();
        /** @var ContractArtist $contract */
        $contract = $this->admin->getSubject();

        if (!$contract) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $contract->getId()));
        }

        if(!$contract->isInTestPeriod()) {
            throw new NotFoundHttpException('this contract is not in test period...');
        }

        $form = $this->createForm(ContractArtistPreValidationType::class, $contract);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if ($form->get('cancel')->isClicked()) {
                return new RedirectResponse($this->admin->generateUrl('list'));
            }

            elseif($form->get('markfailed')->isClicked()) {
                $contract->setSuccessful(false)->setFailed(true)->setReality(null)->setDateEnd(new \DateTime())->setStartDate(new \DateTime())->setTestPeriod(false);

                $this->get(MailDispatcher::class)->sendKnownOutcomeContract($contract, false);

                $em->persist($contract);
                $em->flush();

                $this->addFlash('sonata_flash_success', "L'objectif de cet événement n'a pas été atteint. Les ventes de tickets sont finies ; n'oublie pas de rembourser les fans !");

                return new RedirectResponse($this->admin->generateUrl('list'));
            }

            elseif ($form->get('marksuccessful')->isClicked()) {

                $contract->endTestPeriod();

                $em->persist($contract);
                $em->flush();

                $this->addFlash('sonata_flash_success', "L'événement a bien été modifié.");

                return new RedirectResponse($this->admin->generateUrl('list'));
            }
        }

        return $this->render('@App/Admin/ContractArtist/action_prevalidate.html.twig', array(
            'form' => $form->createView(),
            'contract' => $contract,
        ));
    }


    public function validateAction(Request $request, UserInterface $user) {
        $em = $this->getDoctrine()->getManager();
        $contract = $this->admin->getSubject();

        /** @var ContractArtist $contract */
        if (!$contract) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $contract->getId()));
        }

        $form = $this->createForm(ContractArtistValidationType::class, $contract);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if ($form->get('marksuccessful')->isClicked()) {

                // TODO service
                $contract->setSuccessful(true)->setFailed(false)->setDateSuccess(new \DateTime());

                $this->get(MailDispatcher::class)->sendKnownOutcomeContract($contract, true);

                $em->persist($contract);
                $em->flush();

                $this->addFlash('sonata_flash_success', "Cet événement est désormais confirmé. Les ventes de tickets vont continuer jusqu'au sold out ou jusqu'à quelques jours avant l'événement.");

                return new RedirectResponse($this->admin->generateUrl('list'));
            } elseif ($form->get('markfailed')->isClicked()) {

                $contract->setSuccessful(false)->setFailed(true)->setDateSuccess(null);

                $this->get(MailDispatcher::class)->sendKnownOutcomeContract($contract, false);

                $em->persist($contract);
                $em->flush();

                $this->addFlash('sonata_flash_success', "L'objectif de cet événement n'a pas été atteint. Les ventes de tickets sont finies ; n'oublie pas de rembourser les fans !");

                return new RedirectResponse($this->admin->generateUrl('list'));
            }
        }

        return $this->render('@App/Admin/ContractArtist/action_validate.html.twig', array(
            'form' => $form->createView(),
            'contract' => $contract,
        ));
    }

    public function ticketsAction(Request $request, UserInterface $user) {
        $em = $this->getDoctrine()->getManager();
        $contract = $this->admin->getSubject();

        /** @var ContractArtist $contract */
        if (!$contract) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $contract->getId()));
        }

        $form = $this->createForm(ContractArtistSendTicketsType::class, $contract);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            if ($form->get('send')->isClicked()) {
                // Marks the contract so that we later will automatically send the tickets

                if(null === $tickets_send_result = $this->get(TicketingManager::class)->sendUnSentTicketsForContractArtist($contract)) {
                    $contract->setTicketsSent(true);

                    $em->persist($contract);
                    $em->flush();

                    $this->addFlash('sonata_flash_success', "Les tickets pour cet événement vont être envoyés.");

                    return new RedirectResponse($this->admin->generateUrl('list'));
                }
                else {
                    $form->addError(new FormError('Une erreur est survenue lors de la génération des tickets.'));
                }
            }

            elseif($form->get('preview')->isClicked()) {
                $this->get(TicketingManager::class)->getTicketPreview($contract, $user);
            }

            // cancel
            else {
                return new RedirectResponse($this->admin->generateUrl('list'));
            }
        }

        return $this->render('@App/Admin/ContractArtist/action_tickets.html.twig', array(
            'form' => $form->createView(),
            'contract' => $contract,
        ));
    }
}
