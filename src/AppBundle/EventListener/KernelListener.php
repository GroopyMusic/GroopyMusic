<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\ConditionsController;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
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
        ];
    }

    public function onController(FilterControllerEvent $event) {

        $user = $this->tokenStorage->getToken()->getUser();

        if($user == null)
            return;

        $last_conditions = $this->em->getRepository('AppBundle:Conditions')->findLast();

        if(($last_conditions == null) || $user->hasAccepted($last_conditions))
            return;

        $request = $event->getRequest();
        $session = $request->getSession();

        $session->set('requested_url', $request->getRequestUri());

        $controller = $this->conditionsController;
        $event->setController(array($controller, 'acceptLastAction'));
    }

}