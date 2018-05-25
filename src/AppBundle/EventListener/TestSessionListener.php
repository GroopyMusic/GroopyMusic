<?php

namespace AppBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\EventListener\TestSessionListener as BaseTestSessionListener;

//Functional Test --> Use In memory databse + session need to disable reboot
// If disableReboot --> error : "Cannot set session ID after the session has started"
//This listener + (line service in config_test) resolve the problem
// solute found on https://github.com/symfony/symfony/issues/13450

class TestSessionListener extends BaseTestSessionListener
{
    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * TestRequestListener constructor.
     * @param ContainerInterface $container
     */
    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * @return null|SessionInterface
     */
    protected function getSession() : ?SessionInterface
    {
        if (!$this->container->has('session')) {
            return null;
        }

        return $this->container->get('session');
    }

    /**
     * @param GetResponseEvent $event
     */
    public function onKernelRequest(GetResponseEvent $event)
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        // bootstrap the session
        $session = $this->getSession();
        if (!$session) {
            return;
        }

        $cookies = $event->getRequest()->cookies;

        if ($cookies->has($session->getName())) {
            if (!$session->isStarted()) {
                $session->setId($cookies->get($session->getName()));
            }
        }
    }
}