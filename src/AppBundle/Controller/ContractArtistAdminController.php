<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ConcertPossibility;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Payment;
use AppBundle\Form\ContractArtistPreValidationType;
use AppBundle\Form\ContractArtistSendTicketsType;
use AppBundle\Form\ContractArtistValidationType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PDFWriter;
use AppBundle\Services\TicketingManager;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Entity\ContractFan;

class ContractArtistAdminController extends Controller
{
    protected $container;
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->configure();
    }

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

                    $payments = $contract->getPayments();

                    foreach ($payments as $payment) {
                        /** @var Payment $payment */
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
                    $contract->setRefunded(true)->setFailed(true);
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

                $contract->setSuccessful(true)->setFailed(false);

                foreach($contract->getContractsFanPaid() as $cf) {
                    /** @var ContractFan $cf */
                    $cf_user = $cf->getUser();
                    $cf_user->addCredits($cf->getAmount());
                    $em->persist($cf_user);
                }

                $this->get(MailDispatcher::class)->sendKnownOutcomeContract($contract, true);

                $em->persist($contract);
                $em->flush();

                $this->addFlash('sonata_flash_success', "Cet événement est désormais confirmé. Les ventes de tickets vont continuer jusqu'au sold out ou jusqu'à quelques jours avant l'événement.");

                return new RedirectResponse($this->admin->generateUrl('list'));
            } elseif ($form->get('markfailed')->isClicked()) {

                $contract->setSuccessful(false)->setFailed(true)->setReality(null);

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


   /* public function editAction($id = null)
    {
        $request = $this->getRequest();
        $em = $this->getDoctrine()->getManager();
        // the key used to lookup the template
        $templateKey = 'edit';

        $id = $request->get($this->admin->getIdParameter());
        $existingObject = $this->admin->getObject($id);

        if (!$existingObject) {
            throw $this->createNotFoundException(sprintf('unable to find the object with id : %s', $id));
        }

        $this->admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if ($preResponse !== null) {
            return $preResponse;
        }

        $this->admin->setSubject($existingObject);

        $form = $this->admin->getForm();
        $form->add('edit_and_mail_reality', SubmitType::class, array(
            'attr' => array('class' => 'btn btn-warning'),
            'label' => 'Mettre à jour et envoyer un mail aux fans avec la "réalité"',
        ))
            ->add('edit_and_mail_coartists', SubmitType::class, array(
                'attr' => array('class' => 'btn btn-warning'),
                'label' => 'Mettre à jour et envoyer un mail aux fans avec la (les) première(s) parties pas encore annoncée(s)',
            ));
        $form->setData($existingObject);
        $form->handleRequest($request);


        if ($form->isSubmitted()) {
            //TODO: remove this check for 4.0
            if (method_exists($this->admin, 'preValidate')) {
                $this->admin->preValidate($existingObject);
            }
            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $submittedObject = $form->getData();
                $this->admin->setSubject($submittedObject);

                $recipients_fan = $submittedObject->getFanProfiles();
                $recipients_artist = $submittedObject->getArtistProfiles();

                if($form->get('edit_and_mail_reality')->isClicked()) {

                    if($submittedObject->getReality() != null) {
                        $reality = $submittedObject->getReality();

                        if(count($recipients_fan) > 0) {
                            // TODO send mail fan
                            $this->addFlash('sonata_flash_success', 'Mail envoyé aux fans');
                        }
                        if(count($recipients_artist) > 0) {
                            // TODO send mail artist
                            $this->addFlash('sonata_flash_success', 'Mail envoyé aux artistes');
                        }
                    }
                    else {
                        $this->addFlash('sonata_flash_warning', 'Pas de mail envoyé parce que pas d\'infos données');
                    }
                }
                
                elseif($form->get('edit_and_mail_coartists')->isClicked()) {

                    $coartists = array();
                    foreach($submittedObject->getCoartists_list() as $caa) {
                        if(!$caa->getAnnounced()) {
                            $coartists[] = $caa->getArtist();
                            $caa->setAnnounced(true);
                            $em->persist($caa);
                        }
                    }

                    if(count($coartists) > 0) {
                        if(count($recipients_fan) > 0) {
                            // TODO send mail fan
                            $this->addFlash('sonata_flash_success', 'Mail envoyé aux fans');
                        }
                        if(count($recipients_artist) > 0) { // must be true...
                            // TODO send mail artist
                            $this->addFlash('sonata_flash_success', 'Mail envoyé aux artistes');
                        }
                    }

                    else {
                        $this->addFlash('sonata_flash_warning', 'Pas de mail envoyé');
                    }
                }

                $em->flush();

                try {
                    $existingObject = $this->admin->update($submittedObject);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($existingObject),
                            'objectName' => $this->escapeHtml($this->admin->toString($existingObject)),
                        ), 200, array());
                    }

                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans(
                            'flash_edit_success',
                            array('%name%' => $this->escapeHtml($this->admin->toString($existingObject))),
                            'SonataAdminBundle'
                        )
                    );

                    // redirect to edit mode
                    return $this->redirectTo($existingObject);
                } catch (ModelManagerException $e) {
                    $this->handleModelManagerException($e);

                    $isFormValid = false;
                } catch (LockException $e) {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_lock_error', array(
                        '%name%' => $this->escapeHtml($this->admin->toString($existingObject)),
                        '%link_start%' => '<a href="' . $this->admin->generateObjectUrl('edit', $existingObject) . '">',
                        '%link_end%' => '</a>',
                    ), 'SonataAdminBundle'));
                }
            }

            // show an error message if the form failed validation
            if (!$isFormValid) {
                if (!$this->isXmlHttpRequest()) {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans(
                            'flash_edit_error',
                            array('%name%' => $this->escapeHtml($this->admin->toString($existingObject))),
                            'SonataAdminBundle'
                        )
                    );
                }
            } elseif ($this->isPreviewRequested()) {
                // enable the preview template if the form was valid and preview was requested
                $templateKey = 'preview';
                $this->admin->getShow();
            }
        }

        $formView = $form->createView();
        // set the theme for the current Admin Form
        $theme = $this->admin->getFormTheme();

        $twig = $this->get('twig');

        try {
            $twig
                ->getRuntime('Symfony\Bridge\Twig\Form\TwigRenderer')
                ->setTheme($formView, $theme);
        } catch (\Twig_Error_Runtime $e) {
            // BC for Symfony < 3.2 where this runtime not exists
            $twig
                ->getExtension('Symfony\Bridge\Twig\Extension\FormExtension')
                ->renderer
                ->setTheme($formView, $theme);
        }

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form' => $formView,
            'object' => $existingObject,
        ), null);
    }*/

}
