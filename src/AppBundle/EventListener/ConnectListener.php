<?php

namespace AppBundle\EventListener;

use HWI\Bundle\OAuthBundle\Event\GetResponseUserEvent;
use HWI\Bundle\OAuthBundle\HWIOAuthEvents;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Listener responsible to change the redirection at the end of the password resetting
 */
class ConnectListener implements EventSubscriberInterface {
    private $router;
    private $session;
    private $translator;

    public function __construct(UrlGeneratorInterface $router, Session $session, TranslatorInterface $translator) {
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
    }

    public static function getSubscribedEvents() {
        return [
            HWIOAuthEvents::CONNECT_CONFIRMED => 'onConnectConfirmed',
            HWIOAuthEvents::CONNECT_COMPLETED => 'onConnectCompleted',
            HWIOAuthEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        ];
    }

    public function addSessionMessage($message) {
        $this->session->getFlashBag()->add('notice', $message);
    }

    // Connection through email
    public function onConnectConfirmed(GetResponseUserEvent $event) {
        $is_new_user = boolval($event->getRequest()->query->get('oauth_new_user'));

        $message = $this->translator->trans('notices.social.connection_email_confirmed');

        $params = ['%email%' => $event->getUser()->getUsername()];
        if($is_new_user) {
            $message .= ' ' . $this->translator->trans('notices.social.connection_email_new_account', $params);
        }

        $this->addSessionMessage($message);
    }

    // Connection through facebook_id
    public function onConnectCompleted(Event $event) {
        $this->addSessionMessage('notices.social.connection_oauth_confirmed');
    }

    // Registration
    public function onRegistrationSuccess(Event $event) {
        $this->addSessionMessage('notices.social.registration_success');
    }
}