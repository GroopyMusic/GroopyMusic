<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Services\MailTemplateProvider;
use Symfony\Component\Security\Core\User\UserInterface;

class PublicController extends Controller
{
    /**
     * @Route("/", name="homepage")
     */
    public function indexAction(Request $request)
    {
        return $this->render('AppBundle:Public:home.html.twig');
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
        //$params['subject'] = $subject;
       // $params['name'] = $recipientName;
        //$params['age'] = 42;
        //$params['message'] = "Happy birthday I wish you all the best!!"
        $locale = "fr";

        $mailer = $this->container->get('azine_email_template_twig_swift_mailer');
        $mailer->sendSingleEmail($user->getEmail(), $user->getDisplayName(), "Test", $params, MailTemplateProvider::VIP_INFO_MAIL_TEMPLATE . ".txt.twig", $locale);

        // TODO envoi du mail (pour l'instant manuel :()

        return $this->render('@App/Test/vip.html.twig');
    }
}
