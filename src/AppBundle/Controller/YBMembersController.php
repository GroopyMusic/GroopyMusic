<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Address;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\User;
use AppBundle\Entity\YB\Block;
use AppBundle\Entity\YB\CustomTicket;
use AppBundle\Entity\YB\Membership;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\OrganizationJoinRequest;
use AppBundle\Entity\YB\PublicTransportStation;
use AppBundle\Entity\YB\Venue;
use AppBundle\Entity\YB\VenueConfig;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\YBInvoice;
use AppBundle\Entity\YB\YBTransactionalMessage;
use AppBundle\Exception\YBAuthenticationException;
use AppBundle\Form\UserBankAccountType;
use AppBundle\Form\YB\BlockType;
use AppBundle\Form\YB\CustomTicketType;
use AppBundle\Form\YB\OrganizationType;
use AppBundle\Form\YB\VenueConfigType;
use AppBundle\Form\YB\VenueType;
use AppBundle\Form\YB\YBContractArtistCrowdType;
use AppBundle\Form\YB\YBContractArtistType;
use AppBundle\Form\YB\YBTransactionalMessageType;
use AppBundle\Services\AdminExcelCreator;
use AppBundle\Services\FinancialDataGenerator;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PaymentManager;
use AppBundle\Services\PDFWriter;
use AppBundle\Services\StringHelper;
use AppBundle\Services\TicketingManager;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Routing\Annotation\Route;

class YBMembersController extends BaseController
{

    /**
     * @Route("/dashboard", name="yb_members_dashboard")
     */
    public function dashboardAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if ($currentUser->isSuperAdmin()) {
            $current_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllOnGoingEvents();
            $passed_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllPastEvents();
        } else {
            $current_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getOnGoingEvents($user);
            $passed_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getPassedEvents($user);
        }
        return $this->render('@App/YB/Members/dashboard.html.twig', [
            'current_campaigns' => $current_campaigns,
            'passed_campaigns' => $passed_campaigns,
        ]);
    }

    /**
     * @Route("/organizations", name="yb_members_orgs")
     */
    public function viewOrganizationsAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);
        return $this->render('@App/YB/Members/orgs_list.html.twig');
    }

    /**
     * @Route("/campaign/new/", name="yb_members_campaign_new")
     */
    public function newCampaignAction(UserInterface $user = null, Request $request, EntityManagerInterface $em, MailDispatcher $mailDispatcher, YBContractArtistType $flow)
    {
        /** @var \AppBundle\Entity\User $user */
        $this->checkIfAuthorized($user);
        $campaign_id = $request->get('campaign_id');
        if (null !== $campaign_id) {
            $campaign = $em->getRepository('AppBundle:YB\YBContractArtist')->find(intval($campaign_id));
            $this->checkIfAuthorized($user, $campaign);
        } else {
            $campaign = new YBContractArtist();
        }
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if (!$currentUser->hasPrivateOrganization()) {
            $this->createPrivateOrganization($em, $currentUser);
        }
        $userOrganizations = $this->getOrganizationsFromUser($currentUser);
        $venues = $this->getActiveVenue($em, $currentUser);
        $generic_options = ['admin' => $user->isSuperAdmin(), 'creation' => true, 'userOrganizations' => $userOrganizations, 'venues' => $venues, 'em' => $em, 'user' => $currentUser];
        $flow->setGenericFormOptions($generic_options);
        $flow->setAllowRedirectAfterSubmit(true);
        $flow->bind($campaign);
        $form = $flow->createForm();
        if ($flow->isValid($form)) {
            $em->persist($campaign);
            $em->flush();
            $em->refresh($campaign);
            $flow->setGenericFormOptions($generic_options);
            $flow->saveCurrentStepData($form);
            if ($flow->nextStep()) {
                // form for the next step
                try {
                    $mailDispatcher->sendYBReminderEventCreated($campaign);
                } catch (\Exception $e) {
                }
                if ($flow->getCurrentStepNumber() == 2)
                    $this->addFlash('yb_notice', "La campagne a bien été créée. Pour qu'elle soit fonctionnelle, vous n'avez plus qu'à créer des tickets.");
                elseif ($flow->getCurrentStepNumber() == 3)
                    $this->addFlash('yb_notice', "Les tickets ont été créés. Vous pouvez maintenant nous donner vos infos de facturation, qui nous permettront de vous reverser le fruit de vos ventes. Si vous souhaitez vous occuper de cette étape plus tard, libre à vous..");
                // Redirecting to avoid multiple submissions
                $params = $this->get('craue_formflow_util')->addRouteParameters(array_merge(array_merge(['campaign_id' => $campaign->getId()], $request->query->all()),
                    $request->attributes->get('_route_params')), $flow);
                return $this->redirect($this->generateUrl($request->attributes->get('_route'), $params));
            } else {
                $flow->reset(); // remove step data from the session
                $this->addFlash('yb_notice', 'La campagne a bien été créée. Vous pouvez personnaliser vos tickets depuis votre dashboard !');
                return $this->redirectToRoute('yb_members_dashboard');
            }
        }
        return $this->render('@App/YB/Members/campaign_new.html.twig', [
            'admin' => $user->isSuperAdmin(),
            'form' => $form->createView(),
            'campaign' => $campaign,
            'flow' => $flow,
        ]);
    }

    /**
     * @Route("/campaign/{id}/update", name="yb_members_campaign_edit")
     */
    public function editCampaignAction(YBContractArtist $campaign, UserInterface $user = null, Request $request, EntityManagerInterface $em, YBContractArtistType $flow)
    {
        $this->checkIfAuthorized($user, $campaign);
        if (!$user->isSuperAdmin() && $campaign->isPassed()) {
            $this->addFlash('yb_error', 'Cette campagne est passée. Il est donc impossible de la modifier.');
            return $this->redirectToRoute('yb_members_passed_campaigns');
        }
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if (!$currentUser->hasPrivateOrganization()) {
            $this->createPrivateOrganization($em, $currentUser);
        }
        $userOrganizations = $this->getOrganizationsFromUser($currentUser);
        $venues = $this->getActiveVenue($em, $currentUser);
        $flow->setGenericFormOptions(['admin' => $user->isSuperAdmin(), 'creation' => false, 'userOrganizations' => $userOrganizations, 'campaign_id' => $campaign->getId(), 'venues' => $venues, 'em' => $em]);
        $flow->setAllowDynamicStepNavigation(true);
        $flow->setAllowRedirectAfterSubmit(true);
        $flow->bind($campaign);
        $form = $flow->createForm();
        //$form->handleRequest($request);
        if ($flow->isValid($form)) {
            $flow->saveCurrentStepData($form);
            $em->flush();
            if ($flow->nextStep()) {
                if ($flow->getCurrentStepNumber() == 2)
                    $this->addFlash('yb_notice', 'Les infos générales ont bien été modifiées.');
                elseif ($flow->getCurrentStepNumber() == 3)
                    $this->addFlash('yb_notice', 'Les tickets ont bien été modifiés.');
                if ($flow->redirectAfterSubmit($form)) {
                    $params = $this->get('craue_formflow_util')->addRouteParameters(array_merge($request->query->all(),
                        $request->attributes->get('_route_params')), $flow);
                    return $this->redirect($this->generateUrl($request->attributes->get('_route'), $params));
                } else {
                    $form = $flow->createForm();
                }
            } else {
                $flow->reset(); // remove step data from the session
                $this->addFlash('yb_notice', 'La campagne a bien été modifiée.');
                return $this->redirectToRoute('yb_members_dashboard');
            }
            $em->persist($campaign);
            $em->flush();
        }
        return $this->render('@App/YB/Members/campaign_new.html.twig', [
            'form' => $form->createView(),
            'flow' => $flow,
            'campaign' => $campaign,
        ]);
    }

    /**
     * @Route("/campaign/{id}/crowdfunding", name="yb_members_campaign_crowdfunding")
     */
    public function crowdfundingCampaignAction(YBContractArtist $campaign, UserInterface $user = null, Request $request, EntityManagerInterface $em, TicketingManager $ticketingManager, PaymentManager $paymentManager)
    {
        $this->checkIfAuthorized($user, $campaign);
        $form = $this->createForm(YBContractArtistCrowdType::class, $campaign);
        $form->handleRequest($request);
        if ($form->isSubmitted()) {
            if ($form->get('refund')->isClicked() && !$campaign->getRefunded()) {
                $campaign->setFailed(true);
                $paymentManager->refundStripeAndYBContractArtist($campaign);
                $em->flush();
                $this->addFlash('yb_notice', 'La campagne a bien été annulée. Les éventuels contributeurs ont été avertis et remboursés.');
                return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
            }
            if ($form->get('validate')->isClicked() && !$campaign->getTicketsSent()) {
                foreach ($campaign->getContractsFanPaid() as $cf) {
                    $ticketingManager->generateAndSendYBTickets($cf, true);
                }
                $campaign->setTicketsSent(true)->setSuccessful(true);
                $em->flush();
                $this->addFlash('yb_notice', "L'événement a bien été confirmé et les tickets envoyés aux différents acheteurs.");
                return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
            }
        }
        return $this->render('@App/YB/Members/campaign_crowdfunding.html.twig', [
            'form' => $form->createView(),
            'campaign' => $campaign,
        ]);
    }

    /**
     * @Route("/campaign/{id}/orders", name="yb_members_campaign_orders")
     */
    public function ordersCampaignAction(YBContractArtist $campaign, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user, $campaign);
        $cfs = array_reverse($campaign->getContractsFanPaid());
        return $this->render('@App/YB/Members/campaign_orders.html.twig', [
            'cfs' => $cfs,
            'campaign' => $campaign,
        ]);
    }

    /**
     * @Route("/campaign/{id}/transactional-message", name="yb_members_campaign_transactional_message")
     */
    public function transactionalMessageCampaignAction(YBContractArtist $campaign, Request $request, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user, $campaign);
        $message = new YBTransactionalMessage($campaign);
        $old_messages = $campaign->getTransactionalMessages();
        $form = $this->createForm(YBTransactionalMessageType::class, $message);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->em->persist($message);
            $this->em->flush();
            $this->mailDispatcher->sendYBTransactionalMessageWithCopy($message);
            $this->addFlash('yb_notice', 'Votre message a bien été envoyé.');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }
        return $this->render('@App/YB/Members/campaign_transactional_message.html.twig', [
            'form' => $form->createView(),
            'campaign' => $campaign,
            'old_messages' => $old_messages,
        ]);
    }

    /**
     * @Route("/invoices", name="yb_members_invoices")
     */
    public function invoicesViewListAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);
        /** @var \AppBundle\Repository\YB\YBContractArtistRepository $YBCARepository */
        $YBCARepository = $em->getRepository('AppBundle:YB\YBContractArtist');
        $all_campaigns = $YBCARepository->getAllYBCampaigns($user);
        return $this->render('@App/YB/Members/invoices.html.twig', [
            'admin' => $user->isSuperAdmin(),
            'campaigns' => $all_campaigns,
        ]);
    }

    /**
     * @Route("/invoice/{id}/generate", name="yb_members_invoice_generate")
     */
    public function invoiceGenerateAction(YBContractArtist $campaign, EntityManagerInterface $em, UserInterface $user = null)
    {
        //$this->checkIfAuthorized($user);
        if ($user == null || !$user->isSuperAdmin()) {
            throw new YBAuthenticationException();
        }
        if (!$campaign->isFacturable()) {
            throw $this->createNotFoundException();
        }
        $invoice = new YBInvoice();
        $invoice->setCampaign($campaign);
        $em->persist($invoice);
        $cfs = $campaign->getContractsFanPaid();
        /** @var ContractFan $cf */
        foreach ($cfs as $cf) {
            /** @var Purchase $purchase */
            foreach ($cf->getPurchases() as $purchase) {
                if ($cf->getInvoice() === null) {
                    $cf->setInvoice($invoice);
                    $em->persist($cf);
                }
            }
        }
        $em->flush();
        $this->mailDispatcher->sendYBInvoiceGenerated($invoice);
        $this->addFlash('yb_notice', 'La facture a bien été générée. Les organisateurs ont été prévenus par mail qu\'ils étaient priés de la valider.');
        return $this->redirectToRoute("yb_members_invoices");
    }

    /**
     * @Route("/invoice/{id}/validate", name="yb_members_invoice_validate")
     */
    public function invoiceValidateAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user = null, MailDispatcher $mailDispatcher)
    {
        $this->checkIfAuthorized($user, $invoice->getCampaign());
        if ($invoice->isUserValidated()) {
            throw new YBAuthenticationException();
        }
        $invoice->validate();
        $em->persist($invoice);
        $em->flush();
        $this->addFlash('yb_notice', 'La facture a bien été validée, elle est dès à présent valable.');
        try {
            $mailDispatcher->sendAdminYBInvoiceValidated($invoice);
        } catch (\Exception $e) {
        }
        return $this->redirectToRoute("yb_members_invoices");
    }

    /**
     * @Route("/invoice/{id}/invalidate", name="yb_members_invoice_invalidate")
     */
    public function invoiceInvalidateAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user, $invoice->getCampaign(), true);
        $purchases = $invoice->getPurchases(); // Necessary for hydratation
        if (!$invoice->isUserValidated()) {
            $em->remove($invoice);
            $em->flush();
        }
        $this->addFlash('yb_notice', 'La facture a bien été supprimée.');
        return $this->redirectToRoute("yb_members_invoices");
    }

    /**
     * @Route("/invoice/{id}/sold/{format}", name="yb_members_invoice_sold")
     */
    public function invoiceSoldDetailsAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user, $format = 'pdf', PDFWriter $writer)
    {
        $campaign = $invoice->getCampaign();
        $this->checkIfAuthorized($user, $campaign);
        $purchases = $invoice->getPurchases();  // Necessary for hydratation
        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildFromInvoice($invoice);
        if (strtoupper($format) == 'HTML') {
            return $this->render('@App/PDF/yb_invoice_sold.html.twig', [
                'invoice' => $invoice,
                'ticketData' => $financialDataService->getTicketData(),
                'campaign' => $campaign,
                'cfs' => $invoice->getContractsFan()
            ]);
        } else {
            $writer->writeSoldInvoice($invoice, $financialDataService->getTicketData(), $campaign, $invoice->getContractsFan());
        }
    }

    /**
     * @Route("/invoice/{id}/fee", name="yb_members_invoice_fee")
     */
    public function invoiceFeeDetailsAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user)
    {
        $campaign = $invoice->getCampaign();
        if ($user == null || !$user->isSuperAdmin()) {
            $this->checkIfAuthorized($user, $campaign);
        }
        //$purchases = $invoice->getPurchases();
        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildFromInvoice($invoice);
        $counterparts = array_map(function ($purchase) {
            /** @var Purchase $purchase */
            return $purchase->getCounterpart();
        }, $invoice->getPurchases()->toArray());
        $cfs = array_map(function ($purchase) {
            /** @var Purchase $purchase */
            return $purchase->getContractFan();
        }, $invoice->getPurchases()->toArray());
        return $this->render('@App/PDF/yb_invoice_fee.html.twig', [
            'invoice' => $invoice,
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            //'counterparts' => $counterparts,
            //'cfs' => $cfs
        ]);
    }

    /**
     * @Route("/campaign/{id}/sold/{format}", name="yb_members_campaign_sold")
     */
    public function campaignSoldDetailsAction(YBContractArtist $campaign, UserInterface $user = null, $format = 'pdf', PDFWriter $writer)
    {
        if ($user == null || !$user->isSuperAdmin()) {
            throw new YBAuthenticationException();
        }
        if (!$campaign->isFacturable()) {
            throw $this->createNotFoundException();
        }
        $cfs = array_reverse($campaign->getContractsFanPaid());
        $cfs = array_filter($cfs, function ($cf) {
            /** @var ContractFan $cf */
            /** @var Purchase $purchase */
            $purchase = $cf->getPurchases()->first();
            return $purchase->getInvoice() == null;
        });
        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildInvoicelessCampaignData();
        if (strtoupper($format) == 'HTML') {
            return $this->render('@App/PDF/yb_invoice_sold.html.twig', [
                'invoice' => null,
                'ticketData' => $financialDataService->getTicketData(),
                'campaign' => $campaign,
                'cfs' => $cfs
            ]);
        } else {
            $writer->writeSoldInvoice(null, $financialDataService->getTicketData(), $campaign, $cfs);
        }
    }

    /**
     * @Route("/campaign/{id}/fee", name="yb_members_campaign_fee")
     */
    public function campaignFeeDetailsAction(YBContractArtist $campaign, UserInterface $user = null)
    {
        if ($user == null || !$user->isSuperAdmin()) {
            throw new YBAuthenticationException();
        }
        //$this->checkIfAuthorized($user, $campaign);
        $cfs = array_reverse($campaign->getContractsFanPaid());
        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildInvoicelessCampaignData();
        return $this->render('@App/PDF/yb_invoice_fee.html.twig', [
            'invoice' => null,
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            //'counterparts' => $campaign->getCounterparts()->toArray(),
            //'cfs' => $cfs,
        ]);
    }

    /**
     * @Route("/aide-facturation", name="yb_members_payment_options")
     */
    public function paymentOptionsAction(UserInterface $user = null, Request $request)
    {
        $this->checkIfAuthorized($user, null);
        $form = $this->createForm(UserBankAccountType::class, $user);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->addFlash('yb_notice', "Vos données de facturation ont bien été mises à jour.");
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }
        return $this->render('@App/YB/Members/payment_options.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/passed-campaigns", name="yb_members_passed_campaigns")
     */
    public function passedCampaignsAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);
        if ($user->isSuperAdmin()) {
            $passed_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllPastEvents();
        } else {
            $passed_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getPassedEvents($user);
        }
        return $this->render('@App/YB/Members/passed_campaigns.html.twig', [
            'campaigns' => $passed_campaigns,
        ]);
    }

    /**
     * @Route("/campaign/{id}/excel", name="yb_members_campaign_excel")
     */
    public function excelAction(YBContractArtist $campaign, UserInterface $user = null, StringHelper $strHelper)
    {
        $this->checkIfAuthorized($user, $campaign);
        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $phpExcelObject->getProperties()->setCreator("Ticked-it.be")
            ->setLastModifiedBy("Ticked-it robot")
            ->setTitle("Commandes et tickets")
            ->setSubject("Commandes")
            ->setDescription("Commandes et tickets")
            ->setKeywords("commandes, tickets");
        $cfs = array_reverse($campaign->getContractsFanPaid());
        if (count($cfs) > 0) {
            $colonnes = array(
                'Numéro de commande',
                'Code de confirmation',
                'Date de commande',
                'Acheteur',
                'Prix',
                'Détail',
                'URL de confirmation'
            );
            $lettre = "A";
            foreach ($colonnes as $colonne) {
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettre . '1', $colonne);
                $lettre++;
            }
            $chiffre = 2;
            foreach ($cfs as $cf) {
                /** @var ContractFan $cf */
                $lettre = "A";
                $colonnes = array(
                    $cf->getId(),
                    $cf->getCart()->getBarcodeText(),
                    \PHPExcel_Shared_Date::PHPToExcel($cf->getDate()->getTimeStamp()),
                    $cf->getDisplayName(),
                    $cf->getAmount(),
                    $cf->getPurchasesExport(),
                    $this->generateUrl('yb_order', ['code' => $cf->getCart()->getBarcodeText()], UrlGeneratorInterface::ABSOLUTE_URL),
                );
                foreach ($colonnes as $key => $colonne) {
                    $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettre . "" . $chiffre, $colonne);
                    // Date
                    if ($key == 2)
                        $phpExcelObject->getActiveSheet()
                            ->getStyle($lettre . "" . $chiffre)
                            ->getNumberFormat()
                            ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
                    $lettre++;
                }
                $chiffre++;
            }
        }
        $phpExcelObject->getActiveSheet()->setTitle('Commandes');
        if ($campaign->getTicketsSent()) {
            $phpExcelObject->createSheet();
            $colonnes = array(
                'Identifiant du ticket',
                'Numéro de la commande associée',
                'Code de confirmation',
                'Acheteur',
                'Prix',
                'Type de ticket',
            );
            $lettre = "A";
            foreach ($colonnes as $colonne) {
                $phpExcelObject->setActiveSheetIndex(1)->setCellValue($lettre . '1', $colonne);
                $lettre++;
            }
            $chiffre = 2;
            foreach ($cfs as $cf) {
                /** @var ContractFan $cf */
                foreach ($cf->getTickets() as $ticket) {
                    $lettre = "A";
                    /** @var Ticket $ticket */
                    $colonnes = array(
                        $ticket->getBarcodeText(),
                        $ticket->getContractFan()->getId(),
                        $ticket->getContractFan()->getCart()->getBarcodeText(),
                        $ticket->getName(),
                        $ticket->getPrice(),
                        $ticket->getCounterPart()->__toString(),
                    );
                    foreach ($colonnes as $key => $colonne) {
                        $phpExcelObject->setActiveSheetIndex(1)->setCellValue($lettre . "" . $chiffre, $colonne);
                        $lettre++;
                    }
                    $chiffre++;
                }
                $phpExcelObject->setActiveSheetIndex(1);
                $phpExcelObject->getActiveSheet()->setTitle('Tickets');
            }
        }
        // Set active sheet index to the first sheet, so Excel opens this as the first sheet
        $phpExcelObject->setActiveSheetIndex(0);
        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $strHelper->slugify($campaign->getTitle()) . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;
    }

    /**
     * @Route("/campaign/{id}/{code}remove-photo", name="yb_members_campaign_remove_photo")
     */
    public function removePhotoAction(Request $request, YBContractArtist $campaign, $code)
    {
        $this->checkCampaignCode($campaign, $code);
        $em = $this->getDoctrine()->getManager();
        $filename = $request->get('filename');
        $photo = $em->getRepository('AppBundle:Photo')->findOneBy(['filename' => $filename]);
        $em->remove($photo);
        $campaign->removeCampaignPhoto($photo);
        $filesystem = new Filesystem();
        $filesystem->remove($this->get('kernel')->getRootDir() . '/../web/' . YBContractArtist::getWebPath($photo));
        $em->persist($campaign);
        $em->flush();
        return new Response();
    }

    /**
     * @Route("admin/campaigns/excel", name="yb_admin_excel_all_campaigns")
     */
    public function adminExcelAllCampaigns(UserInterface $user = null)
    {
        //$this->checkIfAuthorized($user);
        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();
        $adminExcelCreator = new AdminExcelCreator($phpExcelObject);
        // create the writer
        $writer = $this->get('phpexcel')->createWriter($adminExcelCreator->renderExcel(), 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'commissions.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);
        return $response;
    }

    /**
     * @Route("/my-organizations", name="yb_members_my_organizations")
     */
    public function myOrganizationsAction(EntityManagerInterface $em, UserInterface $user = null, Request $request)
    {
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if ($currentUser->isSuperAdmin()) {
            $organizations = $em->getRepository('AppBundle:YB\Organization')->findAll();
            $organizationsToBeDisplayed = $this->fetchOrganizationsForSuperUser($currentUser, $organizations);
        } else {
            $organizationsToBeDisplayed = $currentUser->getPublicOrganizations();
        }
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $this->handleNewOrganizationRequest($organization, $currentUser, $em);
            return $this->redirectToRoute('yb_members_my_organizations');
        }
        return $this->render('@App/YB/Members/my_organizations.html.twig', [
            'organizations' => $organizationsToBeDisplayed,
            'form' => $form->createView(),
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * @Route("/quit-organization/{id}", name="yb_members_quit_organization")
     */
    public function quitOrganizationAction(Organization $org, UserInterface $user = null, EntityManagerInterface $em)
    {
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if ($org->hasOnlyOneMember()) {
            if (!$this->hasPendingProjects($org, $em)) {
                $em->remove($org);
            } else {
                $this->addFlash('error', 'Votre organisation a encore des projets et vous êtes le seul membre restant ! Vous ne pouvez pas quitter le navire en pleine mer !');
            }
        } else {
            if ($org->hasAtLeastOneAdminLeft($currentUser)) {
                $this->quitOrganization($em, $org, $currentUser);
            } else {
                $this->addFlash('error', 'Si vous partez, il n\'y a plus de maître à bord ! Désigner d\'abord un administrateur avant de partir !');
            }
        }
        $em->flush();
        return $this->redirectToRoute('yb_members_my_organizations');
    }

    /**
     * @Route("/add-to-organization/{id}", name="yb_members_add_to_organization")
     */
    public function addToOrganizationAction(Request $request, Organization $org, UserInterface $user = null, MailDispatcher $mailDispatcher, EntityManagerInterface $em)
    {
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        $form_add_user = $this->createFormBuilder()
            ->add('email_address', EmailType::class, ['label' => 'Adresse e-mail'])
            ->add('submit', SubmitType::class, ['label' => 'Ajouter'])
            ->getForm();
        $form_add_user->handleRequest($request);
        if ($form_add_user->isSubmitted() && $form_add_user->isValid()) {
            if ($this->handleAddUserToOrganization($form_add_user, $mailDispatcher, $org, $currentUser, $em)) {
                return $this->redirectToRoute('yb_members_my_organizations');
            }
        }
        return $this->render('@App/YB/Members/add_to_organizations.html.twig', [
            'organization' => $org,
            'form' => $form_add_user->createView(),
        ]);
    }

    /**
     * @Route("/remove-from-organization/{organization_id}/{user_id}/", name="yb_members_remove_from_organization")
     */
    public function removeFromOrganizationAction(Request $request, EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);
        $member = $em->getRepository('AppBundle:User')->find($request->get('user_id'));
        $organization = $em->getRepository('AppBundle:YB\Organization')->find($request->get('organization_id'));
        if (!in_array($organization, $member->getOrganizations())) {
            $this->addFlash('error', 'Cet utilisateur ne fait pas partie de cette organisation.');
        } else if (!in_array($member, $organization->getMembers())) {
            $this->addFlash('error', 'Cet utilisateur ne fait pas partie de cette organisation.');
        } else {
            $this->quitOrganization($em, $organization, $member);
        }
        $em->flush();
        return $this->redirectToRoute('yb_members_my_organizations');
    }

    /**
     * @Route("/make-admin/{organization_id}/{user_id}/", name="yb_members_make_admin")
     */
    public function makeAdminAction(Request $request, EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);
        $this->changeAdminRight($request, $em, true);
        return $this->redirectToRoute('yb_members_my_organizations');
    }

    /**
     * @Route("/unmake-admin/{organization_id}/{user_id}/", name="yb_members_unmake_admin")
     */
    public function unmakeAdminAction(Request $request, EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);
        $this->changeAdminRight($request, $em, false);
        return $this->redirectToRoute('yb_members_my_organizations');
    }

    /**
     * @Route("/confirm-joining-organization/{id}", name="yb_members_confirm_joining_organization")
     */
    public function confirmJoiningOrganization(Organization $org, UserInterface $user = null, EntityManagerInterface $em)
    {
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        $request = $em->getRepository('AppBundle:YB\OrganizationJoinRequest')->findByUserAndOrga($org, $currentUser);
        if ($request !== null) {
            $participation = $this->createNewParticipation($org, $currentUser, false);
            $em->persist($participation);
            $em->remove($request);
            $em->flush();
        }
        return $this->redirectToRoute('yb_members_my_organizations');
    }

    /**
     * @Route("rename_organization/{id}", name="yb_members_rename_organization")
     */
    public function renameOrganizationAction(Request $request, EntityManagerInterface $em, UserInterface $user = null, Organization $organization)
    {
        $this->checkIfAuthorized($user);
        $form = $this->createFormBuilder()
            ->add('new_name', TextType::class, ['label' => 'Nouveau nom'])
            ->add('submit', SubmitType::class, ['label' => 'Valider'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->handleRenameOrganization($form, $organization, $em)) {
                return $this->redirectToRoute('yb_members_my_organizations');
            }
        }
        return $this->render('@App/YB/Members/rename_organizations.html.twig', [
            'organization' => $organization,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/venue/new", name="yb_members_venues_new")
     */
    public function newVenueAction(UserInterface $user = null, Request $request, EntityManagerInterface $em, MailDispatcher $mailDispatcher)
    {
        $this->checkIfAuthorized($user);
        $venue = new Venue();
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if (!$currentUser->hasPrivateOrganization()) {
            $this->createPrivateOrganization($em, $currentUser);
        }
        $userOrganizations = $this->getOrganizationsFromUser($currentUser);
        $form = $this->createForm(VenueType::class, $venue, ['creation' => true, 'userOrganizations' => $userOrganizations, 'block' => false]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($this->isExistingAddress($em, $venue->getAddress())) {
                $this->addFlash('error', "Il y a déjà une salle à cette adresse ! Peut-être votre salle existe-elle déjà ?!");
                return $this->redirectToRoute('yb_members_venues_new');
            } else {
                $venue->createDefaultConfig();
                if ($venue->isLocatedInBelgium()) $this->setCoordinates($venue);
                $em->persist($venue);
                $em->flush();
                if ($form->get('addBlocks')->isClicked()) {
                    return $this->redirectToRoute('yb_members_venue_add_configs', ['venue' => $venue->getId()]);
                } else {
                    $this->addFlash('yb_notice', 'La salle a bien été créée.');
                    return $this->redirectToRoute('yb_members_my_venues');
                }
            }
        }
        return $this->render('@App/YB/Members/venue_new.html.twig', [
            'admin' => $user->isSuperAdmin(),
            'form' => $form->createView(),
            'venue' => $venue,
        ]);
    }

    /**
     * @Route("/venue/{venue}/add-config", name="yb_members_venue_add_configs")
     */
    public function addConfigsAction(UserInterface $user = null, Venue $venue, Request $request, EntityManagerInterface $em)
    {
        if (!$user->isSuperAdmin()) $this->checkIfAuthorizedVenue($user, $venue);
        $config = new VenueConfig();
        $form = $this->createForm(VenueConfigType::class, $config);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $venue->addConfiguration($config);
            $em->flush();
            if ($config->hasFreeSeatingPolicy() || $config->isOnlyStandup()) {
                $this->addFlash('yb_notice', 'La salle a bien été configurée.');
                return $this->redirectToRoute('yb_members_my_venues');
            } else {
                return $this->redirectToRoute('yb_members_add_venue_block', ['config' => $config->getId()]);
            }
        }
        return $this->render('@App/YB/Members/venue_add_config.html.twig', [
            'venue' => $venue,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/venue/{config}/add-blocks", name="yb_members_add_venue_block")
     */
    public function addBlocksAction(UserInterface $user = null, VenueConfig $config, Request $request, EntityManagerInterface $em)
    {
        if (!$user->isSuperAdmin()) $this->checkIfAuthorizedVenueConfig($user, $config);
        $form = $this->createForm(VenueConfigType::class, $config, ['block' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->generateRows();
            $em->flush();
            if ($config->hasUnsquaredBlock()) {
                return $this->redirectToRoute('yb_members_add_rows_in_unsquared_block', ['config' => $config->getId()]);
            } else {
                $this->addFlash('yb_notice', 'La salle a bien été configurée.');
                return $this->redirectToRoute('yb_members_my_venues');
            }
        }
        return $this->render('@App/YB/Members/venue_add_block.html.twig', [
            'form' => $form->createView(),
            'config' => $config,
        ]);
    }

    /**
     * @Route("venue/{config}/add-rows", name="yb_members_add_rows_in_unsquared_block")
     */
    public function addRowsAction(VenueConfig $config, Request $request, EntityManagerInterface $em, UserInterface $user = null)
    {
        if (!$user->isSuperAdmin()) $this->checkIfAuthorizedVenueConfig($user, $config);
        $unsquaredBlocks = $config->getUnsquaredBlocks();
        $form = $this->createForm(VenueConfigType::class, $config, ['row' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $config->generateSeatForUnsquareRows();
            $em->flush();
            $this->addFlash('yb_notice', 'La salle a bien été configurée.');
            return $this->redirectToRoute('yb_members_my_venues');
        }
        return $this->render('@App/YB/Members/venue_configure_block.html.twig', [
            'blocks' => $unsquaredBlocks,
            'config' => $config,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/venue/{id}/update", name="yb_members_venue_edit")
     */
    public function editVenueAction(Venue $venue, UserInterface $user = null, Request $request, EntityManagerInterface $em)
    {
        if (!$user->isSuperAdmin()) {
            $this->checkIfAuthorizedVenue($user, $venue);
        }
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if (!$currentUser->hasPrivateOrganization()) {
            $this->createPrivateOrganization($em, $currentUser);
        }
        $userOrganizations = $this->getOrganizationsFromUser($currentUser);
        $savedAddress = $venue->getAddress();
        $form = $this->createForm(VenueType::class, $venue, ['admin' => $user->isSuperAdmin(), 'userOrganizations' => $userOrganizations]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($savedAddress !== $venue->getAddress() && $this->isExistingAddress($em, $venue->getAddress())) {
                $this->addFlash('error', "Il y a déjà une salle à cette adresse ! Peut-être votre salle existe-elle déjà ?!");
                return $this->redirectToRoute('yb_members_venue_edit', ['id' => $venue->getId()]);
            } else {
                if ($savedAddress !== $venue->getAddress() && $venue->isLocatedInBelgium()){
                    $this->setCoordinates($venue);
                }
                $em->persist($venue);
                $em->flush();
                $this->addFlash('yb_notice', 'La salle a bien été modifiée.');
                return $this->redirectToRoute('yb_members_my_venues');
            }
        }
        return $this->render('@App/YB/Members/venue_new.html.twig', [
            'admin' => $user->isSuperAdmin(),
            'form' => $form->createView(),
            'venue' => $venue,
        ]);
    }

    /**
     * @Route("/venue/config/{id}/update", name="yb_members_config_edit")
     */
    public function editConfigurationAction(VenueConfig $config, UserInterface $user = null, Request $request, EntityManagerInterface $em)
    {
        if (!$user->isSuperAdmin()) {
            $this->checkIfAuthorizedVenueConfig($user, $config);
        }
        if ($this->isVenueStillHostingEvent($em, $config->getVenue())) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier l\'agencement d\'une configuration alors qu\'il y a encore au moins un événement de prévu. Attendez que la configuration ne soit plus utilisée pour la modifier.');
            return $this->redirectToRoute('yb_members_my_venues');
        }
        $form = $this->createForm(VenueConfigType::class, $config);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            if ($config->hasFreeSeatingPolicy() || $config->isOnlyStandup()) {
                $this->addFlash('yb_notice', 'La configuration a bien été modifiée');
                return $this->redirectToRoute('yb_members_my_venues');
            } else {
                return $this->redirectToRoute('yb_members_add_venue_block', ['config' => $config->getId()]);
            }
        }
        return $this->render('@App/YB/Members/venue_add_config.html.twig', [
            'venue' => $config->getVenue(),
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/my-venues", name="yb_members_my_venues")
     */
    public function myVenuesAction(UserInterface $user = null, Request $request, EntityManagerInterface $em)
    {
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if ($currentUser->isSuperAdmin()) {
            $venues = $em->getRepository('AppBundle:YB\Venue')->findAll();
        } else {
            $venues = $currentUser->getVenuesHandled();
        }
        $venues = $this->removeClosedVenues($venues);
        return $this->render('@App/YB/Members/my_venues.html.twig', [
            'venues' => $venues,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * @Route("/venue/{id}/close", name="yb_members_close_venue")
     */
    public function closeVenueAction(Venue $venue, UserInterface $user = null, Request $request, EntityManagerInterface $em)
    {
        $this->checkIfAuthorizedVenue($user, $venue);
        if ($this->isVenueStillHostingEvent($em, $venue)) {
            $this->addFlash('error', 'Il y a au moins un événement planifié dans cette salle ! Supprimez-là quand l\'événement est fini !');
        } else {
            $em->remove($venue);
            $em->flush();
        }
        return $this->redirectToRoute('yb_members_my_venues');
    }

    /**
     * @Route("/config/{id}/close", name="yb_members_delete_config")
     */
    public function deleteConfiguration(VenueConfig $config, UserInterface $user = null, Request $request, EntityManagerInterface $em)
    {
        $this->checkIfAuthorizedVenueConfig($user);
        if ($this->isConfigStillUsedForEvent($em, $config)) {
            $this->addFlash('error', 'Il y a au moins un événement planifié dans cette salle avec cette configuration-là ! Supprimez-là quand l\'événement est fini !');
        } else {
            $em->remove($config);
            $em->flush();
        }
        return $this->redirectToRoute('yb_members_my_venues');
    }

    /**
     * @Route("/config/block/{id}/configure-row", name="yb_members_configure_block")
     */
    public function configureBlockAction(Block $block, UserInterface $user, Request $request, EntityManagerInterface $em)
    {
        $this->checkIfAuthorizedVenueBlock($user, $block);
        if ($this->isVenueStillHostingEvent($em, $block->getConfig()->getVenue())){
            $this->addFlash('error', 'Vous ne pouvez pas modifier l\'agencement d\'un bloc alors qu\'il y a encore au moins un événement de prévu. Attendez que la configuration ne soit plus utilisée pour le modifier.');
            return $this->redirectToRoute('yb_members_my_venues');
        }
        $form = $this->createForm(BlockType::class, $block, ['row' => true]);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if (!$block->isValidCustomRow()) {
                $this->addFlash('error', 'Le nombre de sièges calculés avec vos rangées ne correspond pas à la capacité totale du bloc');
                return $this->redirectToRoute('yb_members_configure_block', ['id' => $block->getId()]);
            } else {
                $this->removeSeatsFromDB($block, $em);
                $block->generateSeats();
                $em->persist($block);
                $em->flush();
                $this->addFlash('yb_notice', 'Les rangées ont bien été ajoutées/modifiées.');
                return $this->redirectToRoute('yb_members_my_venues');
            }
        }
        return $this->render('@App/YB/Members/venue_update_unsquared_rows.html.twig', [
            'block' => $block,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/change-blk-capacity", name="change_blk_capacity")
     * @param Request $request
     */
    public function changeBlockCapacity(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $blockId = $request->get('blockid');
        $newCapacity = $request->get('capacity');
        $blk = $em->getRepository('AppBundle:YB\Block')->find($blockId);
        $blk->setCapacity($newCapacity);
        $em->flush();
        $responseArray = array();
        $responseArray[] = array(
            'capacity' => $newCapacity
        );
        return new JsonResponse($responseArray);
    }

    /**
     * @Route("/venue-help", name="help_venue")
     */
    public function openHelpCreateVenue()
    {
        return $this->render('@App/YB/Members/venue_help.html.twig');
    }

    /**
     * @Route("/customize-ticket/{id}", name="yb_customize_ticket")
     */
    public function customizeTicketAction(YBContractArtist $campaign, UserInterface $user = null, Request $request, EntityManagerInterface $em){
        $this->checkIfAuthorized($user, $campaign);
        $customTicket = $em->getRepository('AppBundle:YB\CustomTicket')->findByYBContractArtist($campaign->getId());
        if ($customTicket === null){
            $customTicket = new CustomTicket($campaign);
        }
        $form = $this->createForm(CustomTicketType::class, $customTicket);
        $form->handleRequest($request);
        $stations = new ArrayCollection();
        if ($form->isSubmitted() && $form->isValid()){
            if ($customTicket->isCommuteAdded()) {
                if ($customTicket->isCommuteSTIBAdded()) {
                    $stationsSTIB = $this->getPublicTransportStations($campaign->getVenue()->getAddress()->getLatitude(), $campaign->getVenue()->getAddress()->getLongitude(), PublicTransportStation::STIB, $em);
                    if (is_array($stations)){
                        $stations = array_merge($stations, $stationsSTIB);
                    } else {
                        $stations = array_merge($stations->toArray(), $stationsSTIB);
                    }
                }
                if ($customTicket->isCommuteSNCBAdded()) {
                    $stationsSNCB = $this->getPublicTransportStations($campaign->getVenue()->getAddress()->getLatitude(), $campaign->getVenue()->getAddress()->getLongitude(), PublicTransportStation::SNCB, $em);
                    if (is_array($stations)){
                        $stations = array_merge($stations, $stationsSNCB);
                    } else {
                        $stations = array_merge($stations->toArray(), $stationsSNCB);
                    }
                }
                if ($customTicket->isCommuteTECAdded()) {
                    $stationsTEC = $this->getTECStop($campaign->getVenue()->getAddress());
                    if (is_array($stations)){
                        $stations = array_merge($stations, $stationsTEC);
                    } else {
                        $stations = array_merge($stations->toArray(), $stationsTEC);
                    }
                }
                $customTicket->setStations($stations);
                if (count($stations) > 0){
                    $mapUrl = $customTicket->getMapQuestUrl($this->getParameter('mapquest_key'));
                    $imgPath = 'yb/images/custom-tickets/campaign' . $customTicket->getCampaign()->getId() . '.jpg';
                    $customTicket->setMapsImagePath($imgPath);
                    file_put_contents($imgPath, file_get_contents($mapUrl));
                } else {
                    $customTicket->setMapsImagePath(null);
                }
            }
            $em->persist($customTicket);
            $em->flush();
            $this->addFlash('yb_notice', 'Vos préférences ont bien été enregistrées !');
            return $this->redirectToRoute('yb_members_dashboard');
        }
        return $this->render('@App/YB/Members/customize_ticket.html.twig', [
            'campaign' => $campaign,
            'form' => $form->createView(),
        ]);
    }

    // ------------------ private functions -------------------- //

    /**
     * Check if an organization has ongoing campaigns
     *
     * @param Organization $organization
     * @param EntityManagerInterface $em
     * @return bool
     */
    private function hasPendingProjects(Organization $organization, EntityManagerInterface $em)
    {
        $pendingProjects = $em->getRepository('AppBundle:YB\YBContractArtist')->getOrganizationOnGoingEvents($organization);
        return count($pendingProjects) !== 0;
    }

    /**
     * The user quit the organization
     * Even if the user was the last member, the organization is not deleted
     *
     * @param EntityManagerInterface $em
     * @param Organization $org
     * @param User $member
     */
    private function quitOrganization(EntityManagerInterface $em, Organization $org, User $member)
    {
        $em->remove($member->getParticipationToOrganization($org));
    }

    /**
     * Changes the admin right of a user for a given organization
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param $isAdmin
     */
    private function changeAdminRight(Request $request, EntityManagerInterface $em, $isAdmin)
    {
        $member = $em->getRepository('AppBundle:User')->find($request->get('user_id'));
        $org = $em->getRepository('AppBundle:YB\Organization')->find($request->get('organization_id'));
        $participation = $member->getParticipationToOrganization($org);
        $participation->setAdmin($isAdmin);
        $em->flush();
    }

    /**
     * When a user wanna create a new organization
     *
     * First, check that the user does not try to create a self-named organization (it's forbidden)
     * Creates the new organization, set the user as its admin
     * Also add the super-admin of Un-Mute as admin of the organization
     *
     * @param Organization $organization
     * @param User $currentUser
     * @param EntityManagerInterface $em
     */
    private function handleNewOrganizationRequest(Organization $organization, User $currentUser, EntityManagerInterface $em)
    {
        if ($organization->getName() === $currentUser->getDisplayName()) {
            $this->addFlash('error', 'Une organisation ne peut porter le même nom que son administrateur !');
        } elseif ($this->organizationNameExist($em, $organization->getName())) {
            $this->addFlash('error', 'Une organisation existe déjà avec ce nom. Essayez un autre nom !');
        } else {
            $this->createNewParticipation($organization, $currentUser, true);
            $em->persist($organization);
            $em->flush();
            $orgName = $organization->getName();
            $this->addFlash('yb_notice', 'Votre nouvelle organisation, ' . $orgName . ', a bien été enregistrée.');
        }
    }

    /**
     * When a user wanna add another user to its organization
     *
     * First check if the user to be addde is not already a member
     * If the user is not in the DB, it creates a User with the given email address as username
     * Add the user in the organization with a "pending" status
     * Send a confirmation email to the user to be added
     *
     * @param FormInterface $form_add_user
     * @param MailDispatcher $mailDispatcher
     * @param Organization $org
     * @param User $user
     * @param EntityManagerInterface $em
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    private function handleAddUserToOrganization(FormInterface $form_add_user, MailDispatcher $mailDispatcher, Organization $org, User $user, EntityManagerInterface $em)
    {
        $data = $form_add_user->getData();
        if ($form_add_user->get('submit')->isClicked()) {
            $guest = $em->getRepository('AppBundle:User')->findOneBy(['username' => $data['email_address']]);
            if ($guest !== null && $org->hasMember($guest)) {
                $this->addFlash('error', 'Cet utilisateur est déjà dans votre organisation !');
                return false;
            } else {
                $invitation = new OrganizationJoinRequest($user, $data['email_address'], $org);
                $em->persist($invitation);
                $em->flush();
                $this->sendInvitation($invitation, $mailDispatcher);
                $this->addFlash('yb_notice', 'Un mail a été envoyé à la personne invitée !');
                return true;
            }
        }
        return false;
    }

    /**
     * Send a invitation to join a organization to the one person that is invited
     *
     * @param OrganizationJoinRequest $invitation
     * @param MailDispatcher $mailDispatcher
     */
    private function sendInvitation(OrganizationJoinRequest $invitation, MailDispatcher $mailDispatcher)
    {
        $email = $invitation->getEmail();
        $organization = $invitation->getOrganization();
        $demander = $invitation->getDemander();
        $mailDispatcher->sendYBJoinOrganization($email, $organization, $demander);
    }

    /**
     * Adds a user to an organization
     *
     * @param Organization $org
     * @param User $user
     * @param $isAdmin
     * @return Membership
     */
    private function createNewParticipation(Organization $org, User $user, $isAdmin)
    {
        $participation = new Membership();
        $participation->setAdmin($isAdmin);
        $user->addParticipation($participation);
        $org->addParticipation($participation);
        return $participation;
    }

    /**
     * Creates a self-named organization for the user.
     * When he creates a campaign, he can either do it on the name of a organization
     * or on its own name (a "self-named organization").
     *
     * @param EntityManagerInterface $em
     * @param User $currentUser
     */
    private function createPrivateOrganization(EntityManagerInterface $em, User $currentUser)
    {
        $ownNameOrg = new Organization();
        $ownNameOrg->setName($currentUser->getDisplayName());
        $ownNameOrg->setIsPrivate(true);
        $this->createNewParticipation($ownNameOrg, $currentUser, true);
        $em->persist($ownNameOrg);
        $em->flush();
    }

    /**
     * Checks if a organization that has the given name already exists
     *
     * @param EntityManagerInterface $em
     * @param $newName
     * @return bool
     */
    private function organizationNameExist(EntityManagerInterface $em, $newName)
    {
        $selfNamedOrg = $em->getRepository('AppBundle:YB\Organization')->findBy(['name' => $newName]);
        if (count($selfNamedOrg) === 0) {
            return false;
        } else {
            return !$this->areAllDeleted($selfNamedOrg);
        }
    }

    /**
     * Checks if a given string is already used as the name of a member of a given organization
     * When you create or rename an organization, it cant have the same name as one of its member
     *
     * @param Organization $organization
     * @param $newName
     * @return bool
     */
    private function isNameOfMember(Organization $organization, $newName)
    {
        foreach ($organization->getMembers() as $member) {
            if ($member->getDisplayName() === $newName) {
                return true;
            }
        }
        return false;
    }

    /**
     * Checks if the name is already used by another organization
     * Checks if the name is not one of its member
     * Changes the name of the organization and show a message
     *
     * @param FormInterface $form
     * @param Organization $organization
     * @param EntityManagerInterface $em
     * @return bool
     */
    private function handleRenameOrganization(FormInterface $form, Organization $organization, EntityManagerInterface $em)
    {
        $new_name = $form->getData()['new_name'];
        if ($this->organizationNameExist($em, $new_name)) {
            $this->addFlash('error', 'Une organisation existe déjà avec ce nom. Essayez un autre nom !');
            return false;
        } elseif ($this->isNameOfMember($organization, $new_name)) {
            $this->addFlash('error', 'L\'organisation ne peut avoir le même nom qu\'un de ses membres. Essayez un autre nom !');
            return false;
        } else {
            $organization->setName($new_name);
            $em->flush();
            $this->addFlash('yb_notice', 'Le nom a bien été changé !');
            return true;
        }
    }

    /**
     * Fetches all the active organization of a user
     * Sort them according to their name (A->Z)
     * If the user is a super admin, it removes all the private organization of the other user
     *
     * Result : all the active public organization + the user's private one
     *
     * @param User $currentUser
     * @return array
     */
    private function getOrganizationsFromUser(User $currentUser)
    {
        if ($currentUser->isSuperAdmin()) {
            $userOrganizations = $this->getDoctrine()->getManager()->getRepository('AppBundle:YB\Organization')->findAll();
            $userOrganizations = $this->fetchOrganizationsForSuperUser($currentUser, $userOrganizations);
        } else {
            $userOrganizations = $currentUser->getOrganizations();
        }
        usort($userOrganizations, function ($organization1, $organization2) {
            return strtolower($organization1->getName()) > strtolower($organization2->getName());
        });
        return $userOrganizations;
    }

    /**
     * Checks if all the organizations of an array are deleted
     *
     * @param $orgs
     * @return bool
     */
    private function areAllDeleted($orgs)
    {
        foreach ($orgs as $org) {
            if (!$org->isDeleted()) {
                return false;
            }
        }
        return true;
    }

    /**
     * Fetches all the organizations to be displayed if the user is a Super Admin
     * @param $currentUser
     * @param $organizations
     * @return array
     */
    private function fetchOrganizationsForSuperUser($currentUser, $organizations)
    {
        $organizations = array_filter($organizations, function ($org) {
            return !$org->isDeleted();
        });
        $organizationsToBeDisplayed = [];
        foreach ($organizations as $org) {
            if ($org->hasMember($currentUser)) {
                array_unshift($organizationsToBeDisplayed, $org);
            } else {
                array_push($organizationsToBeDisplayed, $org);
            }
        }
        return $organizationsToBeDisplayed;
    }

    /**
     * Remove all the closed venues (venues that have been soft deleted)
     * @param $venues
     * @return array
     */
    private function removeClosedVenues($venues)
    {
        $venues = array_filter($venues, function ($venue) {
            return !$venue->isDeleted();
        });
        usort($venues, function (Venue $v1, Venue $v2) {
            return strtolower($v1->getAddress()->getName()) > strtolower($v2->getAddress()->getName());
        });
        return $venues;
    }
    /**
     * Checks in the DB if the given address already exists.
     * @param EntityManagerInterface $em
     * @param Address $a
     * @return bool
     */
    private function isExistingAddress(EntityManagerInterface $em, Address $a)
    {
        $addresses = $em->getRepository('AppBundle:Address')->findAll();
        foreach ($addresses as $address) {
            if ($a->equals($address)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Checks if there is still at least one event (YBContractArtist) planned in the venue
     * @param EntityManagerInterface $em
     * @param Venue $venue
     * @return bool
     */
    private function isVenueStillHostingEvent(EntityManagerInterface $em, Venue $venue)
    {
        $currentEvents = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllOnGoingEvents();
        foreach ($currentEvents as $currentEvent) {
            if ($currentEvent->getVenue() === $venue) {
                return true;
            }
        }
        return false;
    }
    /**
     * Checks if there is still at least one event (YBContractArtist) planned in the venue with the given configurations
     * @param EntityManagerInterface $em
     * @param VenueConfig $config
     * @return bool
     */
    private function isConfigStillUsedForEvent(EntityManagerInterface $em, VenueConfig $config)
    {
        $currentEvents = $em->getRepository('AppBundle:YB\YBContractArtist')->getAllOnGoingEvents();
        foreach ($currentEvents as $currentEvent) {
            if ($currentEvent->getConfig() === $config) {
                return true;
            }
        }
        return false;
    }
    /**
     * Fetches in the DB all the venues that are not closed or not temporary
     * @param EntityManagerInterface $em
     * @param User $user
     * @return array
     */
    private function getActiveVenue(EntityManagerInterface $em, User $user){
        $venues = $em->getRepository('AppBundle:YB\Venue')->findAll();
        $activeVenues = [];
        foreach ($venues as $venue) {
            if (!$venue->isDeleted() && !$venue->getAcceptVenueTemp()) {
                array_push($activeVenues, $venue);
            }
        }
        return $this->sortVenues($activeVenues, $user);
    }
    /**
     * Sorts the list of venues.
     * First, the venues that are handled by the given user
     * Then, all the others venue
     * If the venue is handled by the user, we add a suffix to its name.
     * @param $venues
     * @param User $user
     * @return array
     */
    private function sortVenues($venues, User $user){
        $sortedVenues = [];
        foreach ($venues as $v){
            if ($user->ownsYBVenue($v)){
                $v->setDisplayName($v->getName().Venue::OWN_VENUE);
                array_unshift($sortedVenues, $v);
            } else {
                $v->setDisplayName($v->getName());
                array_push($sortedVenues, $v);
            }
        }
        return $sortedVenues;
    }

    /**
     * @Route("/get-config-from-venue", name="campaign_venue_list_config")
     * @param Request $request
     * @return JsonResponse
     */
    public function getListOfConfigsForVenue(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $id = $request->get('venueid');
        $venue = $em->getRepository('AppBundle:YB\Venue')->find($id);
        $configs = $venue->getConfigurations();
        $responseArray = array();
        foreach ($configs as $config) {
            $responseArray[] = array(
                'id' => $config->getId(),
                'name' => $config->getName(),
            );
        }
        return new JsonResponse($responseArray);
    }

    private function removeSeatsFromDB(Block $block, EntityManagerInterface $em){
        $seats = $em->getRepository('AppBundle:YB\Seat')->getSeatFromBlock($block->getId());
        foreach ($seats as $s){
            $em->remove($s);
        }
    }

    private function setCoordinates(Venue $venue){
        $url = $this->generateLocationIQUrl($venue);
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        $r = json_decode($result, true);
        $lat = $r[0]["lat"];
        $lon = $r[0]["lon"];
        curl_close($curl);
        $venue->getAddress()->setLatitude($lat);
        $venue->getAddress()->setLongitude($lon);
    }

    public function generateLocationIQUrl(Venue $v){
        $key = $this->getParameter('location_iq_token');
        $baseUrl = 'https://eu1.locationiq.com/v1/search.php?key='. $key . '&q=';
        $formattedAddress = str_replace(' ', '%20', $v->getAddress()->getNaturalWithCountry());
        $url = $baseUrl . $formattedAddress;
        $url .= '&format=json';
        return $url;
    }

    private function getPublicTransportStations($lat, $lon, $transportType, EntityManagerInterface $em, $distance = 1000)
    {
        $url = 'https://opendata.bruxelles.be/api/records/1.0/search/?dataset=' . $transportType . '&geofilter.distance=' . $lat . '%2C' . $lon . '%2C' . $distance;
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = curl_exec($curl);
        $resultArray = json_decode($result, true);
        $stations = [];
        if (isset($resultArray['records'])) {
            foreach ($resultArray['records'] as $station) {
                $stationName = $station['fields']['name'];
                $stationLat = $station['fields']['latitude'];
                $stationLon = $station['fields']['longitude'];
                $distance = ($station['fields']['dist']) / 1000;
                $stations[] = $this->getStationsFromInfos($stationName, $stationLat, $stationLon, $transportType, $distance, $em);
            }
        }
        curl_close($curl);
        $stations = $this->sortStations($stations);
        return $stations;
    }

    private function getStationsFromInfos($stationName, $stationLat, $stationLon, $transportType, $distance, EntityManagerInterface $em){
        $station = $em->getRepository('AppBundle:YB\PublicTransportStation')->getStationsFromInfos($stationName, $stationLat, $stationLon, $this->getCorrespondingType($transportType), $distance);
        if ($station === null){
            $station = new PublicTransportStation($stationName, $stationLat, $stationLon, $transportType, $distance);
        }
        return $station;
    }

    private function getCorrespondingType($string){
        if ($string === PublicTransportStation::SNCB){
            return 'SNCB';
        } else if ($string === PublicTransportStation::STIB) {
            return 'STIB';
        } else {
            return '';
        }
    }

    private function getTECStop(Address $address){
        $city = $address->getCity();
        $lat = $address->getLatitude();
        $lon = $address->getLongitude();
        $handle = fopen("stops.txt", "r");
        $infos = [];
        while (($line = fgets($handle)) !== false) {
            if (strpos($line, strtoupper($city)) !== false){
                $infos[] = $line;
            }
        }
        fclose($handle);
        $stops = $this->getStopFromLine($lat, $lon, $infos);
        $stops = $this->sortStations($stops);
        return $stops;
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        if (($lat1 == $lat2) && ($lon1 == $lon2)) {
            return 0;
        } else {
            $theta = $lon1 - $lon2;
            $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
            $dist = acos($dist);
            $dist = rad2deg($dist);
            $miles = $dist * 60 * 1.1515;
            return ($miles * 1.609344);
        }
    }

    private function getStopFromLine($addressLat, $addressLon, $lines){
        $stops = [];
        foreach ($lines as $line){
            $infos = explode(',', $line);
            $lat = $infos[4];
            $lon = $infos[5];
            $distance = $this->calculateDistance($addressLat, $addressLon, $lat, $lon);
            if ($distance <= 1){
                $stops [] = new PublicTransportStation($infos[2], $lat, $lon, 'TEC', $distance);
            }
        }
        return $stops;
    }

    private function sortStations($stations){
        usort($stations, function(PublicTransportStation $s1, PublicTransportStation $s2){
            if ($s1->getDistance() === $s2->getDistance()){
                return 0;
            } else if ($s1->getDistance() < $s2->getDistance()){
                return -1;
            } else {
                return 1;
            }
        });
        $stations = array_slice($stations, 0, 5);
        return $stations;
    }

    /**
     * @Route("/gmaps", name="gmaps")
     */
    public function getUrl(){
        /*$url = 'http://opendata.tec-wl.be/Current%20GTFS/TEC-GTFS.zip';
        file_put_contents('yb/file-from-tec.zip', file_get_contents($url));
        $zip = new \ZipArchive();
        $res = $zip->open('yb/file-from-tec.zip');
        if ($res === true){
            $zip->extractTo('yb/file-from-tec/');
            $zip->close();
        }*/
        $dir = new \DirectoryIterator('sdnijsdnm');
        foreach ($dir as $file){
            if (!$file->isDot() && $file->getFilename() !== 'stops.txt'){
                unlink('yb/file-from-tec/'.$file);
            }
        }
        return new JsonResponse('hello');
    }
}