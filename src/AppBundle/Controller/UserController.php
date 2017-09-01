<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Step;
use AppBundle\Entity\User;
use AppBundle\Services\MailNotifierService;
use AppBundle\Services\MailTemplateProvider;
use Azine\EmailBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Spipu\Html2Pdf\Html2Pdf;

class UserController extends Controller
{
    /**
     * @Route("/testmail", name="testmail")
     */
    public function testmailAction(Request $request, UserInterface $user) {
        // get all elements used for the notification email
        $title = "You have won the lottery!";
        $content = "Congratulation John! You have won 7'000'000$ in the lottery";
        $recipientId = $user->getId();

        // get your implementation of the AzineNotifierService
        $notifierService = $this->get(MailNotifierService::class);
        $notifierService->addNotificationMessage($recipientId, $title, $content);

        $from = "no-reply@un-mute.be";
        $fromName = "Un-Mute";

        $bcc = "gonzyer@gmail.com";
        $bccName = "Webmaster";

        $replyTo = "gonzyer@gmail.com";
        $replyToName = "Webmaster";

        $params = [];

        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($this->renderView('AppBundle:PDF:ticket.html.twig', array()));
        $html2pdf->Output('pdf/contracts/contrat-x.pdf', 'F');

        $attachments = ['votreContrat.pdf' => $this->get('kernel')->getRootDir() . '\..\web\pdf\contracts\contrat-x.pdf'];

        $mailer = $this->get('azine_email_template_twig_swift_mailer');
        $mailer->sendEmail($failedRecipients, "Sujet", $from, $fromName, $user->getEmail(), $user->getDisplayName(), '', '',
            $bcc, $bccName, $replyTo, $replyToName, $params, MailTemplateProvider::VIP_INFO_MAIL_TEMPLATE, $attachments, 'fr');

        return $this->render('@App/Public/home.html.twig');
    }

    /**
     * @Route("/inbox", name="user_notifications")
     */
    public function notifsAction(Request $request, UserInterface $user)
    {
        $notifs = $this->getDoctrine()->getRepository('AzineEmailBundle:Notification')->findBy(array('recipient_id' => $user->getId()));

        return $this->render('@App/Fan/notifications.html.twig', array(
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

        return $this->render('@App/Fan/notification.html.twig', array(
            'notif' => $notif,
        ));
    }
}
