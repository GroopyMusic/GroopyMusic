<?php

namespace AppBundle\Services;

use AppBundle\Entity\Notification;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class NotificationExtension extends \Twig_Extension
{
    private $em;
    private $renderer;
    private $dispatcher;
    private $requestStack;
    private $token_storage;

    public function __construct(EntityManagerInterface $em, NotificationRenderer $renderer, NotificationDispatcher $dispatcher, RequestStack $requestStack, TokenStorageInterface $token_storage)
    {
        $this->em = $em;
        $this->renderer = $renderer;
        $this->dispatcher = $dispatcher;
        $this->requestStack = $requestStack;
        $this->token_storage = $token_storage;
    }

    public function getFunctions() {
        return array(
            new \Twig_SimpleFunction('render_notification', array($this, 'render_notification')),
            new \Twig_SimpleFunction('preview_notification', array($this, 'preview_notification')),
            new \Twig_SimpleFunction('preview_menu_notification', array($this, 'preview_menu_notification')),
            new \Twig_SimpleFunction('unseen_notifs_nb', array($this, 'unseen_notifs_nb')),
            new \Twig_SimpleFunction('last_notifs', array($this, 'last_notifs')),
        );
    }

    // Returns last x notifs
    public function last_notifs($x = 5) {
        $user = $this->token_storage->getToken()->getUser();
        return $this->em->getRepository('AppBundle:Notification')->findBy(['user' => $user], ['seen' => 'asc', 'date' => 'desc'], $x);
    }

    public function render_notification(Notification $notification) {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        return $this->renderer->renderNotif($notification, $locale, false);
    }

    public function preview_notification(Notification $notification) {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        return $this->renderer->renderNotif($notification, $locale, true);
    }

    public function preview_menu_notification(Notification $notification) {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        return $this->renderer->renderNotif($notification, $locale, false, true);
    }

    public function unseen_notifs_nb(User $user = null) {
        if($user == null)
            $user = $this->token_storage->getToken()->getUser();
        return $this->dispatcher->getUnseenNb($user);
    }
}