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
use Azine\EmailBundle\Services\AzineTwigSwiftMailer;
use Doctrine\ORM\EntityManagerInterface;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpKernel\KernelInterface;

class MailDispatcher
{
    const TO = "no-reply@un-mute.be";

    const REPLY_TO = "info@un-mute.be";
    const REPLY_TO_NAME = "Un-Mute ASBL";

    const ADMIN_TO = "gonzyer@gmail.com";
    const ADMIN_TO_NAME = "Admin Un-Mute";

    private $mailer;
    private $from_address;
    private $from_name;
    private $translator;
    private $notification_dispatcher;
    private $em;
    private $kernel;

    public function __construct(AzineTwigSwiftMailer $mailer, Translator $translator, NotificationDispatcher $notificationDispatcher, EntityManagerInterface $em, $from_address, $from_name, KernelInterface $kernel)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->notification_dispatcher = $notificationDispatcher;
        $this->em = $em;
        $this->from_address = $from_address;
        $this->from_name = $from_name;
        $this->kernel = $kernel;
    }

    private function sendEmail($template, $subject, array $params, $bcc_emails, array $attachments = [], $to = self::TO, $to_name = '') {
        $this->mailer->sendEmail($failedRecipients, $subject, $this->from_address, $this->from_name, $to, $to_name, [], '',
            $bcc_emails, '', self::REPLY_TO, self::REPLY_TO_NAME, array_merge(['subject' => $subject], $params), $template, $attachments);
        return $failedRecipients;
    }

    private function sendAdminEmail($template, $subject, array $params = [], array $attachments = []) {
        return $this->sendEmail($template, $subject, $params, [], $attachments, self::ADMIN_TO, self::ADMIN_TO_NAME);
    }

    public function sendTestEmail() {
        return $this->sendEmail(MailTemplateProvider::ADMIN_TEST_TEMPLATE, 'test', [], ['gonzyer@gmail.com']);
    }

    public function sendEmailChangeConfirmation(User $user) {
        $template = MailTemplateProvider::CHANGE_EMAIL_CONFIRMATION_TEMPLATE;

        $params = ['user' => $user];

        $this->sendEmail($template, "Changement d'e-mail", $params, [], [], $user->getAskedEmail(), $user->getDisplayName());
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

        $this->sendEmail($template, "Quelqu'un a dit que vous possédiez un artiste sur Un-Mute", $params, [], [], $req->getEmail(), $toName);
    }

    public function sendSuggestionBoxCopy(SuggestionBox $suggestionBox) {
        $recipient = $suggestionBox->getEmail();
        $recipientName = $suggestionBox->getDisplayName();
        $this->sendEmail(MailTemplateProvider::SUGGESTIONBOXCOPY_TEMPLATE, 'Un-Mute / ' . $suggestionBox->getObject(), ['suggestionBox' => $suggestionBox], [], [], $recipient, $recipientName);
    }

    public function sendKnownOutcomeContract(ContractArtist $contract, $success, $artist_users, $fan_users) {
        $params = ['contract' => $contract, 'artist' => $contract->getArtist()->getArtistname()];

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

        $this->sendEmail($template_artist, 'Subject', $params, $bcc);

        // mail to fans
        if(!empty($fan_users)) {
            $bcc = array_unique(array_map(function(User $elem) {
                return $elem->getEmail();
            }, $fan_users));

            $this->sendEmail($template_fan, 'Subject', $params, $bcc);
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
        $this->sendEmail(MailTemplateProvider::ONGOING_CART_TEMPLATE, 'subject', $params, $recipients);
        $this->notification_dispatcher->notifyOngoingCart($users, $contract);
    }

    public function sendArtistReminderContract($users, ContractArtist $contract, $nb_days) {
        $recipients = array_map(function(User $elem) {
            return $elem->getEmail();
        }, $users);
        $params = ['contract' => $contract, 'nbDays' => $nb_days, 'artist' => $contract->getArtist()->getArtistname()];
        $this->sendEmail(MailTemplateProvider::REMINDER_CONTRACT_ARTIST_TEMPLATE, 'subject', $params, $recipients);
        $this->notification_dispatcher->notifyReminderArtistContract($users, $contract, $nb_days);
    }

    public function sendOrderRecap(ContractFan $contractFan) {
        // TODO should be another way of getting pdf path
        $attachments = ['votreCommande.pdf' => $this->kernel->getRootDir() . '\..\web\pdf\orders\\' . $contractFan->getBarcodeText().'.pdf'];

        $to = $contractFan->getFan()->getEmail();
        $toName = $contractFan->getFan()->getDisplayName();
        $subject = "Votre commande sur Un-Mute.be";
        $params = [];

        $this->sendEmail(MailTemplateProvider::ORDER_RECAP_TEMPLATE, $subject, $params, [], $attachments, $to, $toName);
    }

    public function sendTicket($ticket_html, ContractFan $contractFan) {
        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($ticket_html);
        $html2pdf->output('pdf/tickets/'.$contractFan->getBarcodeText().'.pdf', 'F');

        $attachments = ['votreContrat.pdf' => $this->get('kernel')->getRootDir() . '\..\web\pdf\tickets\\' . $contractFan->getBarcodeText().'.pdf'];

        $to = $contractFan->getFan()->getEmail();
        $toName = $contractFan->getFan()->getDisplayName();
        $subject = "subject";
        $params = [];

        $this->sendEmail(MailTemplateProvider::TICKET_TEMPLATE, $subject, $params, [], $attachments, $to, $toName);
        $this->notification_dispatcher->notifyTicket($contractFan->getFan(), $contractFan);
    }

    public function sendAdminReminderContract(ContractArtist $contract, $nb_days) {
        $subject = "Rappel : un contrat doit être concrétisé";
        $params = ['contractArtist' => $contract, 'nbDays' => $nb_days];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_REMINDER_CONTRACT_TEMPLATE, $subject, $params);
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