<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Entity\Artist_User;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractArtistSales;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Hall;
use AppBundle\Entity\PropositionContractArtist;
use AppBundle\Entity\User;
use AppBundle\Entity\SuggestionBox;
use AppBundle\Form\CartType;
use AppBundle\Form\ContractFanType;
use AppBundle\Form\PropositionContractArtistType;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\RewardSpendingService;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\Event\GetResponseUserEvent;
use FOS\UserBundle\Form\Factory\FactoryInterface;
use FOS\UserBundle\FOSUserEvents;
use FOS\UserBundle\Model\UserManagerInterface;
use Mailgun\Mailgun;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\BufferedOutput;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use AppBundle\Form\SuggestionBoxType;
use AppBundle\Form\UserSuggestionBoxType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Translation\TranslatorInterface;
use AppBundle\Services\ArrayHelper;

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