<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\Step;
use AppBundle\Entity\User;
use AppBundle\Services\MailTemplateProvider;
use Azine\EmailBundle\Entity\Notification;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\User\UserInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class UserController extends Controller
{
    /**
     * @Route("/home", name="default_security_target")
     */
    public function homeAction(Request $request, UserInterface $user) {

        // TODO sécuriser cette route tout comme tout le reste de l'espace membres

        return $this->redirectToRoute('fan_home');
    }

    /**
     * @Route("/testmail", name="testmail")
     */
    public function testmailAction(Request $request, UserInterface $user) {
        // get all elements used for the notification email
        $title = "You have won the lottery!";
        $content = "Congratulation John! You have won 7'000'000$ in the lottery";
        $goToUrl = "http://www.acmelottery.com/claim/you/money";
        $recipientId = $user->getId();

        // get your implementation of the AzineNotifierService
        $notifierService = $this->get('email.notifier_service');
        $notifierService->addNotificationMessage($recipientId, $title, $content, $goToUrl);

        $params = array();
        $params['subject'] = $title;
        $params['name'] = $user->getUsername();
        $params['age'] = 42;
        $params['message'] = "Happy birthday I wish you all the best!!";
        $locale = "fr";

        $mailer = $this->get('azine_email_template_twig_swift_mailer');
        $mailer->sendSingleEmail($user->getEmail(), $user->getDisplayName(), "Test", $params, MailTemplateProvider::VIP_INFO_MAIL_TEMPLATE . ".txt.twig", $locale);

        // TODO envoi du mail (pour l'instant manuel)

        return $this->render('@App/Public/home.html.twig');
    }

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

    /**
     * @Route("/see-contract-{id}", name="user_see_contract")
     */
    public function seeContractAction(ContractArtist $contract) {

        $current = new \DateTime();
        $done = $contract->getDateEnd() < $current;

        return $this->render('@App/User/artist_contract.html.twig', array(
            'contract' => $contract,
            'done' => $done,
        ));
    }

    /**
     * @Route("/profile-{id}", name="user_see_profile")
     */
    public function seeProfileAction(User $user) {
        return $this->render('@App/User/profile.html.twig', array(
            'user' => $user,
        ));
    }
}
