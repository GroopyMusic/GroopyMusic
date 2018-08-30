<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\ConditionsController;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class KernelListener implements EventSubscriberInterface
{
    private $tokenStorage;
    private $em;
    private $conditionsController;
    private $session_name;
    private $remember_me_name;

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em, ConditionsController $conditionsController, $session_name, $remember_me_name)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->conditionsController = $conditionsController;
        $this->session_name = $session_name;
        $this->remember_me_name = $remember_me_name;
    }

    public static function getSubscribedEvents() {
        return [
            KernelEvents::CONTROLLER => 'onController',
            KernelEvents::RESPONSE => 'onResponse',
        ];
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

        $host = $request->getHttpHost();

        $yb = false;
        if(($host == 'localhost' && strpos($request->getRequestUri(), 'yb') !== false) || (strpos(strtoupper($host), 'TICKED-IT') !== false)) {
            $this->em->getRepository('AppBundle:User')->yb = 1;
            $yb = true;
        }

        $token = $this->tokenStorage->getToken();
        if($token == null) {
            return;
        }
        $user = $token->getUser();

        if(!$user instanceof User) {
            return;
        }

        if($yb != $user->isYB()) {
            // Logging user out.
            $this->tokenStorage->setToken(null);

            // Invalidating the session.
            $session->invalidate();
            return;
        }


        if(!$yb) {
            $controller = $this->conditionsController;
            $callable = $event->getController();

            if(is_array($callable) && $callable[0] == $controller)
                return;

            $user->setPreferredLocale($request->getLocale());
            $last_conditions = $this->em->getRepository('AppBundle:Conditions')->findLast();

            if(!$user->isYB() && ($last_conditions == null) || $user->hasAccepted($last_conditions))
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

        if($this->tokenStorage->getToken() == null) {
            // Clearing the cookies.
            $cookieNames = [
                $this->session_name,
                $this->remember_me_name,
            ];
            foreach ($cookieNames as $cookieName) {
                $response->headers->clearCookie($cookieName);
            }
        }

        $this->em->flush();
    }

}