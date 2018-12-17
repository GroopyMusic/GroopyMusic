<?php

namespace AppBundle\Controller;

use AppBundle\Form\MailFormType;
use AppBundle\Services\MailAdminService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MailAdminController extends BaseAdminController
{
    public function listAction()
    {
        $form = $this->createForm(MailFormType::class);
        return $this->render('@App/Admin/MailSystem/mail_admin_system.html.twig', array('form' => $form->createView()));
    }

    public function getMembersAction(Request $request, MailAdminService $mailAdminService)
    {
        try {
            $members = $mailAdminService->fillArtistOwnersArray($request->get('options'));
            return new JsonResponse($members);
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);
        }
    }

    public function getUserParticipantsAction(Request $request, MailAdminService $mailAdminService)
    {
        try {
            $user_participants = $mailAdminService->fillParticipantsArray($request->get('options'));
            return new JsonResponse($user_participants);
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);
        }
    }

    public function getArtistParticipantsAction(Request $request, MailAdminService $mailAdminService)
    {
        try {
            $artist_participants = $mailAdminService->fillArtistParticipantsArray($request->get('options'));
            return new JsonResponse($artist_participants);
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);
        }
    }

    public function sendEmailAction(Request $request, MailAdminService $mailAdminService)
    {
        try {
            $mailAdminService->sendEmail($request->get('recipients'), $request->get('object'), $request->get('content'));
            return new Response("Success", 200, []);
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);
        }
    }

    public function getSummaryAction(Request $request, MailAdminService $mailAdminService)
    {
        try {
            $recipients = $request->get('recipients');
            $userSummary = $mailAdminService->getUsersSummary($recipients);
            $simpleEmailSummary = $mailAdminService->getSimpleEmails($recipients);
            $template = '@App/Admin/MailSystem/modal_summary_preview.html.twig';
            return $this->render($template, array(
                'users' => $userSummary,
                'emails' => $simpleEmailSummary,
                'object' => $request->get('object'),
                'content' => $request->get('content')
            ));
        } catch (\Throwable $th) {
            return new Response($th->getMessage(), 500, []);
        }
    }
}