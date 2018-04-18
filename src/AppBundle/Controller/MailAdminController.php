<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 12/04/2018
 * Time: 10:47
 */

namespace AppBundle\Controller;

use AppBundle\Entity\Artist;
use AppBundle\Form\MailFormType;
use AppBundle\Services\MailAdminService;
use Psr\Log\LoggerInterface;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MailAdminController extends Controller
{
    protected $container;

    private $logger;

    public function __construct(ContainerInterface $container, LoggerInterface $logger)
    {
        $this->container = $container;
        $this->configure();
        $this->logger = $logger;
    }

    public function listAction()
    {
        $form = $this->createForm(MailFormType::class);
        return $this->render('@App/Admin/MailSystem/mail_admin_system.html.twig', array('form' => $form->createView()));
    }

    public function getMembersAction(Request $request, MailAdminService $mailAdminService)
    {
        $members = $mailAdminService->fillMembersArray($request->get('options'));
        return null;
    }

    public function getUserParticipantsAction(Request $request, MailAdminService $mailAdminService)
    {
        $user_participants = $mailAdminService->fillParticipantsArray($request->get('options'));
        return new JsonResponse($user_participants);
    }

    public function getArtistParticipantsAction(Request $request, MailAdminService $mailAdminService)
    {
        $artist_participants = $mailAdminService->fillArtistParticipantsArray($request->get('options'));
        return new JsonResponse($artist_participants);
    }

    public function sendEmailAction(Request $request, MailAdminService $mailAdminService)
    {
        $mailAdminService->sendEmail($request->get('recipients'), $request->get('object'), $request->get('content'));
        return new JsonResponse([]);
    }
}