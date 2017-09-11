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
            new \Twig_SimpleFunction('unseen_notifs_nb', array($this, 'unseen_notifs_nb')),
        );
    }

    public function render_notification(Notification $notification) {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        return $this->renderer->renderNotif($notification, $locale);
    }

    public function preview_notification(Notification $notification) {
        $locale = $this->requestStack->getCurrentRequest()->getLocale();
        return $this->renderer->renderPreviewNotif($notification, $locale);
    }

    public function unseen_notifs_nb(User $user = null) {
        if($user == null)
            $user = $this->token_storage->getToken()->getUser();
        return $this->dispatcher->getUnseenNb($user);
    }
}