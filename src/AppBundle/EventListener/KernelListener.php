<?php

namespace AppBundle\EventListener;

use AppBundle\Controller\ConditionsController;
use AppBundle\Entity\User;
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

        $controller = $this->conditionsController;
        $callable = $event->getController();

        if(is_array($callable) && $callable[0] == $controller)
            return;

        if(!$user instanceof User)
            return;

        $last_conditions = $this->em->getRepository('AppBundle:Conditions')->findLast();

        if(($last_conditions == null) || $user->hasAccepted($last_conditions))
            return;

        $request = $event->getRequest();
        $session = $request->getSession();

        $session->set('requested_url', $request->getRequestUri());

        $event->setController(array($controller, 'acceptLastAction'));
    }

}