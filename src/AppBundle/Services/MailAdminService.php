<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 16/04/2018
 * Time: 15:46
 */

namespace AppBundle\Services;


use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class MailAdminService
{

    private $em;

    private $logger;

    private $mailDispatcher;

    public function __construct(EntityManagerInterface $em, LoggerInterface $logger, MailDispatcher $mailDispatcher)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailDispatcher = $mailDispatcher;
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
        $emails = $this->constructArrayEmails($recipients);
        $emails = array_unique($emails);
        $this->logger->warning("pute", [$emails, $object, $content]);
        $this->mailDispatcher->sendEmailFromAdmin($emails, $object, $content);
    }

    private function constructArrayEmails($recipients)
    {
        $emails = [];
        foreach ($recipients as $key => $recipient) {
            switch ($key) {
                case "users":
                    if ($recipient == 'all') {
                        $users = $this->em->getRepository('AppBundle:User')->findUsersNotDeletedForSelect([]);
                        foreach ($users as $user) {
                            array_push($emails, $user->getEmail());
                        }
                    } else {
                        foreach ($recipient as $id) {
                            array_push($emails, $this->em->getRepository('AppBundle:User')->find($id)->getEmail());
                        }
                    }
                    break;
                case "newsletter_users":
                    if ($recipient == 'all') {
                        $users = $this->em->getRepository('AppBundle:User')->findNewsletterUsersNotDeletedForSelect([]);
                        foreach ($users as $user) {
                            array_push($emails, $user->getEmail());
                        }
                    } else {
                        foreach ($recipient as $id) {
                            array_push($emails, $this->em->getRepository('AppBundle:User')->find($id)->getEmail());
                        }
                    }
                    break;
                case "emails_input":
                    foreach ($recipient as $email) {
                        array_push($emails, $email);
                    }
                    break;
                default:
                    foreach ($recipient as $id) {
                        array_push($emails, $this->em->getRepository('AppBundle:User')->find($id)->getEmail());
                    }
                    break;
            }
        }
        return $emails;
    }
}