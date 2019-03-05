<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\YBTransactionalMessage;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\Participation;
use AppBundle\Form\UserBankAccountType;
use AppBundle\Form\YB\YBContractArtistCrowdType;
use AppBundle\Form\YB\YBContractArtistType;
use AppBundle\Form\YB\YBTransactionalMessageType;
use AppBundle\Form\YB\OrganizationType;
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

        $current_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getCurrentYBCampaigns($user);
        $passed_campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getPassedYBCampaigns($user);

        return $this->render('@App/YB/Members/dashboard.html.twig', [
            'current_campaigns' => $current_campaigns,
            'passed_campaigns' => $passed_campaigns,
        ]);
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
        
        /*$currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        $userOrganizations = $currentUser->getOrganizations();
        $ownNameOrg = new Organization();
        $ownNameOrg->setName($currentUser->getDisplayName());
        array_unshift($userOrganizations, $ownNameOrg);*/

        $form = $this->createForm(YBContractArtistType::class, $campaign, ['creation' => true, /*'userOrganizations' => $userOrganizations*/]);

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
     * @Route("/facturation", name="yb_members_payment_options")
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
     * @Route("/my-organizations", name="yb_members_my_organizations")
     */
    public function myOrganizationsAction(EntityManagerInterface $em, UserInterface $user = null, Request $request, MailDispatcher $mailDispatcher){
        
        // regarder si il a les autorisations pour accéder à la page
        //$this->checkIfAuthorized($user);

        // récupérer toutes ses organisations
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        $organizationsToBeDisplayed = $currentUser->getPublicOrganizations();

        // init form pour la création d'une nouvelle organisation
        $organization = new Organization();
        $form = $this->createForm(OrganizationType::class, $organization);

        // creation form pour l'ajout d'une personne
        $defaultData = ['email_adress' => 'Adresse e-mail de la personne'];
        $form_add_user = $this->createFormBuilder($defaultData)
            ->add('email_address', TextType::class, ['label' => 'Adresse e-mail'])
            ->add('submit', SubmitType::class, ['label' => 'Ajouter'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()){
            $participation = new Participation();
            $participation->setAdmin(true);
            $currentUser->addParticipation($participation);
            $organization->addParticipation($participation);
            $participation->setRole();
            $em->persist($organization);
            $em->flush();
            $orgName = $organization->getName();
            $this->addFlash('yb_notice', 'Votre nouvelle organisation, '. $orgName .', a bien été enregistrée.');
            return $this->redirectToRoute('yb_members_my_organizations');
        }

        if ($form_add_user->isSubmitted() && $form->isValid){
            $data = $form_add_user->getData();
            if ($form_add_user->get('submit')->isClicked()) {
                // TODO
                return $this->redirectToRoute('yb_members_my_organizations');
            }
        }

        // renvoyer vers la page
        return $this->render('@App/YB/Members/my_organizations.html.twig', [
            'organizations' => $organizationsToBeDisplayed,
            'form' => $form->createView(),
            'currentUser' => $currentUser,
            'newMemberForm' => $form_add_user->createView(),
        ]);
    }

    /**
     * @Route("/delete-organization/{id}", name="yb_members_delete_organization")
     */
    public function deleteOrganizationAction(Organization $org, UserInterface $user = null, EntityManagerInterface $em){
        $currentUser = $em->getRepository('AppBundle:User')->find($user->getId());
        if ($org->hasOnlyOneMember()){
            if (!$this->hasPendingProjects($org)){
                $em->remove($currentUser->getParticipationToOrganizaton($org));
            } else {
                $this->addFlash('yb_notice', 'Votre organisation a encore des projets et vous êtes le seul membre restant ! Vous ne pouvez pas quitter le navire en pleine mer !');
            }
        } else {
            if ($org->hasAtLeastOneAdmin($currentUser)){
                $em->remove($currentUser->getParticipationToOrganizaton($org));
            } else {
                $this->addFlash('yb_notice', 'Si vous partez, il n\'y a plus de maître à bord ! Désigner d\'abord un administrateur avant de partir !');
            }
        }
        $em->flush();
        return $this->redirectToRoute('yb_members_my_organizations');
    }

    private function hasPendingProjects(Organization $organization){
        return false;
    }

    /**
     * @Route("/remove-from-organization/{id}", name="yb_members_remove_from_organization")
     */
    public function removeFromOrganizationAction(){

    }

    /**
     * @Route("/make-admin/{organization_id}/{user_id}/", name="yb_members_make_admin")
     */
    public function makeAdminAction(){
        
    }

    /**
     * @Route("/unmake-admin/{organization_id}/{user_id}/", name="yb_members_unmake_admin")
     */
    public function unmakeAdminAction(){
        
    }


}
