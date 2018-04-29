<?php
/**
 * Created by PhpStorm.
 * User: jcochart
 * Date: 25/04/2018
 * Time: 22:47
 */

namespace AppBundle\Services;


use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\SponsorshipInvitation;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use FOS\UserBundle\Util\TokenGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class SponsorshipService
{
    private $em;

    private $logger;

    private $mailDispatcher;

    private $token_gen;

    public function __construct(MailDispatcher $mailDispatcher, EntityManagerInterface $em, LoggerInterface $logger, TokenGeneratorInterface $token_gen)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailDispatcher = $mailDispatcher;
        $this->token_gen = $token_gen;
    }

    public function sendSponsorshipInvitation($emails, $content, ContractArtist $contractArtist, User $user)
    {
        if (count($emails) == 0) {
            throw new \Exception();
        }
        $verifiedEmails = $this->verifyEmails($emails);
        $emails = $verifiedEmails[0];
        if (count($emails) == 0) {
            return [false, $verifiedEmails[1]];
        }
        foreach ($emails as $email) {
            $sponsorship_invitation = new SponsorshipInvitation(new \DateTime(), $email, $content, $user,
                $contractArtist, $user->getId() . $this->token_gen->generateToken() . $contractArtist->getId());
            $this->em->persist($sponsorship_invitation);
            $this->logger->warning("test", [$sponsorship_invitation]);
            $this->mailDispatcher->sendSponsorshipInvitationEmail($sponsorship_invitation, $content);
        }
        $this->em->flush();
        return [true, $verifiedEmails[1]];
    }

    private function verifyEmails($emails)
    {
        $userRepository = $this->em->getRepository('AppBundle:User');
        $sponsorshipInvitationRepository = $this->em->getRepository('AppBundle:SponsorshipInvitation');
        $clearedEmails = [];
        $knownEmail = [];
        $this->logger->warning('emails', $emails);
        foreach ($emails as $email) {
            if (strlen(trim($email)) == 0) {
                continue;
            }
            if ($userRepository->emailExists($email) == null) {
                $this->logger->warning('emails', [$email]);
                $this->logger->warning('emails', [$userRepository->emailExists($email)]);
                if (!in_array($email, $clearedEmails)) {
                    array_push($clearedEmails, $email);
                }
            } else {
                if (!in_array($email, $knownEmail)) {
                    array_push($knownEmail, $email);
                }
            }
        }
        return [$clearedEmails, $knownEmail];
    }

    public function checkSponsorship(User $user)
    {
        var_dump($user);
        $sponsorship = $this->em->getRepository('AppBundle:SponsorshipInvitation')->getSponsorshipInvitationByMail($user->getEmail());
        if ($sponsorship != null) {
            $this->em->persist($sponsorship);
            $sponsorship->setTargetInvitation($user);
        }
    }

}