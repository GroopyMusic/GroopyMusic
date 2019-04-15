<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\ConditionsController;
use AppBundle\Controller\YBController;
use AppBundle\Controller\YBMembersController;
use AppBundle\Entity\User;
use AppBundle\Exception\UMAuthenticationException;
use AppBundle\Exception\YBAuthenticationException;
use AppBundle\Services\UserManager;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Controller\SecurityController;
use FOS\UserBundle\FOSUserBundle;
use Psr\Log\LoggerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use XBundle\Controller\XArtistController;
use XBundle\Controller\XPublicController;
use XBundle\Exception\NoAuthenticationException;
use XBundle\Exception\NotArtistOwnerException;

class KernelListener implements EventSubscriberInterface
{
    private $tokenStorage;
    private $em;
    private $conditionsController;
    private $securityController;
    private $YBMembersController;
    private $YBController;
    private $session_name;
    private $remember_me_name;
    private $router;
    private $XPublicController;
    private $XArtistController;
    private $userManager;
    private $logger;
    private $yb = false;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em, ConditionsController $conditionsController, SecurityController $securityController, YBMembersController $YBMembersController, YBController $YBController, RouterInterface $router, $session_name, $remember_me_name, XArtistController $XArtistController, XPublicController $XPublicController, UserManager $userManager, LoggerInterface $logger, RequestStack $requestStack)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->conditionsController = $conditionsController;
        $this->securityController = $securityController;
        $this->YBController = $YBController;
        $this->YBMembersController = $YBMembersController;
        $this->session_name = $session_name;
        $this->remember_me_name = $remember_me_name;
        $this->router = $router;
        $this->XArtistController = $XArtistController;
        $this->XPublicController = $XPublicController;
        $this->userManager = $userManager;
        $this->logger = $logger;
        $request = $requestStack->getParentRequest();


    }
    public static function getSubscribedEvents() {
        return [
            KernelEvents::CONTROLLER => 'onController',
            KernelEvents::RESPONSE => 'onResponse',
            KernelEvents::EXCEPTION => 'onException',
            KernelEvents::REQUEST => [['onRequest', 3000]],
        ];
    }

    public function onRequest(GetResponseEvent $event) {
        $request = $event->getRequest();
        $event->getRequest()->getUri();
        $this->logger->log('critical', $event->getRequest()->getUri());
        if(strpos($event->getRequest()->getUri(), '/yb/') !== false || strpos($event->getRequest()->getUri(), 'ticked-it.be') !== false) {
            $this->yb = true;
            $this->userManager->setYB(true);
            $this->em->getRepository('AppBundle:User')->setYB(true);
        }
        $this->logger->log('critical', $request->get('_route'));
    }

    public function onException(GetResponseForExceptionEvent $event) {
        $exception = $event->getException();
        $request = $event->getRequest();
        $session = $request->getSession();

        if($exception instanceof UMAuthenticationException) {
            // Logging user out.
            $this->tokenStorage->setToken(null);

            // Invalidating the session.
            $session->invalidate();

            $cookieNames = [
                $this->session_name,
                $this->remember_me_name,
            ];

            $response = new RedirectResponse($this->router->generate($request->get('_route'), $request->get('_route_params')));

            foreach ($cookieNames as $cookieName) {
                $response->headers->clearCookie($cookieName);
            }

            $event->setResponse($response);
        }

        // YB
        if($exception instanceof YBAuthenticationException) {
            // Logging user out.
            $this->tokenStorage->setToken(null);

            // Invalidating the session.
            $session->invalidate();

            $cookieNames = [
                $this->session_name,
                $this->remember_me_name,
            ];
            
            $response = new RedirectResponse($this->router->generate('yb_login'));
            
            foreach ($cookieNames as $cookieName) {
                $response->headers->clearCookie($cookieName);
            }

            $session->getFlashBag()->add('yb_error', "Votre compte n'est pas autorisé pour Ticked-it ; il faut qu'un administrateur Un-Mute vous donne les privilèges nécessaires.");
            
            $event->setResponse($response);
        }

        // X - if not authenticate
        if($exception instanceof NoAuthenticationException) {
            // Logging user out.
            $this->tokenStorage->setToken(null);

            // Invalidating the session.
            $session->invalidate();

            $cookieNames = [
                $this->session_name,
                $this->remember_me_name,
            ];
            
            $response = new RedirectResponse($this->router->generate('x_login'));
            
            foreach ($cookieNames as $cookieName) {
                $response->headers->clearCookie($cookieName);
            }

            $session->getFlashBag()->add('yb_error', "Votre compte n'est pas autorisé pour Chapots. Pour vous connecter, assurez vous d'avoir un compte sur Un-Mute");
            
            $event->setResponse($response);
        }

        // X - if not artist owner
        if($exception instanceof NotArtistOwnerException) {
            $response = new RedirectResponse($this->router->generate('x_homepage'));

            $session->getFlashBag()->add('yb_error', "Accès refusé si vous ne gérez pas d'artiste!");
            
            $event->setResponse($response);
        }
    }

    /**
     * @param FilterControllerEvent $event
     *
     * Actions on controller :
     * - redirect to a page on which users need to accept new conditions if the terms of use of the website changed since their last session
     */
    public function onController(FilterControllerEvent $event) {

        $request = $event->getRequest();
        $session = $request->getSession();
        $session->set('requested_url', $request->getRequestUri());
        $callable = $event->getController();

        $yb = false;
        if((is_array($callable) && ($callable[0] == $this->YBMembersController || $callable[0] == $this->YBController) || $request->get('_route') == 'fos_user_security_check_yb') || $request->get('_route') == 'yb_login') {
            $yb = true;
            $this->yb = true;
        }
        $this->em->getRepository('AppBundle:User')->setYB($this->yb);
        $this->userManager->setYB($this->yb);

        $yb_text = $yb ? 'true':'false';
        $session->set('yb', $yb_text);

        $this->logger->log('critical', 'Voici la route : ' . $request->get('_route') . ' et le YB : ' . $yb);

        $token = $this->tokenStorage->getToken();

        if($token == null) {
            return;
        }
        $user = $token->getUser();

        if(!$user instanceof User) {
            return;
        }

        if($user->isYB() != $yb) {
            // Logging user out.
            $this->tokenStorage->setToken(null);

            // Invalidating the session.
            $session->invalidate();

            throw new UMAuthenticationException();
        }

        if(!$yb) {
            $controller = $this->conditionsController;

            if(is_array($callable) && $callable[0] == $controller)
                return;

            $user->setPreferredLocale($request->getLocale());
            $last_conditions = $this->em->getRepository('AppBundle:Conditions')->findLast();

            if(($last_conditions == null))
                return;

            $has_accepted = $this->em->getRepository('AppBundle:User_Conditions')->findBy(['conditions' => $last_conditions, 'user' => $user]);
            if(count($has_accepted) > 0)
                return;

            $event->setController(array($controller, 'acceptLastAction'));
        }
    }

    /**
     * @param FilterResponseEvent $event
     *  Actions on response :
     *  - flush manager to ensure that no persisted entities (by services e.g.) is forgotten
     */

    public function onResponse(FilterResponseEvent $event)
    {
        $response = $event->getResponse();

        /*if($this->tokenStorage->getToken() == null) {
            // Clearing the cookies.
            $cookieNames = [
                $this->session_name,
                $this->remember_me_name,
            ];
            foreach ($cookieNames as $cookieName) {
                $response->headers->clearCookie($cookieName);
            }
        }*/

        $this->em->flush();
    }

}