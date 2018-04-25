<?php
/**
 * Created by PhpStorm.
 * User: jcochart
 * Date: 25/04/2018
 * Time: 22:47
 */

namespace AppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class SponsorshipService
{
    private $em;

    private $logger;

    private $mailDispatcher;

    public function __construct(MailDispatcher $mailDispatcher, EntityManagerInterface $em, LoggerInterface $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailDispatcher = $mailDispatcher;
    }

    public function sendSponsorshipInvitation($emails, $content)
    {
        $emails = $this->verifyEmails($emails);
        if (count($emails) == 0) {
            return false;
        }
        $this->mailDispatcher->sendSponsorshipInvitationEmail($emails, $content);
        return true;
    }

    private function verifyEmails($emails)
    {
        $userRepository = $this->em->getRepository('AppBundle:User');
        $clearedEmails = [];
        $this->logger->warning('emails', $emails);
        foreach ($emails as $email) {
            if ($userRepository->emailExists($email) == null) {
                $this->logger->warning('emails', [$userRepository->emailExists($email)]);
                array_push($clearedEmails, $email);
            }
        }
        return array_unique($clearedEmails);
    }

}