<?php

namespace AppBundle\Services;

use AppBundle\Entity\Artist;
use AppBundle\Entity\ArtistOwnershipRequest;
use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Payment;
use AppBundle\Entity\PhysicalPersonInterface;
use AppBundle\Entity\PropositionContractArtist;
use AppBundle\Entity\SuggestionBox;
use AppBundle\Entity\User;
use AppBundle\Entity\VIPInscription;
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

    const ADMIN_TO = ["pierre@un-mute.be", "gonzague@un-mute.be"];

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

    private function sendEmail($template, $subject, array $params, array $subject_params, array $bcc_emails, array $attachments = [], array $to = self::TO, $to_name = '', $reply_to = self::REPLY_TO, $reply_to_name = self::REPLY_TO_NAME) {

        // TODO translate for each recipient of course...
        $subject = $this->translator->trans($subject, $subject_params, 'emails');

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

    private function sendAdminEmail($template, $subject, array $params = [], array $subject_params = [], array $attachments = [], $reply_to= self::REPLY_TO, $reply_to_name = self::REPLY_TO_NAME) {
        return $this->sendEmail($template, $subject, $params, $subject_params, [], $attachments, self::ADMIN_TO, '', $reply_to, $reply_to_name);
    }

    public function sendTestEmail() {
        return $this->sendEmail(MailTemplateProvider::ADMIN_TEST_TEMPLATE, 'test', [], [], ['gonzyer@gmail.com']);
    }

    public function sendEmailChangeConfirmation(User $user) {
        $template = MailTemplateProvider::CHANGE_EMAIL_CONFIRMATION_TEMPLATE;

        $params = ['user' => $user];
        $subject_params = [];

        $this->sendEmail($template, "subjects.change_email_confirmation", $params, $subject_params, [], [], [$user->getAskedEmail()], [$user->getDisplayName()]);
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

        $subject_params = [];
        $this->sendEmail($template, "subjects.new_ownership_request", $params, $subject_params, [], [], [$req->getEmail()], [$toName]);
    }

    public function sendSuggestionBoxCopy(SuggestionBox $suggestionBox) {
        $recipient = $suggestionBox->getEmail();
        $recipientName = $suggestionBox->getDisplayName();
        $params = ['suggestionBox' => $suggestionBox];
        $subject_params = [];
        $this->sendEmail(MailTemplateProvider::SUGGESTIONBOXCOPY_TEMPLATE, 'Un-Mute / ' . $suggestionBox->getObject(), $params, $subject_params, [], [], [$recipient], [$recipientName]);
    }

    public function sendVIPInscriptionCopy(VIPInscription $inscription) {
        $recipient = $inscription->getEmail();
        $recipientName = $inscription->getDisplayName();
        $params = ['inscription' => $inscription];
        $subject_params = [];
        $subject = 'Votre inscription Presse sur Un-Mute';

        $this->sendEmail(MailTemplateProvider::VIPINSCRIPTIONCOPY_TEMPLATE, $subject, $params, $subject_params, [], [], [$recipient], [$recipientName]);
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

        $subject_params = [];
        $this->sendEmail($template_artist, 'subjects.concert.artist.known_outcome', $params, $subject_params, $bcc);

        // mail to fans
        if(!empty($fan_users)) {
            $bcc = array_unique(array_map(function(User $elem) {
                return $elem->getEmail();
            }, $fan_users));

            $subject_params = ['%artist%' => $contract->getArtist()->getArtistname()];
            $this->sendEmail($template_fan, 'subjects.concert.fan.known_outcome', $params, $subject_params, $bcc);
        }

        $this->notification_dispatcher->notifyKnownOutcomeContract($artist_users, $contract, true, $success);
        $this->notification_dispatcher->notifyKnownOutcomeContract($fan_users, $contract, false, $success);
    }

    /*
    public function sendOngoingCart($users, ContractArtist $contract) {
        $recipients = array_map(function($elem) {
            return $elem->getEmail();
        }, $users);
        $params = ['contract' => $contract, 'artist' => $contract->getArtist()->getArtistname()];

        $subject_params = [];
        $this->sendEmail(MailTemplateProvider::ONGOING_CART_TEMPLATE, 'Votre panier sur Un-Mute.be', $params, $subject_params, $recipients);
        $this->notification_dispatcher->notifyOngoingCart($users, $contract);
    }
    */

    public function sendArtistReminderContract($users, ContractArtist $contract) {
        $nb_days = (new \DateTime())->diff($contract->getDateEnd())->days;
        $places = $contract->getNbTicketsToSuccess();

        $recipients = array_map(function(User $elem) {
            return $elem->getEmail();
        }, $users);

        $params = ['contract' => $contract, 'days' => $nb_days, 'places' => $places];

        $subject_params = [];
        $this->sendEmail(MailTemplateProvider::REMINDER_CONTRACT_ARTIST_TEMPLATE, 'subjects.concert.artist.reminder', $params, $subject_params, $recipients);
        $this->notification_dispatcher->notifyReminderArtistContract($users, $contract, $nb_days, $places);
    }

    public function sendOrderRecap(ContractFan $contractFan) {
        // TODO should be another way of getting pdf path
        $attachments = ['votreCommande.pdf' => $this->kernel->getRootDir() . '/../web/' . $contractFan->getPdfPath()];

        $to = [$contractFan->getFan()->getEmail()];
        $toName = [$contractFan->getFan()->getDisplayName()];
        $subject = 'subjects.order_recap';
        $params = ['motivations' => $contractFan->getContractArtist()->getMotivations()];
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::ORDER_RECAP_TEMPLATE, $subject, $params, $subject_params, [], $attachments, $to, $toName);
    }

    public function sendTicketsForPhysicalPerson(PhysicalPersonInterface $physicalPerson, ContractArtist $contractArtist, $path) {
        $attachments = ['um-ticket.pdf' => $this->kernel->getRootDir() . '/../web/' . $path];
        $params = ['contract' => $contractArtist];

        $toName = [$physicalPerson->getDisplayName()];
        $to = [$physicalPerson->getEmail()];

        $subject = 'subjects.concert.fan.viptickets';
        $subject_params = ['%artist%' => $contractArtist->getArtist()->getArtistname()];

        $this->sendEmail(MailTemplateProvider::VIP_TICKETS_TEMPLATE, $subject, $params, $subject_params, [], $attachments, $to, $toName);
    }

    public function sendTicketsForContractFan(ContractFan $cf, ContractArtist $ca) {
        $firstParts = $ca->getCoartists();

        $first = null; $second = null;

        if(!empty($firstParts)) {
            if(isset($firstParts[0]))
                $first = $firstParts[0];
            if(isset($firstParts[1]))
                $second = $firstParts[1];
        }

        $params = [
            'artist' => $ca->getArtist(),
            'contract' => $ca,
            'first' => $first,
            'second' => $second,
            'username' => $cf->getFan()->getFirstname(),
        ];

        $attachments = ['um-ticket.pdf' => $this->kernel->getRootDir() . '/../web/' . $cf->getTicketsPath()];

        $to = [$cf->getFan()->getEmail()];
        $toName = [$cf->getFan()->getDisplayName()];

        $subject = 'subjects.concert.fan.tickets';
        $subject_params = ['%artist%' => $ca->getArtist()->getArtistname()];

        $this->sendEmail(MailTemplateProvider::TICKETS_TEMPLATE, $subject, $params, $subject_params, [], $attachments, $to, $toName);
    }

    public function sendRefundedPayment(Payment $payment) {
        $params = [
            'payment' => $payment,
        ];

        $to = [$payment->getUser()->getEmail()];
        $toName = [$payment->getUser()->getDisplayName()];

        $subject = 'subjects.refunded_payment';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::REFUNDED_PAYMENT_TEMPLATE, $subject, $params, $subject_params, [], [], $to, $toName);
    }

    public function sendArtistValidated(Artist $artist) {
        $params = [
            'artist' => $artist,
        ];

        $to = array_map(function(User $user) {
            return $user->getEmail();
        }, $artist->getOwners());

        $toName = [];

        $subject = 'subjects.artist_validated';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::ARTIST_VALIDATED_TEMPLATE, $subject, $params, $subject_params, [], [], $to, $toName);
    }

    // ----------------------
    // ADMIN EMAILS
    // ----------------------

    public function sendAdminNewArtist(Artist $artist) {
        $params = ['artist' => $artist];
        $subject = 'Nouvel artiste inscrit sur Un-Mute';
        $subject_params = [];

        $this->sendAdminEmail(MailTemplateProvider::ADMIN_NEW_ARTIST, $subject, $params, $subject_params);
    }

    public function sendAdminContact(SuggestionBox $suggestionBox) {
        $params = ['suggestionBox' => $suggestionBox];

        $reply_to = $suggestionBox->getEmail() ?: self::REPLY_TO;
        $reply_to_name = $suggestionBox->getDisplayName() ?: '';

        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_CONTACT_FORM, 'Un-Mute / ' . $suggestionBox->getObject(), $params, $subject_params, [], $reply_to, $reply_to_name);
    }

    public function sendAdminVIPInscription(VIPInscription $inscription) {
        $params = ['inscription' => $inscription];
        $subject_params = [];
        $subject = 'Nouvelle inscription Presse';

        $this->sendAdminEmail(MailTemplateProvider::ADMIN_VIP_INSCRIPTION_FORM, $subject, $params, $subject_params);
    }

    public function sendAdminTicketsSent(ContractArtist $contractArtist) {
        $params = ['contract' => $contractArtist];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_TICKETS_SENT, 'Tickets envoyés pour le concert de ' . $contractArtist->getArtist()->getArtistname(), $params, $subject_params);
    }

    public function sendAdminReminderContract(ContractArtist $contract, $nb_days) {
        $subject = "Rappel : un contrat doit être concrétisé";
        $params = ['contractArtist' => $contract, 'nbDays' => $nb_days];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_REMINDER_CONTRACT_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminPendingContract(ContractArtist $contract) {
        $subject = "La récolte de tickets d'un événement est arrivée à échéance";
        $params = ['contractArtist' => $contract];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_PENDING_CONTRACT_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminNewlySuccessfulContract(ContractArtist $contract) {
        $subject = "Un événement a atteint le seuil pour être concrétisé";
        $params = ['contractArtist' => $contract];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_NEWLY_SUCCESSFUL_CONTRACT_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminEnormousPayer(User $user) {
        $subject = "Payeur énorme spotted";
        $params = ['user' => $user];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_ENORMOUS_PAYER_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminStripeError(\Exception $e, User $user, Cart $cart) {
        $subject = "Erreur lors d'un paiement Stripe";
        $params = ['stripe_error' => $e, 'user' => $user, 'cart' => $cart];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_STRIPE_ERROR_TEMPLATE, $subject, $params, $subject_params);
    }
    public function sendAdminProposition(PropositionContractArtist $propositionContractArtist){
        $subject = "Soumission de proposition";
        $params = ['contact_person' => $propositionContractArtist->getContactPerson(), 'event' => $propositionContractArtist];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_PROPOSITION_SUBMIT, $subject, $params, $subject_params);
    }

    /*
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
        $subject_params = [];
        $this->sendEmail(MailTemplateProvider::DETAILS_KNOWN_CONTRACT_ARTIST_TEMPLATE, 'subjects.concert.artist.details', $params, $subject_params, $bcc);
    }
    */
}
