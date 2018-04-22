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

    public function __construct(TokenStorageInterface $tokenStorage, EntityManagerInterface $em, ConditionsController $conditionsController)
    {
        $this->tokenStorage = $tokenStorage;
        $this->em = $em;
        $this->conditionsController = $conditionsController;
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

        $token = $this->tokenStorage->getToken();
        if($token == null) {
            return;
        }
        $user = $token->getUser();

        $controller = $this->conditionsController;
        $callable = $event->getController();

        if(is_array($callable) && $callable[0] == $controller)
            return;

        if(!$user instanceof User)
            return;

        $request = $event->getRequest();
        $session = $request->getSession();

        $user->setPreferredLocale($request->getLocale());

        $last_conditions = $this->em->getRepository('AppBundle:Conditions')->findLast();

        if(($last_conditions == null) || $user->hasAccepted($last_conditions))
            return;

        $session->set('requested_url', $request->getRequestUri());

        $event->setController(array($controller, 'acceptLastAction'));
    }

    /**
     * @param FilterResponseEvent $event
     *  Actions on response :
     *  - flush manager to ensure that no persisted entities (by services e.g.) is forgotten
     */

    public function onResponse(FilterResponseEvent $event)
    {
        $this->em->flush();
        $response = $event->getResponse();
    }

}