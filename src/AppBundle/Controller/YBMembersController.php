<?php

namespace AppBundle\Controller;

use AppBundle\AppBundle;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\YB\YBCommission;
use AppBundle\Entity\User;
use AppBundle\Entity\YB\OrganizationJoinRequest;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\YBInvoice;
use AppBundle\Entity\YB\YBTransactionalMessage;
use AppBundle\Exception\YBAuthenticationException;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\Membership;
use AppBundle\Form\UserBankAccountType;
use AppBundle\Form\YB\YBContractArtistCrowdType;
use AppBundle\Form\YB\YBContractArtistType;
use AppBundle\Services\AdminExcelCreator;
use AppBundle\Form\YB\YBTransactionalMessageType;
use AppBundle\Services\FinancialDataGenerator;
use AppBundle\Form\YB\OrganizationType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PaymentManager;
use AppBundle\Services\StringHelper;
use AppBundle\Services\TicketingManager;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;

class YBMembersController extends BaseController
{
    /**
     * @Route("/dashboard", name="yb_members_dashboard")
     */
    public function dashboardAction(EntityManagerInterface $em, UserInterface $user = null)
    {
        $this->checkIfAuthorized($user);

        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if ($currentUser->isSuperAdmin()){
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
    public function viewOrganizationsAction(EntityManagerInterface $em, UserInterface $user = null){
        $this->checkIfAuthorized($user);
        return $this->render('@App/YB/Members/orgs_list.html.twig');
    }

    /**
     * @Route("/campaign/new", name="yb_members_campaign_new")
     */
    public function newCampaignAction(UserInterface $user = null, Request $request, EntityManagerInterface $em, MailDispatcher $mailDispatcher) {
        /** @var \AppBundle\Entity\User $user */
        $this->checkIfAuthorized($user);
        $campaign = new YBContractArtist();

    
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if (!$currentUser->hasPrivateOrganization()){
            $this->createPrivateOrganization($em, $currentUser);
        }
        $userOrganizations = $this->getOrganizationsFromUser($currentUser);
        $form = $this->createForm(YBContractArtistType::class, $campaign, ['creation' => true, 'userOrganizations' => $userOrganizations]);

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
            'admin' => $user->isSuperAdmin(),
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

        $form = $this->createForm(YBContractArtistType::class, $campaign,
            ['admin' => $user->isSuperAdmin()]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($campaign);
            $em->flush();

            $this->addFlash('yb_notice', 'La campagne a bien été modifiée.');
            return $this->redirectToRoute($request->get('_route'), $request->get('_route_params'));
        }


        return $this->render('@App/YB/Members/campaign_new.html.twig', [
            'admin' => $user->isSuperAdmin(),
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
            'admin' => $user->isSuperAdmin(),
            'campaigns' => $all_campaigns,
        ]);
    }

    /**
     * @Route("/invoice/{id}/generate", name="yb_members_invoice_generate")
     */
    public function invoiceGenerateAction(YBContractArtist $campaign, EntityManagerInterface $em, UserInterface $user = null){
        //$this->checkIfAuthorized($user);
        if ($user == null || !$user->isSuperAdmin()){
            throw new YBAuthenticationException();
        }

        $invoice = new YBInvoice();
        $invoice->setCampaign($campaign);
        $em->persist($invoice);

        $cfs = $campaign->getContractsFanPaid();
        /** @var ContractFan $cf */
        foreach ($cfs as $cf){
            /** @var Purchase $purchase */
            foreach ($cf->getPurchases() as $purchase){
                if ($purchase->getInvoice() === null){
                    $purchase->setInvoice($invoice);
                    $em->persist($purchase);
                }
            }
        }

        $em->flush();
        return $this->redirectToRoute("yb_members_invoices");
    }

    /**
     * @Route("/invoice/{id}/validate", name="yb_members_invoice_validate")
     */
    public function invoiceValidateAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user = null){
        $this->checkIfAuthorized($user, $invoice->getCampaign());

        $invoice->validate();
        $em->persist($invoice);

        return $this->redirectToRoute("yb_members_invoices");
    }

    /**
     * @Route("/invoice/{id}/sold", name="yb_members_invoice_sold")
     */
    public function invoiceSoldDetailsAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user){
        $campaign = $invoice->getCampaign();
        if ($user == null || !$user->isSuperAdmin()){
            $this->checkIfAuthorized($user, $campaign);
        }
        $cfs = array_reverse($campaign->getContractsFanPaid());
        $purchases = $invoice->getPurchases();

        $tickets = array();

        foreach ($cfs as $cf){
            /** @var ContractFan $cf */
            if ($cf->getPurchases()->first()->getInvoice() == $invoice){
                $tickets = array_merge($tickets, $cf->getTickets()->toArray());
            }
        }

        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildFromInvoice($invoice);

        $counterparts = array_map(function ($purchase){
            /** @var Purchase $purchase */
            return $purchase->getCounterpart();
        }, $invoice->getPurchases()->toArray());

        return $this->render('@App/PDF/yb_invoice_sold.html.twig', [
            'invoice' => $invoice,
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            //'counterparts' => $counterparts,
            'tickets' => $tickets
        ]);
    }

    /**
     * @Route("/invoice/{id}/fee", name="yb_members_invoice_fee")
     */
    public function invoiceFeeDetailsAction(YBInvoice $invoice, EntityManagerInterface $em, UserInterface $user){
        $campaign = $invoice->getCampaign();
        if ($user == null || !$user->isSuperAdmin()){
            $this->checkIfAuthorized($user, $campaign);
        }

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
            'invoice' => $invoice,
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            //'counterparts' => $counterparts,
            //'cfs' => $cfs
        ]);
    }

    /**
     * @Route("/campaign/{id}/sold", name="yb_members_campaign_sold")
     */
    public function campaignSoldDetailsAction(YBContractArtist $campaign, UserInterface $user = null){
        if ($user == null || !$user->isSuperAdmin()){
            throw new YBAuthenticationException();
        }
        //$this->checkIfAuthorized($user, $campaign);

        $cfs = array_reverse($campaign->getContractsFanPaid());
        $cfs = array_filter($cfs, function($cf){
            /** @var ContractFan $cf */
            /** @var Purchase $purchase */
            $purchase = $cf->getPurchases()->first();
            return $purchase->getInvoice() == null;
        });
        $tickets = array();

        foreach ($cfs as $cf){
            /** @var ContractFan $cf */
            $tickets = array_merge($tickets, $cf->getTickets()->toArray());
        }


        $financialDataService = new FinancialDataGenerator($campaign);
        $financialDataService->buildInvoicelessCampaignData();

        return $this->render('@App/PDF/yb_invoice_sold.html.twig', [
            'invoice' => null,
            'ticketData' => $financialDataService->getTicketData(),
            'campaign' => $campaign,
            //'counterparts' => $campaign->getCounterparts()->toArray(),
            //'cfs' => $cfs,
            'tickets' => $tickets
        ]);
    }

    /**
     * @Route("/campaign/{id}/fee", name="yb_members_campaign_fee")
     */
    public function campaignFeeDetailsAction(YBContractArtist $campaign, UserInterface $user = null){
        if ($user == null || !$user->isSuperAdmin()){
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

        if ($user->isSuperAdmin()){
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
    public function excelAction(YBContractArtist $campaign, UserInterface $user = null, StringHelper $strHelper) {
        if ($user == null){
            throw new YBAuthenticationException();
        }
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

     /**
     * @Route("/my-organizations", name="yb_members_my_organizations")
     */
    public function myOrganizationsAction(EntityManagerInterface $em, UserInterface $user = null, Request $request){
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if ($currentUser->isSuperAdmin()){
            $organizations = $em->getRepository('AppBundle:YB\Organization')->findAll();
            $organizationsToBeDisplayed = $this->fetchOrganizationsForSuperUser($currentUser, $organizations);
        } else {
            $organizationsToBeDisplayed = $currentUser->getPublicOrganizations();
        }
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
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
    public function quitOrganizationAction(Organization $org, UserInterface $user = null, EntityManagerInterface $em){
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if ($org->hasOnlyOneMember()){
            if (!$this->hasPendingProjects($org, $em)){
                $em->remove($org);
            } else {
                $this->addFlash('error', 'Votre organisation a encore des projets et vous êtes le seul membre restant ! Vous ne pouvez pas quitter le navire en pleine mer !');
            }
        } else {
            if ($org->hasAtLeastOneAdminLeft($currentUser)){
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
    public function addToOrganizationAction(Request $request, Organization $org, UserInterface $user = null, MailDispatcher $mailDispatcher, EntityManagerInterface $em){
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        $form_add_user = $this->createFormBuilder()
            ->add('email_address', EmailType::class, ['label' => 'Adresse e-mail'])
            ->add('submit', SubmitType::class, ['label' => 'Ajouter'])
            ->getForm();
        $form_add_user->handleRequest($request);
        if ($form_add_user->isSubmitted() && $form_add_user->isValid()){
            if ($this->handleAddUserToOrganization($form_add_user, $mailDispatcher, $org, $currentUser, $em)){
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
    public function removeFromOrganizationAction(Request $request, EntityManagerInterface $em, UserInterface $user = null){
        $this->checkIfAuthorized($user);
        $member = $em->getRepository('AppBundle:User')->find($request->get('user_id'));
        $organization = $em->getRepository('AppBundle:YB\Organization')->find($request->get('organization_id'));
        if (!in_array($organization, $member->getOrganizations())){
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
    public function makeAdminAction(Request $request, EntityManagerInterface $em, UserInterface $user = null){
        $this->checkIfAuthorized($user);
        $this->changeAdminRight($request, $em, true);
        return $this->redirectToRoute('yb_members_my_organizations');
    }

    /**
     * @Route("/unmake-admin/{organization_id}/{user_id}/", name="yb_members_unmake_admin")
     */
    public function unmakeAdminAction(Request $request, EntityManagerInterface $em, UserInterface $user = null){
        $this->checkIfAuthorized($user);
        $this->changeAdminRight($request, $em, false);
        return $this->redirectToRoute('yb_members_my_organizations');
    }

    /**
     * @Route("/venue/new", name="yb_members_venue_new")
     */
    public function newVenueAction(UserInterface $user = null, Request $request, EntityManagerInterface $em, MailDispatcher $mailDispatcher){
        //TODO
    }

    /**
     * @Route("/confirm-joining-organization/{id}", name="yb_members_confirm_joining_organization")
     */
    public function confirmJoiningOrganization(Organization $org, UserInterface $user = null, EntityManagerInterface $em){
        $this->checkIfAuthorized($user);
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        $request = $em->getRepository('AppBundle:YB\OrganizationJoinRequest')->findByUserAndOrga($org, $currentUser);
        if ($request !== null){
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
    public function renameOrganizationAction(Request $request, EntityManagerInterface $em, UserInterface $user = null, Organization $organization){
        $this->checkIfAuthorized($user);
        $form = $this->createFormBuilder()
            ->add('new_name', TextType::class, ['label' => 'Nouveau nom'])
            ->add('submit', SubmitType::class, ['label' => 'Valider'])
            ->getForm();
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            if ($this->handleRenameOrganization($form, $organization, $em)){
                return $this->redirectToRoute('yb_members_my_organizations');
            }
        }
        return $this->render('@App/YB/Members/rename_organizations.html.twig', [
            'organization' => $organization,
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
    private function hasPendingProjects(Organization $organization, EntityManagerInterface $em){
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
    private function quitOrganization(EntityManagerInterface $em, Organization $org, User $member){
        $em->remove($member->getParticipationToOrganization($org));
    }

    /**
     * Changes the admin right of a user for a given organization
     *
     * @param Request $request
     * @param EntityManagerInterface $em
     * @param $isAdmin
     */
    private function changeAdminRight(Request $request, EntityManagerInterface $em, $isAdmin){
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
    private function handleNewOrganizationRequest(Organization $organization, User $currentUser, EntityManagerInterface $em){
        if ($organization->getName() === $currentUser->getDisplayName()){
            $this->addFlash('error', 'Une organisation ne peut porter le même nom que son administrateur !');
        } elseif ($this->organizationNameExist($em, $organization->getName())){
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
    private function handleAddUserToOrganization(FormInterface $form_add_user, MailDispatcher $mailDispatcher, Organization $org, User $user, EntityManagerInterface $em){
        $data = $form_add_user->getData();
        if ($form_add_user->get('submit')->isClicked()) {
            $guest = $em->getRepository('AppBundle:User')->findOneBy(['username' => $data['email_address']]);
            if ($guest !== null && $org->hasMember($guest)){
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
    private function sendInvitation(OrganizationJoinRequest $invitation, MailDispatcher $mailDispatcher){
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
    private function createNewParticipation(Organization $org, User $user, $isAdmin){
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
    private function createPrivateOrganization(EntityManagerInterface $em, User $currentUser){
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
    private function organizationNameExist(EntityManagerInterface $em, $newName){
        $selfNamedOrg = $em->getRepository('AppBundle:YB\Organization')->findBy(['name' => $newName]);
        if (count($selfNamedOrg) === 0){
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
    private function isNameOfMember(Organization $organization, $newName){
        foreach ($organization->getMembers() as $member){
            if ($member->getDisplayName() === $newName){
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
    private function handleRenameOrganization(FormInterface $form, Organization $organization, EntityManagerInterface $em){
        $new_name = $form->getData()['new_name'];
        if ($this->organizationNameExist($em, $new_name)){
            $this->addFlash('error', 'Une organisation existe déjà avec ce nom. Essayez un autre nom !');
            return false;
        } elseif($this->isNameOfMember($organization, $new_name)){
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
    private function getOrganizationsFromUser(User $currentUser){
        $userOrganizations = $currentUser->getOrganizations();
        usort($userOrganizations, function($organization1, $organization2){
            return strtolower($organization1->getName()) > strtolower($organization2->getName());
        });
        if ($currentUser->isSuperAdmin()){
            $userOrganizations = $this->removeOthersPrivateOrganization($userOrganizations, $currentUser);
        }
        return $userOrganizations;
    }

    /**
     * Remove all the private organization of the other users.
     * By default, a super admin has access to all the organizations of the DB
     * Here, we remove all the private organization that does not belong to him
     *
     * @param $orgs
     * @param $user
     * @return array
     */
    private function removeOthersPrivateOrganization($orgs, $user){
        $finalOrg = [];
        foreach ($orgs as $org){
            if (!$org->isPrivate()){
                $finalOrg[] = $org;
            } else {
                if($org->getName() === $user->getDisplayName()){
                    $finalOrg[] = $org;
                }
            }
        }
        return $finalOrg;
    }

    /**
     * Checks if all the organizations of an array are deleted
     *
     * @param $orgs
     * @return bool
     */
    private function areAllDeleted($orgs){
        foreach ($orgs as $org){
            if (!$org->isDeleted()){
                return false;
            }
        }
        return true;
    }

    private function fetchOrganizationsForSuperUser($currentUser, $organizations){
        $organizations = array_filter($organizations, function($org){
            return !$org->isDeleted();
        });
        $organizationsToBeDisplayed = [];
        foreach ($organizations as $org){
            if ($org->hasMember($currentUser)){
                array_unshift($organizationsToBeDisplayed, $org);
            } else {
                array_push($organizationsToBeDisplayed, $org);
            }
        }
        return $organizationsToBeDisplayed;
    }

}
