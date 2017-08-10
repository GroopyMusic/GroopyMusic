<?php

namespace AppBundle\Services;

use Azine\EmailBundle\Services\AzineTwigSwiftMailer;
use Azine\EmailBundle\Services\NotifierServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class ReminderContractArtist  {

    private $em;
    private $notifier;
    private $mailer;
    private $logger;

    public function __construct(EntityManagerInterface $em, NotifierServiceInterface $notifier, AzineTwigSwiftMailer $mailer, LoggerInterface $logger) {
        $this->em = $em;
        $this->notifier = $notifier;
        $this->mailer = $mailer;
        $this->logger = $logger;
    }

    public function sendMailsForXDays($days) {

        $currentContracts = $this->em->getRepository('AppBundle:ContractArtist')->findCurrents();
        $currentDate = new \DateTime();

        $result = ['notifs' => 0, 'mails' => 0];

        foreach($currentContracts as $contract) {
            $reminder = false;

            if((($contract->getReminders() < 1 && $days == 30) || ($contract->getReminders() < 2 && $days == 15))
                && $currentDate->diff($contract->getDateEnd())->days <= $days ) {
                $reminder = true;
            }

            if($reminder) {
                $artist_users = $contract->getArtist()->getArtistsUser();
                $users = array();

                foreach($artist_users as $au) {
                    $user = $au->getUser();
                    $users[] = $user->getEmail();

                    // Notification creation
                    $title = $days . " days until mdrz !";
                    $content = "Wouhouuu";
                    $recipientId = $user->getId();

                    $this->notifier->addNotificationMessage($recipientId, $title, $content);
                    $result['notifs']++;
                }

                $from = "no-reply@un-mute.be";
                $fromName = "Un-Mute";

                $bcc = "gonzyer@gmail.com";
                $bccName = "Webmaster";

                $replyTo = "gonzyer@gmail.com";
                $replyToName = "Webmaster";

                $params = ['contract' => $contract, 'days' => $days, 'artist' => $contract->getArtist()->getArtistName()];

                $this->mailer->sendEmail($failedRecipients, "Sujet", $from, $fromName, $users, '', '', '',
                    $bcc, $bccName, $replyTo, $replyToName, $params, MailTemplateProvider::REMINDER_CONTRACT_ARTIST_TEMPLATE);

                $result['mails']++;

                $contract->setReminders($contract->getReminders() + 1);
                $this->em->persist($contract);
            }
        }

        $this->em->flush();
        return $result;
    }
}
