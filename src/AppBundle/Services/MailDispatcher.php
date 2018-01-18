<?php

namespace AppBundle\Services;

use AppBundle\Entity\Artist;
use AppBundle\Entity\ArtistOwnershipRequest;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Newsletter;
use AppBundle\Entity\SuggestionBox;
use AppBundle\Entity\User;
use AppBundle\Repository\SuggestionTypeEnumRepository;
use Azine\EmailBundle\Services\AzineTwigSwiftMailer;
use Doctrine\ORM\EntityManagerInterface;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;

class MailDispatcher
{
    const MAX_BCC = 100;

    const TO = ["no-reply@un-mute.be"];

    const REPLY_TO = "pierre@un-mute.be";
    const REPLY_TO_NAME = "Un-Mute ASBL";

    const ADMIN_BCC = ["pierre@un-mute.be", "gonzague@un-mute.be"];

    private $mailer;
    private $from_address;
    private $from_name;
    private $translator;
    private $notification_dispatcher;
    private $em;
    private $kernel;
    private $twig;

    public function __construct(AzineTwigSwiftMailer $mailer, Translator $translator, NotificationDispatcher $notificationDispatcher, EntityManagerInterface $em, $from_address, $from_name, KernelInterface $kernel, Environment $twig)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->notification_dispatcher = $notificationDispatcher;
        $this->em = $em;
        $this->from_address = $from_address;
        $this->from_name = $from_name;
        $this->kernel = $kernel;
        $this->twig = $twig;
    }

    private function sendEmail($template, $subject, array $params, array $bcc_emails, array $attachments = [], array $to = self::TO, $to_name = '', $reply_to = self::REPLY_TO, $reply_to_name = self::REPLY_TO_NAME) {

        // CASE 1 : # of recipients is reasonable -> one mail
        if(count($to) + count($bcc_emails) <= self::MAX_BCC) {
            $this->mailer->sendEmail($failedRecipients, $subject, $this->from_address, $this->from_name, $to, $to_name, [], '',
                $bcc_emails, '', $reply_to, $reply_to_name, array_merge(['subject' => $subject], $params), $template, $attachments);
            return $failedRecipients;
        }

        // CASE 2 : # of recipients is high and "to" field is for no-reply only
        // We need to chunk the bcc recipients
        elseif($to == self::TO) {
            $failedRecipients = array();
            $bcc_chunks = array_chunk($bcc_emails, self::MAX_BCC);

            foreach($bcc_chunks as $chunk)  {
                $this->mailer->sendEmail($newFailedRecipients, $subject, $this->from_address, $this->from_name, $to, $to_name, [], '',
                    $chunk, '', $reply_to, $reply_to_name, array_merge(['subject' => $subject], $params), $template, $attachments);
                $failedRecipients = array_merge($failedRecipients, $newFailedRecipients);
            }
        }

        // CASE 3 : # of recipients is high and "to" fields is actually used
        // We need to separate BCC and TO and send e-mails in chunks
        // This case shouldn't happen in practice
        else {
            $failedRecipients = array();
            $bcc_chunks = array_chunk($bcc_emails, self::MAX_BCC);

            foreach($bcc_chunks as $chunk)  {
                $this->mailer->sendEmail($newFailedRecipients, $subject, $this->from_address, $this->from_name, self::TO, '', [], '',
                    $chunk, '', $reply_to, $reply_to_name, array_merge(['subject' => $subject], $params), $template, $attachments);
                $failedRecipients = array_merge($failedRecipients, $newFailedRecipients);
            }

            $to_chunks = array_chunk($to, self::MAX_BCC);

            foreach($to_chunks as $chunk)  {
                $this->mailer->sendEmail($newFailedRecipients, $subject, $this->from_address, $this->from_name, $chunk, '', [], '',
                    '', '', $reply_to, $reply_to_name, array_merge(['subject' => $subject], $params), $template, $attachments);
                $failedRecipients = array_merge($failedRecipients, $newFailedRecipients);
            }
        }

        return $failedRecipients;
    }

    private function sendAdminEmail($template, $subject, array $params = [], array $attachments = [], $reply_to= self::REPLY_TO, $reply_to_name = self::REPLY_TO_NAME) {
        return $this->sendEmail($template, $subject, $params, self::ADMIN_BCC, $attachments, [], '', $reply_to, $reply_to_name);
    }

    public function sendTestEmail() {
        return $this->sendEmail(MailTemplateProvider::ADMIN_TEST_TEMPLATE, 'test', [], ['gonzyer@gmail.com']);
    }

    public function sendEmailChangeConfirmation(User $user) {
        $template = MailTemplateProvider::CHANGE_EMAIL_CONFIRMATION_TEMPLATE;

        $params = ['user' => $user];

        $this->sendEmail($template, "Changement d'e-mail", $params, [], [], [$user->getAskedEmail()], [$user->getDisplayName()]);
    }


    public function sendNewOwnershipRequest(Artist $artist, ArtistOwnershipRequest $req) {
        $params = ['artist' => $artist, 'request' => $req];

        $toName = '';

        $possible_user = $this->em->getRepository('AppBundle:User')->findOneBy(['email'=>$req->getEmail()]);
        if($possible_user != null) {
            $template = MailTemplateProvider::OWNERSHIPREQUEST_MEMBER_TEMPLATE;
            $params['user'] = $possible_user->getEmail();
            $toName = $possible_user->getDisplayName();
        }
        else {
            $template = MailTemplateProvider::OWNERSHIPREQUEST_NONMEMBER_TEMPLATE;
        }

        $this->sendEmail($template, "Quelqu'un a dit que vous possédiez un artiste sur Un-Mute", $params, [], [], [$req->getEmail()], [$toName]);
    }

    public function sendSuggestionBoxCopy(SuggestionBox $suggestionBox) {
        $recipient = $suggestionBox->getEmail();
        $recipientName = $suggestionBox->getDisplayName();
        $params = ['suggestionBox' => $suggestionBox];
        $this->sendEmail(MailTemplateProvider::SUGGESTIONBOXCOPY_TEMPLATE, 'Un-Mute / ' . $suggestionBox->getObject(), $params, [], [], [$recipient], [$recipientName]);
    }

    public function sendKnownOutcomeContract(ContractArtist $contract, $success) {
        $artist_users = $contract->getArtistProfiles();
        $fan_users = $contract->getFanProfiles();

        $params = ['contract' => $contract, 'artist' => $contract->getArtist()];

        if($success) {
            $template_artist = MailTemplateProvider::SUCCESSFUL_CONTRACT_ARTIST_TEMPLATE;
            $template_fan = MailTemplateProvider::SUCCESSFUL_CONTRACT_FAN_TEMPLATE;
        } else {
            $template_artist = MailTemplateProvider::FAILED_CONTRACT_ARTIST_TEMPLATE;
            $template_fan = MailTemplateProvider::FAILED_CONTRACT_FAN_TEMPLATE;
        }

        // mail to artists
        $bcc = array_map(function(User $elem) {
            return $elem->getEmail();
        }, $artist_users);

        $this->sendEmail($template_artist, 'Votre événement Un-Mute - Résultat des courses', $params, $bcc);

        // mail to fans
        if(!empty($fan_users)) {
            $bcc = array_unique(array_map(function(User $elem) {
                return $elem->getEmail();
            }, $fan_users));

            $this->sendEmail($template_fan, 'Concert de ' . $contract->getArtist()->getArtistname() . ' : résultat des courses', $params, $bcc);
        }

        $this->notification_dispatcher->notifyArtistsKnownOutcomeContract($artist_users, $contract, $success);
        $this->notification_dispatcher->notifyFansKnownOutcomeContract($fan_users, $contract, $success);
    }

    public function sendNewsletter(Newsletter $newsletter, $recipients) {
        $params = ['newsletter' => $newsletter];
        $this->sendEmail(MailTemplateProvider::NEWSLETTER_TEMPLATE, $newsletter->getTitle(), $params, $recipients);
    }

    public function sendOngoingCart($users, ContractArtist $contract) {
        $recipients = array_map(function($elem) {
            return $elem->getEmail();
        }, $users);
        $params = ['contract' => $contract, 'artist' => $contract->getArtist()->getArtistname()];
        $this->sendEmail(MailTemplateProvider::ONGOING_CART_TEMPLATE, 'Votre panier sur Un-Mute.be', $params, $recipients);
        $this->notification_dispatcher->notifyOngoingCart($users, $contract);
    }

    public function sendArtistReminderContract($users, ContractArtist $contract, $nb_days) {

        $places = $contract->getStep()->getMinTickets() - $contract->getTicketsSold();

        $recipients = array_map(function(User $elem) {
            return $elem->getEmail();
        }, $users);
        $params = ['contract' => $contract, 'nbDays' => $nb_days, 'artist' => $contract->getArtist()->getArtistname(), 'places' => $places];
        $this->sendEmail(MailTemplateProvider::REMINDER_CONTRACT_ARTIST_TEMPLATE, 'Rappel : votre événement sur Un-Mute.be', $params, $recipients);
        $this->notification_dispatcher->notifyReminderArtistContract($users, $contract, $nb_days, $places);
    }

    public function sendOrderRecap(ContractFan $contractFan) {
        // TODO should be another way of getting pdf path
        $attachments = ['votreCommande.pdf' => $this->kernel->getRootDir() . '/../web/pdf/orders/' . $contractFan->getBarcodeText().'.pdf'];

        $to = [$contractFan->getFan()->getEmail()];
        $toName = [$contractFan->getFan()->getDisplayName()];
        $subject = "Votre commande sur Un-Mute.be";
        $params = [];

        $this->sendEmail(MailTemplateProvider::ORDER_RECAP_TEMPLATE, $subject, $params, [], $attachments, $to, $toName);
    }

    public function sendDetailsKnownArtist(ContractArtist $contractArtist) {
        $users = $contractArtist->getArtistProfiles();
        // mail to artists
        $bcc = array_map(function(User $elem) {
            return $elem->getEmail();
        }, $users);

        $firstParts = $contractArtist->getCoartists();

        $first = $firstParts[0] ?: null;
        $second = $firstParts[1] ?: null;

        $params = [
            'artist' => $contractArtist->getArtist(),
            'contract' => $contractArtist,
            'first' => $first, 
            'second' => $second,
        ];

        $this->sendEmail(MailTemplateProvider::DETAILS_KNOWN_CONTRACT_ARTIST_TEMPLATE, 'Les détails de votre concert', $params, $bcc);

    }

    public function sendDetailsKnownFan(ContractArtist $contractArtist, $cf = null) {
        $firstParts = $contractArtist->getCoartists();

        $first = $firstParts[0] ?: null;
        $second = $firstParts[1] ?: null;

        $params = [
            'artist' => $contractArtist->getArtist(),
            'contract' => $contractArtist,
            'first' => $first,
            'second' => $second,
        ];

        $html2pdf = new Html2Pdf();

        $cfs = $cf == null ? $contractArtist->getContractsFan() : [$cf];

        foreach($cfs as $contractFan) {

            $contractFan->generateTickets();

            $html2pdf->writeHTML($this->twig->render('@App/PDF/tickets.html.twig', ['contractFan' => $contractFan]));
            $html2pdf->output($contractFan->getTicketsPath(), 'F');

            $attachments = ['votreTicket.pdf' => $this->get('kernel')->getRootDir() . '/../web/' . $contractFan->getTicketsPath()];

            $to = [$contractFan->getFan()->getEmail()];
            $toName = [$contractFan->getFan()->getDisplayName()];
            $subject = "Votre ticket Un-Mute";

            $this->sendEmail(MailTemplateProvider::DETAILS_KNOWN_CONTRACT_FAN_TEMPLATE, $subject, $params, [], $attachments, $to, $toName);
            $this->notification_dispatcher->notifyTicket($contractFan->getFan(), $contractFan);
        }

        if($cf == null) {
            $this->sendAdminTicketsSent($contractArtist);
            $contractArtist->setTicketsSent(true);
        }
    }

    public function sendAdminContact(SuggestionBox $suggestionBox) {
        $params = ['suggestionBox' => $suggestionBox];

        $reply_to = $suggestionBox->getEmail() ?: self::REPLY_TO;
        $reply_to_name = $suggestionBox->getDisplayName() ?: '';

        $this->sendAdminEmail(MailTemplateProvider::ADMIN_CONTACT_FORM, 'Un-Mute / ' . $suggestionBox->getObject(), $params, [], $reply_to, $reply_to_name);
    }

    public function sendAdminTicketsSent(ContractArtist $contractArtist) {
        $params = ['contract' => $contractArtist];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_TICKETS_SENT, 'Tickets envoyés pour le concert de ' . $contractArtist->getArtist()->getArtistname(), $params);
    }

    public function sendAdminReminderContract(ContractArtist $contract, $nb_days) {
        $subject = "Rappel : un contrat doit être concrétisé";
        $params = ['contractArtist' => $contract, 'nbDays' => $nb_days];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_REMINDER_CONTRACT_TEMPLATE, $subject, $params);
    }

    public function sendAdminPendingContract(ContractArtist $contract) {
        $subject = "La récolte de tickets d'un événement est arrivée à échéance";
        $params = ['contractArtist' => $contract];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_PENDING_CONTRACT_TEMPLATE, $subject, $params);
    }

    public function sendAdminEnormousPayer(User $user) {
        $subject = "Payeur énorme spotted";
        $params = ['user' => $user];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_ENORMOUS_PAYER_TEMPLATE, $subject, $params);
    }

    public function sendAdminStripeError(\Stripe\Error\Base $e, User $user, Cart $cart) {
        $subject = "Erreur lors d'un paiement Stripe";
        $params = ['stripe_error' => $e, 'user' => $user, 'cart' => $cart];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_STRIPE_ERROR_TEMPLATE, $subject, $params);
    }
}