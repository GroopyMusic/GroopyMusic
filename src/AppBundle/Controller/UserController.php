<?php

namespace AppBundle\Controller;

use Azine\EmailBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends Controller
{
    /**
     * @Route("/inbox", name="user_notifications")
     */
    public function notifsAction(Request $request, UserInterface $user)
    {
        $notifs = $this->getDoctrine()->getRepository('AzineEmailBundle:Notification')->findBy(array('recipient_id' => $user->getId()));

        /*$notifs = array();
        foreach($notifications as $n) {
            if($n->getSent() != null) {
                $notifs[] = $n;
            }
        }*/

        return $this->render('@App/User/notifications.html.twig', array(
            'notifs' => $notifs,
        ));
    }

    /**
     * @Route("/inbox/notifications/{id}", name="user_notification")
     */
    public function notifAction(Notification $notif, Request $request, UserInterface $user) {
        if($notif->getRecipientId() != $user->getId()) {
            throw $this->createAccessDeniedException();
        }

        return $this->render('@App/User/notification.html.twig', array(
            'notif' => $notif,
        ));
    }
}
