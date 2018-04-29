<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 16/04/2018
 * Time: 15:46
 */

namespace AppBundle\Services;


use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class MailAdminService
{

    private $em;

    private $logger;

    private $mailDispatcher;

    private $translator;


    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, MailDispatcher $mailDispatcher, Translator $translator)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailDispatcher = $mailDispatcher;
        $this->translator = $translator;
    }

    public function fillMembersArray($artists)
    {
        $members = [];
        foreach ($artists as $artist_id) {
            $artits_users = $this->em->getRepository('AppBundle:Artist_User')->getMembers($artist_id);
            foreach ($artits_users as $artist_user) {
                array_push($members, ['email' => $artist_user->getUser()->getEmail(), 'id' => $artist_user->getUser()->getId()]);
            }
        }
        return $members;
    }

    public function fillParticipantsArray($contract_artists)
    {
        $user_participants = [];
        foreach ($contract_artists as $contract_artist) {
            $users = $this->em->getRepository('AppBundle:User')->getParticipants($contract_artist);
            foreach ($users as $user) {
                array_push($user_participants, ['email' => $user->getEmail(), 'id' => $user->getId()]);
            }
        }
        return $user_participants;
    }

    public function fillArtistParticipantsArray($contract_artists_id)
    {
        $artist_participants = [];
        foreach ($contract_artists_id as $contract_artist_id) {
            $contractArtist = $this->em->getRepository('AppBundle:ContractArtist')->getArtistParticipants($contract_artist_id);
            $this->logger->warning('ro', [$contractArtist]);
            foreach ($contractArtist->getArtist()->getArtistsUser()->toArray() as $artist_user) {
                array_push($artist_participants, ['email' => $artist_user->getUser()->getEmail(), 'id' => $artist_user->getUser()->getId()]);
            }
            foreach ($contractArtist->getCoartistsList()->toArray() as $co_artist) {
                foreach ($co_artist->getArtist()->getArtistsUser()->toArray() as $artist_user) {
                    array_push($artist_participants, ['email' => $artist_user->getUser()->getEmail(), 'id' => $artist_user->getUser()->getId()]);
                }
            }
        }
        return $artist_participants;
    }

    public function sendEmail($recipients, $object, $content)
    {
        $arrayRecipients = $this->addAdminToRecipients($this->constructArrayRecipients($recipients));
        $simpleEmails = $this->getSimpleEmails($recipients);
        $emails = [];
        foreach ($recipients as $recipient) {
            $arrayRecipients[$recipient->getEmail()] = $recipient->getPreferredLocale();
        }
        foreach ($simpleEmails as $email) {
            $emails[$email] = $this->translator->getLocale();
        }
        $emails = array_unique(array_merge($emails, $simpleEmails));
        $this->mailDispatcher->sendEmailFromAdmin($emails, $object, $content);
    }

    public function getSimpleEmails($recipients)
    {
        $simpleEmails = [];
        if (array_key_exists('emails_input', $recipients)) {
            foreach ($recipients['emails_input'] as $email) {
                if (!in_array($email, $simpleEmails)) {
                    array_push($simpleEmails, $email);
                }
            }
        }
        return $simpleEmails;
    }

    public function getUsersSummary($recipients)
    {
        $userSummary = $this->addAdminToRecipients($this->constructArrayRecipients($recipients));
        return array_unique($userSummary, SORT_REGULAR);
    }

    public function constructArrayRecipients($recipients)
    {
        $arrayRecipients = [];
        foreach ($recipients as $key => $recipient) {
            switch ($key) {
                case "users":
                    if ($recipient == 'all') {
                        $users = $this->em->getRepository('AppBundle:User')->findUsersNotDeletedForSelect([]);
                        foreach ($users as $user) {
                            array_push($arrayRecipients, $user);
                        }
                    } else {
                        foreach ($recipient as $id) {
                            array_push($arrayRecipients, $this->em->getRepository('AppBundle:User')->find($id));
                        }
                    }
                    break;
                case "newsletter_users":
                    if ($recipient == 'all') {
                        $users = $this->em->getRepository('AppBundle:User')->findNewsletterUsersNotDeletedForSelect([]);
                        foreach ($users as $user) {
                            array_push($arrayRecipients, $user);
                        }
                    } else {
                        foreach ($recipient as $id) {
                            array_push($arrayRecipients, $this->em->getRepository('AppBundle:User')->find($id));
                        }
                    }
                    break;
                case "emails_input";
                    break;
                default:
                    foreach ($recipient as $id) {
                        array_push($arrayRecipients, $this->em->getRepository('AppBundle:User')->find($id));
                    }
                    break;
            }
        }
        return $arrayRecipients;
    }

    public function addAdminToRecipients($recipients)
    {
        $users = $this->em->getRepository('AppBundle:User')->findUsersWithRoles(['ROLE_SUPER_ADMIN']);
        foreach ($users as $user) {
            array_push($recipients, $user);
        }
        return $recipients;
    }
}