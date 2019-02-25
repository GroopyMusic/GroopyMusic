<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\YB\YBCommission;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\YBInvoice;
use AppBundle\Entity\YB\YBTransactionalMessage;
use AppBundle\Form\UserBankAccountType;
use AppBundle\Form\YB\YBContractArtistCrowdType;
use AppBundle\Form\YB\YBContractArtistType;
use AppBundle\Services\AdminExcelCreator;
use AppBundle\Form\YB\YBTransactionalMessageType;
use AppBundle\Services\FinancialDataGenerator;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PaymentManager;
use AppBundle\Services\StringHelper;
use AppBundle\Services\TicketingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class YBMembersController extends BaseController
{
    /**
     * @Route("/dashboard", name="yb_members_dashboard")
     */
    public function dashboardAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);

        $current_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getCurrentYBCampaigns($user);
        $passed_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getPassedYBCampaigns($user);

        return $this->render('@App/YB/Members/dashboard.html.twig', [
            'current_campaigns' => $current_campaigns,
            'passed_campaigns' => $passed_campaigns,
        ]);
    }

    /**
     * @Route("/organizations", name="yb_members_orgs")
     */
    public function viewOrganizationsAction(EntityManagerInterface $em, UserInterface $user = null){
        $this->checkIfAuthorized($user);
        return $this->render('@App/YB/Members/orgs_list.html.twig');
    }

    /**
     * @Route("/campaign/new", name="yb_members_campaign_new")
     */
    public function newCampaignAction(UserInterface $user = null, Request $request, EntityManagerInterface $em, MailDispatcher $mailDispatcher) {
        $this->checkIfAuthorized($user);
        $campaign = new YBContractArtist();
        $campaign->addHandler($user);

        $adminUsers = $em->getRepository('AppBundle:User')->getYBAdmins();

        foreach($adminUsers as $au) {
            $campaign->addHandler($au);
        }

        $form = $this->createForm(YBContractArtistType::class, $campaign, ['creation' => true]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($campaign);
            $em->flush();

            $this->addFlash('yb_notice', 'La campagne a bien été créée.');

            try {
            	$mailDispatcher->sendYBReminderEventCreated($campaign);
            }
            catch(\Exception $e) {

            }

            return $this->redirectToRoute('yb_members_dashboard');
        }

        return $this->render('@App/YB/Members/campaign_new.html.twig', [
            'form' => $form->createView(),
            'campaign' => $campaign,
        ]);
    }

    /**
     * @Route("/campaign/{id}/update", name="yb_members_campaign_edit")
     */
    public function editCampaignAction(YBContractArtist $campaign, UserInterface $user = null, Request $request, EntityManagerInterface $em) {
        $this->checkIfAuthorized($user, $campaign);

        if($campaign->isPassed()) {
            $this->addFlash('yb_error', 'Cette campagne est passée. Il est donc impossible de la modifier.');
            return $this->redirectToRoute('yb_members_passed_campaigns');
        }

        $form = $this->createForm(YBContractArtistType::class, $campaign);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($campaign);
            $em->flush();

            $this->addFlash('yb_notice', 'La campagne a bien été modifiée.');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }


        return $this->render('@App/YB/Members/campaign_new.html.twig', [
            'form' => $form->createView(),
            'campaign' => $campaign,
        ]);
    }

    /**
     * @Route("/campaign/{id}/crowdfunding", name="yb_members_campaign_crowdfunding")
     */
    public function crowdfundingCampaignAction(YBContractArtist $campaign, UserInterface $user = null, Request $request, EntityManagerInterface $em, TicketingManager $ticketingManager, PaymentManager $paymentManager) {
        $this->checkIfAuthorized($user, $campaign);

        $form = $this->createForm(YBContractArtistCrowdType::class, $campaign);

        $form->handleRequest($request);

        if($form->isSubmitted()) {
            if($form->get('refund')->isClicked() && !$campaign->getRefunded()) {
                $campaign->setFailed(true);
                $paymentManager->refundStripeAndYBContractArtist($campaign);

                $em->flush();

                $this->addFlash('yb_notice', 'La campagne a bien été annulée. Les éventuels contributeurs ont été avertis et remboursés.');
                return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
            }

            if($form->get('validate')->isClicked() && !$campaign->getTicketsSent()) {
                foreach($campaign->getContractsFanPaid() as $cf) {
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
    public function ordersCampaignAction(YBContractArtist $campaign, UserInterface $user = null) {
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
    public function transactionalMessageCampaignAction(YBContractArtist $campaign, Request $request, UserInterface $user = null) {
        $this->checkIfAuthorized($user, $campaign);

        $message = new YBTransactionalMessage($campaign);
        $old_messages = $campaign->getTransactionalMessages();

        $form = $this->createForm(YBTransactionalMessageType::class, $message);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
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
    public function invoicesViewListAction(EntityManagerInterface $em,UserInterface $user = null){
        $this->checkIfAuthorized($user);

        /** @var \AppBundle\Repository\YB\YBContractArtistRepository $YBCARepository */
        $YBCARepository = $em->getRepository('AppBundle:YB\YBContractArtist');
        $all_campaigns = $YBCARepository->getAllYBCampaigns($user);

        return $this->render('@App/YB/Members/invoices.html.twig', [
            'campaigns' => $all_campaigns,
        ]);
    }

    /**
     * @Route("/invoice/{id}/generate", name="yb_members_invoice_generate")
     */
    public function invoiceGenerateAction(YBContractArtist $campaign, EntityManagerInterface $em, UserInterface $user = null){
        $this->checkIfAuthorized($user);

        $invoice = new YBInvoice();
        $invoice->setCampaign($campaign);
        $em->persist($invoice);

        $cfs = $campaign->getContractsFanPaid();
        /** @var ContractFan $cf */
        foreach ($cfs as $cf){
            if ($campaign->isPassed() || $cf->getDate() < new \DateTime("first day of this month midnight")) {
                /** @var Purchase $purchase */
                foreach ($cf->getPurchases() as $purchase){
                    if ($purchase->getInvoice() === null){
                        $purchase->setInvoice($invoice);
                    }
                }
            }
        }


        return $this->invoicesViewListAction($em, $user);
    }

    /**
     * @Route("/invoice/{id}/validate", name="yb_members_invoice_validate")
     */
    public function invoiceValidateAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user = null){
        $this->checkIfAuthorized($user, $invoice->getCampaign());

        $invoice->validate();
        $em->persist($invoice);

        return $this->invoicesViewListAction($em, $user);
    }

    /**
     * @Route("/invoice/{id}/sold", name="yb_members_invoice_sold")
     */
    public function invoiceSoldDetailsAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user){
        $campaign = $invoice->getCampaign();
        $this->checkIfAuthorized($user, $campaign);

        //$purchases = $invoice->getPurchases();
        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildFromInvoice($invoice);

        $counterparts = array_map(function ($purchase){
            /** @var Purchase $purchase */
            return $purchase->getCounterpart();
        }, $invoice->getPurchases()->toArray());

        $cfs = array_map(function ($purchase){
            /** @var Purchase $purchase */
            return $purchase->getContractFan();
        }, $invoice->getPurchases()->toArray());

        return $this->render('@App/PDF/yb_invoice_sold.html.twig', [
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            'counterparts' => $counterparts,
            'cfs' => $cfs
        ]);
    }

    /**
     * @Route("/invoice/{id}/fee", name="yb_members_invoice_fee")
     */
    public function invoiceFeeDetailsAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user){
        $campaign = $invoice->getCampaign();
        $this->checkIfAuthorized($user, $campaign);

        //$purchases = $invoice->getPurchases();
        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildFromInvoice($invoice);

        $counterparts = array_map(function ($purchase){
            /** @var Purchase $purchase */
            return $purchase->getCounterpart();
        }, $invoice->getPurchases()->toArray());

        $cfs = array_map(function ($purchase){
            /** @var Purchase $purchase */
            return $purchase->getContractFan();
        }, $invoice->getPurchases()->toArray());

        return $this->render('@App/PDF/yb_invoice_fee.html.twig', [
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            'counterparts' => $counterparts,
            'cfs' => $cfs
        ]);
    }

    /**
     * @Route("/campaign/{id}/sold", name="yb_members_campaign_sold")
     */
    public function campaignSoldDetailsAction(YBContractArtist $campaign, UserInterface $user = null){
        $this->checkIfAuthorized($user, $campaign);

        $cfs = array_reverse($campaign->getContractsFanPaid());
        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildAllCampaignData();

        return $this->render('@App/PDF/yb_invoice_sold.html.twig', [
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            'counterparts' => $campaign->getCounterparts()->toArray(),
            'cfs' => $cfs
        ]);
    }

    /**
     * @Route("/campaign/{id}/fee", name="yb_members_campaign_fee")
     */
    public function campaignFeeDetailsAction(YBContractArtist $campaign, UserInterface $user = null){
        $this->checkIfAuthorized($user, $campaign);

        $cfs = array_reverse($campaign->getContractsFanPaid());
        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildAllCampaignData();

        return $this->render('@App/PDF/yb_invoice_fee.html.twig', [
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            'counterparts' => $campaign->getCounterparts()->toArray(),
            'cfs' => $cfs,
        ]);
    }

    /**
     * @Route("/aide-facturation", name="yb_members_payment_options")
     */
    public function paymentOptionsAction(UserInterface $user = null, Request $request) {
        $this->checkIfAuthorized($user, null);

        $form = $this->createForm(UserBankAccountType::class, $user);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
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

        $passed_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getPassedYBCampaigns($user);

        return $this->render('@App/YB/Members/passed_campaigns.html.twig', [
            'campaigns' => $passed_campaigns,
        ]);
    }

    /**
     * @Route("/campaign/{id}/excel", name="yb_members_campaign_excel")
     */
    public function excelAction(YBContractArtist $campaign, UserInterface $user = null, StringHelper $strHelper) {
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

        if(count($cfs) > 0) {

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

            foreach($colonnes as $colonne) {
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettre . '1', $colonne);
                $lettre++;
            }

            $chiffre = 2;

            foreach($cfs as $cf) {
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


                foreach($colonnes as $key => $colonne) {
                    $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettre. "" . $chiffre, $colonne);

                    // Date
                    if($key == 2)
                        $phpExcelObject->getActiveSheet()
                            ->getStyle($lettre. "" . $chiffre)
                            ->getNumberFormat()
                            ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);
                    $lettre++;
                }
                $chiffre++;
            }
        }

        $phpExcelObject->getActiveSheet()->setTitle('Commandes');

        if($campaign->getTicketsSent()) {
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

            foreach($colonnes as $colonne) {
                $phpExcelObject->setActiveSheetIndex(1)->setCellValue($lettre . '1', $colonne);
                $lettre++;
            }

            $chiffre = 2;

            foreach($cfs as $cf) {
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

                    foreach($colonnes as $key => $colonne) {
                        $phpExcelObject->setActiveSheetIndex(1)->setCellValue($lettre. "" . $chiffre, $colonne);
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
    public function removePhotoAction(Request $request, YBContractArtist $campaign, $code) {

        $this->checkCampaignCode($campaign, $code);

        $em = $this->getDoctrine()->getManager();

        $filename = $request->get('filename');

        $photo = $em->getRepository('AppBundle:Photo')->findOneBy(['filename' => $filename]);

        $em->remove($photo);

        $campaign->removeCampaignPhoto($photo);

        $filesystem = new Filesystem();
        $filesystem->remove($this->get('kernel')->getRootDir().'/../web/' . YBContractArtist::getWebPath($photo));

        $em->persist($campaign);
        $em->flush();

        return new Response();
    }

    /**
     * @Route("admin/campaigns/excel", name="yb_admin_excel_all_campaigns")
     */
    public function adminExcelAllCampaigns(UserInterface $user = null){
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
}
