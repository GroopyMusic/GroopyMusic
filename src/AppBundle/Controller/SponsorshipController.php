<?php

namespace AppBundle\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\Translation\TranslatorInterface;

class SponsorshipController extends Controller
{
    protected $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Validation of a sponsorship request by the invited email
     * @Route("/sponsorship-link-token-{token}", name="sponsorship_link")
     */
    public function sponsorshipLinkAction(Request $request, UserInterface $current_user = null, LoggerInterface $logger, TranslatorInterface $translator, TokenStorageInterface $tokenStorage)
    {
        try {
            if ($current_user != null) {
                $tokenStorage->setToken(null);
                $session = $request->getSession();
                $session->invalidate();

                $cookieNames = [
                    $this->getParameter('session.name'),
                    $this->getParameter('session.remember_me.name'),
                ];
            }
            $em = $this->getDoctrine()->getManager();
            $token = $request->get('token');
            $sponsorship = $em->getRepository('AppBundle:SponsorshipInvitation')->getSponsorshipInvitationByToken($token);
            if ($sponsorship == null) {
                $this->addFlash('error', $translator->trans('notices.sponsorship.link.error', []));
                return $this->redirectToRoute('homepage');
            } else {
                $em->persist($sponsorship);
                $sponsorship->setLastDateAcceptation(new \DateTime());

                $response = new RedirectResponse($this->generateUrl('sponsorship_link_valid', array("id" => $sponsorship->getContractArtist()->getId())));

                if (isset($cookieNames)) {
                    foreach ($cookieNames as $cookieName) {
                        $response->headers->clearCookie($cookieName);
                    }
                }
                return $response;
            }

        } catch (\Throwable $th) {
            $this->addFlash('error', $translator->trans('notices.sponsorship.link.error', []));
            return $this->redirectToRoute('homepage');
        }
    }

    /** 
     * @Route("/on-sponsorship-link-valid-{id}", name="sponsorship_link_valid") 
     */
    public function onSponsorshipLinkValidAction(TranslatorInterface $translator, $id) {
        $this->addFlash('notice', $translator->trans('notices.sponsorship.link.success', []));
        return $this->redirectToRoute('artist_contract', array("id" => $id));
    }
}