<?php

namespace AppBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use AppBundle\Services\MailTemplateProvider;

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
    public function testmailAction(Request $request) {
        // get all elements used for the notification email
        $title = "You have won the lottery!";
        $content = "Congratulation John! You have won 7'000'000$ in the lottery";
        $goToUrl = "http://www.acmelottery.com/claim/you/money";
        $recipientId = 8; // user id

        // get your implementation of the AzineNotifierService
        $notifierService = $this->get('email.notifier_service');
        $notifierService->addNotificationMessage($recipientId, $title, $content, $goToUrl);

        // TODO envoi du mail (pour l'instant manuel :()

        return $this->render('@App/Test/vip.html.twig');
    }
}
