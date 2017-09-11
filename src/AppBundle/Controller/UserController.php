<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Notification;
use AppBundle\Entity\Step;
use AppBundle\Entity\User;
use AppBundle\Services\MailNotifierService;
use AppBundle\Services\MailTemplateProvider;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Spipu\Html2Pdf\Html2Pdf;

class UserController extends Controller
{
    /**
     * @Route("/inbox", name="user_notifications")
     */
    public function notifsAction(Request $request, UserInterface $user)
    {
        $notifs = $this->getDoctrine()->getRepository('AppBundle:Notification')->findBy(array('user' => $user));

        return $this->render('@App/Fan/notifications.html.twig', array(
            'notifs' => $notifs,
        ));
    }

    /**
     * @Route("/inbox/notifications/{id}", name="user_notification")
     */
    public function notifAction(Notification $notif, Request $request, UserInterface $user) {
        if($notif->getUser() != $user) {
            throw $this->createAccessDeniedException();
        }

        $em = $this->getDoctrine()->getManager();

        $notif->setSeen(true);
        $em->persist($notif);
        $em->flush();

        return $this->render('@App/Fan/notification.html.twig', array(
            'notif' => $notif,
        ));
    }
}
