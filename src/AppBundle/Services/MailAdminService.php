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

    /**
     * Retrieves all email adresses of artist
     * @param $artists (ids)
     * @return array members
     */
    public function fillArtistOwnersArray($artists)
    {
        $members = [];
        foreach ($artists as $artist_id) {
            $artits_users = $this->em->getRepository('AppBundle:Artist_User')->getArtistOwners($artist_id);
            foreach ($artits_users as $artist_user) {
                array_push($members, ['email' => $artist_user->getUser()->getEmail(), 'id' => $artist_user->getUser()->getId()]);
            }
        }
        return $members;
    }

    /**
     * get all the email addresses of the participants of an event
     *
     * @param $contract_artists
     * @return array
     */
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

    /**
     * Retrieves all email addresses of artists participating in an event
     * @param $contract_artists_id
     * @return array
     */
    public function fillArtistParticipantsArray($contract_artists_id)
    {
        $artist_participants = [];
        foreach ($contract_artists_id as $contract_artist_id) {
            $contractArtist = $this->em->getRepository('AppBundle:ContractArtist')->getArtistParticipants($contract_artist_id);
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

    /**
     * Sends an email to all users, email addresses and admin (in copy) without duplicates
     *
     * @param $recipients
     * @param $object
     * @param $content
     */
    public function sendEmail($recipients, $object, $content)
    {
        $arrayRecipients = $this->addAdminToRecipients($this->constructArrayRecipients($recipients));
        $simpleEmails = $this->getSimpleEmails($recipients);
        $emails = [];
        foreach ($arrayRecipients as $recipient) {
            if (!array_key_exists($recipient->getEmail(), $emails)) {
                $emails[$recipient->getEmail()] = $recipient->getPreferredLocale();
            }
        }
        foreach ($simpleEmails as $email) {
            if (!array_key_exists($email, $emails)) {
                $emails[$email] = $this->translator->getLocale();
            }
        }
        $this->mailDispatcher->sendEmailFromAdmin($emails, $object, $content);
    }

    /**
     * Retrieves all email addresses from the key 'input_email' ( email entered manually) in the table
     *
     * @param $recipients
     * @return array
     */
    public function getSimpleEmails($recipients)
    {
        $simpleEmails = [];
        if (array_key_exists('emails_input', $recipients)) {
            foreach ($recipients['emails_input'] as $email) {
                if (!in_array($email, $simpleEmails) && strlen(trim($email)) != 0) {
                    array_push($simpleEmails, $email);
                }
            }
        }
        return $simpleEmails;
    }

    /**
     * Retrieves a summary of all email addresses to which an email will be sent
     *
     * @param $recipients
     * @return array
     */
    public function getUsersSummary($recipients)
    {
        $userSummary = $this->addAdminToRecipients($this->constructArrayRecipients($recipients));
        return array_unique($userSummary, SORT_REGULAR);
    }

    /**
     * Construct the recipient user table according to the @param $recipients array
     *
     * @param $recipients
     * @return array
     */
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
                            $u = $this->em->getRepository('AppBundle:User')->find($id);
                            if($u != null)
                            array_push($arrayRecipients,$u );
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
                            $u = $this->em->getRepository('AppBundle:User')->find($id);
                            if($u != null)
                                array_push($arrayRecipients,$u );
                        }
                    }
                    break;
                case "emails_input";
                    break;
                default:
                    foreach ($recipient as $id) {
                        $u = $this->em->getRepository('AppBundle:User')->find($id);
                        if($u != null)
                            array_push($arrayRecipients,$u );
                    }
                    break;
            }
        }
        return $arrayRecipients;
    }

    /**
     * Add admin users to the table passed as parameters
     * @param $recipients
     * @return mixed
     */
    public function addAdminToRecipients($recipients)
    {
        $users = $this->em->getRepository('AppBundle:User')->findUsersWithRoles(['ROLE_SUPER_ADMIN']);
        foreach ($users as $user) {
            array_push($recipients, $user);
        }
        return $recipients;
    }
}