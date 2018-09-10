<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Form\YB\YBContractArtistType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

class YBMembersController extends Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function checkIfAuthorized($user, YBContractArtist $campaign = null) {
        if(!$user || !$user instanceof User) {
            throw $this->createAccessDeniedException();
        }
        if($campaign != null && !$user->ownsYBCampaign($campaign)) {
            throw $this->createAccessDeniedException();
        }
    }

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
    public function newCampaignAction(UserInterface $user = null, Request $request, EntityManagerInterface $em) {
        $this->checkIfAuthorized($user);

        $campaign = new YBContractArtist();
        $campaign->addHandler($user);
        $form = $this->createForm(YBContractArtistType::class, $campaign, ['creation' => true]);

        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {
            $em->persist($campaign);
            $em->flush();

            $this->addFlash('yb_notice', 'La campagne a bien été créée.');
            return $this->redirectToRoute('yb_members_dashboard');
        }

        return $this->render('@App/YB/Members/campaign_new.html.twig', [
            'form' => $form->createView(),
            'campaign' => $campaign,
        ]);
    }

    /**
     * @Route("/campaign/{id}/edit", name="yb_members_campaign_edit")
     */
    public function editCampaignAction(YBContractArtist $campaign, UserInterface $user = null, Request $request, EntityManagerInterface $em) {
        $this->checkIfAuthorized($user, $campaign);

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
     * @Route("/campaign/{id}/orders", name="yb_members_campaign_orders")
     */
    public function ordersCampaignAction(YBContractArtist $campaign, UserInterface $user = null) {
        $this->checkIfAuthorized($user, $campaign);

        $cfs = $campaign->getContractsFanPaid();

        return $this->render('@App/YB/Members/campaign_orders.html.twig', [
            'cfs' => $cfs,
            'campaign' => $campaign,
        ]);
    }

}
