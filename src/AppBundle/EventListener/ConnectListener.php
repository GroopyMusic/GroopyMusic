<?php

namespace AppBundle\EventListener;

use AppBundle\Entity\User_Conditions;
use AppBundle\Services\SponsorshipService;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Event\FilterUserResponseEvent;
use FOS\UserBundle\Event\FormEvent;
use FOS\UserBundle\FOSUserEvents;
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
    private $em;
    private $sponsorshipService;

    public function __construct(UrlGeneratorInterface $router, Session $session, TranslatorInterface $translator, EntityManagerInterface $em, SponsorshipService $sponsorshipService) {
        $this->router = $router;
        $this->session = $session;
        $this->translator = $translator;
        $this->em = $em;
        $this->sponsorshipService = $sponsorshipService;
    }

    public static function getSubscribedEvents() {
        return [
            HWIOAuthEvents::CONNECT_CONFIRMED => 'onSocialConnectConfirmed',
            HWIOAuthEvents::CONNECT_COMPLETED => 'onSocialConnectCompleted',
            HWIOAuthEvents::REGISTRATION_SUCCESS => 'onSocialRegistrationSuccess',
            FOSUserEvents::REGISTRATION_COMPLETED => 'onRegistrationCompleted',
            FOSUserEvents::REGISTRATION_SUCCESS => 'onRegistrationSuccess',
        ];
    }

    public function addSessionMessage($message) {
        $this->session->getFlashBag()->add('notice', $message);
    }

    // Connection through email
    public function onSocialConnectConfirmed(GetResponseUserEvent $event) {
        $is_new_user = boolval($event->getRequest()->query->get('oauth_new_user'));

        $message = $this->translator->trans('notices.social.connection_email_confirmed');

        $params = ['%email%' => $event->getUser()->getUsername()];
        if($is_new_user) {
            $message .= ' ' . $this->translator->trans('notices.social.connection_email_new_account', $params);
        }

        $this->addSessionMessage($message);
    }

    // Connection through facebook_id
    public function onSocialConnectCompleted(Event $event) {
        $this->addSessionMessage('notices.social.connection_oauth_confirmed');
    }

    // Registration with Facebook
    public function onSocialRegistrationSuccess(Event $event,\HWI\Bundle\OAuthBundle\Event\FormEvent $formEvent) {
        $this->addSessionMessage('notices.social.registration_success');
        $this->sponsorshipService->checkSponsorship($formEvent->getForm()->getData());
    }

    // Registration with FOSUserBundle -> completed
    public function onRegistrationSuccess(FormEvent $event) {
        $event->getForm()->getData()->setPreferredLocale($this->translator->getLocale());
        $this->sponsorshipService->checkSponsorship($event->getForm()->getData());
    }

    // Registration with FOSUserBundle -> completed
    public function onRegistrationCompleted(FilterUserResponseEvent $event) {
        $last_terms = $this->em->getRepository('AppBundle:Conditions')->findLast();
        $user_conditions = new User_Conditions($event->getUser(), $last_terms);

        $this->em->persist($user_conditions);
        $this->em->flush();
    }
}