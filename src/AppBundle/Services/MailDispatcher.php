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
use AppBundle\Entity\SponsorshipInvitation;
use AppBundle\Entity\SuggestionBox;
use AppBundle\Entity\User;
use AppBundle\Entity\User_Category;
use AppBundle\Entity\VIPInscription;
use AppBundle\Entity\VolunteerProposal;
use AppBundle\Entity\YB\Organization;
use AppBundle\Entity\YB\OrganizationJoinRequest;
use AppBundle\Entity\YB\YBContact;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\YBTransactionalMessage;
use AppBundle\Repository\SuggestionTypeEnumRepository;
use Azine\EmailBundle\Services\AzineTwigSwiftMailer;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\HttpKernel\KernelInterface;
use Twig\Environment;
use XBundle\Entity\Product;
use XBundle\Entity\Project;
use XBundle\Entity\XContact;

class MailDispatcher
{
    const DEFAULT_LOCALE = 'fr';
    const MAX_BCC = 100;

    const TO = ["festivals@un-mute.be" => self::DEFAULT_LOCALE];

    const REPLY_TO = ["festivals@un-mute.be"];
    const REPLY_TO_NAME = "Un-Mute ASBL";

    const YB_REPLY_TO = ["info@ticked-it.be"];
    const YB_REPLY_TO_NAME = "Ticked-it!";

    const ADMIN_TO = ["pierre@un-mute.be" => self::DEFAULT_LOCALE, "gonzague@un-mute.be" => self::DEFAULT_LOCALE];

    private $mailer;
    private $from_address;
    private $from_name;
    private $translator;
    private $notification_dispatcher;
    private $em;
    private $kernel;
    private $twig;
    private $locales;
    private $logger;

    public function __construct(AzineTwigSwiftMailer $mailer, Translator $translator, NotificationDispatcher $notificationDispatcher, EntityManagerInterface $em, $from_address, $from_name, KernelInterface $kernel, Environment $twig, LoggerInterface $logger, $locales)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
        $this->notification_dispatcher = $notificationDispatcher;
        $this->em = $em;
        $this->from_address = $from_address;
        $this->from_name = $from_name;
        $this->kernel = $kernel;
        $this->twig = $twig;
        $this->locales = $locales;
        $this->logger = $logger;
    }

    private function extract_locale($locale, $haystack)
    {
        return array_filter($haystack, function ($elem) use ($locale) {
            return $elem == $locale;
        });
    }

    private function sendEmail($template, $subject, array $params, array $subject_params, array $bcc_emails, array $attachments = [], array $to = self::TO, $to_name = '', $reply_to = self::REPLY_TO, $reply_to_name = self::REPLY_TO_NAME)
    {
        $failedRecipients = array();
        $bccs = array();
        $tos = array();
        $to_chunks = array();
        $bcc_chunks = array();
        // CASE 1 : Only "to"s chunked by locale
        if (empty($bcc_emails) && !empty($to)) {
            foreach ($this->locales as $locale) {
                $tos[$locale] = $this->extract_locale($locale, $to);
                if (!empty($tos[$locale])) {
                    $trans_subject = $this->translator->trans($subject, $subject_params, 'emails', $locale);
                    $to_chunks[$locale] = array_chunk(array_keys($tos[$locale]), self::MAX_BCC);
                    foreach ($to_chunks[$locale] as $chunk) {
                        $this->mailer->sendEmail($failedRecipients, $trans_subject, $this->from_address, $this->from_name, $chunk, '', [], '',
                            [], '', $reply_to, $reply_to_name, array_merge(['subject' => $trans_subject], $params), $template, $attachments, $locale);
                    }
                }
            }
        }

        // CASE 2 : # of recipients is high and "to" field is for no-reply only
        // We need to chunk the bcc recipients
        else {
            foreach ($this->locales as $locale) {
                $bccs[$locale] = $this->extract_locale($locale, $bcc_emails);
                if (!empty($bccs[$locale])) {
                    $trans_subject = $this->translator->trans($subject, $subject_params, 'emails', $locale);
                    $bcc_chunks[$locale] = array_chunk(array_keys($bccs[$locale]), self::MAX_BCC);
                    foreach ($bcc_chunks[$locale] as $chunk) {
                        $this->mailer->sendEmail($failedRecipients, $trans_subject, $this->from_address, $this->from_name, array_keys($to), $to_name, [], '',
                            $chunk, '', $reply_to, $reply_to_name, array_merge(['subject' => $trans_subject], $params), $template, $attachments, $locale);
                    }
                }
            }
        }

        // CASE 3 : # of recipients is high and "to" fields is actually used
        // We need to separate BCC and TO and send e-mails in chunks
        // This case shouldn't happen in practice
        /* And it is disabled for now
        else {
            $failedRecipients = array();
            $bcc_chunks = array_chunk($bcc_emails, self::MAX_BCC);

            foreach ($bcc_chunks as $chunk) {
                $this->mailer->sendEmail($newFailedRecipients, $subject, $this->from_address, $this->from_name, self::TO, '', [], '',
                    $chunk, '', $reply_to, $reply_to_name, array_merge(['subject' => $subject], $params), $template, $attachments);
                $failedRecipients = array_merge($failedRecipients, $newFailedRecipients);
            }

            $to_chunks = array_chunk($to, self::MAX_BCC);

            foreach ($to_chunks as $chunk) {
                $this->mailer->sendEmail($newFailedRecipients, $subject, $this->from_address, $this->from_name, $chunk, '', [], '',
                    '', '', $reply_to, $reply_to_name, array_merge(['subject' => $subject], $params), $template, $attachments);
                $failedRecipients = array_merge($failedRecipients, $newFailedRecipients);
            }
        }
        */

        return $failedRecipients;
    }

    private function sendAdminEmail($template, $subject, array $params = [], array $subject_params = [], array $attachments = [], $reply_to = self::REPLY_TO, $reply_to_name = self::REPLY_TO_NAME)
    {
        return $this->sendEmail($template, $subject, $params, $subject_params, [], $attachments, self::ADMIN_TO, '', $reply_to, $reply_to_name);
    }

    public function sendTestEmail()
    {
        $emails = [];
        for ($i = 0; $i <= 180; $i++) {
            $locale = $i > 120 ? 'fr' : 'en';
            $emails['gonzyer' . $i . '@hotmail.com'] = $locale;
        }

        return $this->sendEmail(MailTemplateProvider::ADMIN_TEST_TEMPLATE, 'test', [], [], $emails);
    }

    public function sendEmailChangeConfirmation(User $user)
    {
        $template = MailTemplateProvider::CHANGE_EMAIL_CONFIRMATION_TEMPLATE;

        $params = ['user' => $user];
        $subject_params = [];

        $recipient = [$user->getAskedEmail() => $user->getPreferredLocale()];
        $recipientName = [$user->getDisplayName()];

        $this->sendEmail($template, "subjects.change_email_confirmation", $params, $subject_params, [], [], $recipient, $recipientName);
    }


    public function sendNewOwnershipRequest(Artist $artist, ArtistOwnershipRequest $req)
    {
        $params = ['artist' => $artist, 'request' => $req];

        $toName = '';
        $locale = $this->translator->getLocale();

        $possible_user = $this->em->getRepository('AppBundle:User')->findOneBy(['email' => $req->getEmail()]);
        if ($possible_user != null) {
            $template = MailTemplateProvider::OWNERSHIPREQUEST_MEMBER_TEMPLATE;
            $params['user'] = $possible_user->getEmail();
            $toName = $possible_user->getDisplayName();
            $locale = $possible_user->getPreferredLocale();
        } else {
            $template = MailTemplateProvider::OWNERSHIPREQUEST_NONMEMBER_TEMPLATE;
        }

        $recipient = [$req->getEmail() => $locale];

        $subject_params = [];
        $this->sendEmail($template, "subjects.new_ownership_request", $params, $subject_params, [], [], $recipient, [$toName]);
    }

    public function sendSuggestionBoxCopy(SuggestionBox $suggestionBox)
    {
        $recipient = [$suggestionBox->getEmail() => $this->translator->getLocale()];
        $recipientName = [$suggestionBox->getDisplayName()];
        $params = ['suggestionBox' => $suggestionBox];
        $subject_params = [];
        $this->sendEmail(MailTemplateProvider::SUGGESTIONBOXCOPY_TEMPLATE, 'Un-Mute / ' . $suggestionBox->getObject(), $params, $subject_params, [], [], $recipient, $recipientName);
    }

    public function sendVIPInscriptionCopy(VIPInscription $inscription)
    {
        $recipient = [$inscription->getEmail() => $this->translator->getLocale()];
        $recipientName = [$inscription->getDisplayName()];
        $params = ['inscription' => $inscription];
        $subject_params = [];
        $subject = "Votre demande d'accréditation sur Un-Mute";

        $this->sendEmail(MailTemplateProvider::VIPINSCRIPTIONCOPY_TEMPLATE, $subject, $params, $subject_params, [], [], $recipient, $recipientName);
    }

    public function sendVolunteerProposalCopy(VolunteerProposal $inscription)
    {
        $recipient = [$inscription->getEmail() => $this->translator->getLocale()];
        $recipientName = [$inscription->getDisplayName()];
        $params = ['inscription' => $inscription];
        $subject_params = [];
        $subject = "Votre proposition de bénévolat sur Un-Mute";

        $this->sendEmail(MailTemplateProvider::VOLUNTEERPROPOSALCOPY_TEMPLATE, $subject, $params, $subject_params, [], [], $recipient, $recipientName);
    }

    public function sendKnownOutcomeContract(ContractArtist $contract, $success)
    {
        // $artist_users = $contract->getArtistProfiles();
        $fan_users = $contract->getPhysicalPersons();

        $params = ['contract' => $contract];

        if ($success) {
          //  $template_artist = MailTemplateProvider::SUCCESSFUL_CONTRACT_ARTIST_TEMPLATE;
            $template_fan = MailTemplateProvider::SUCCESSFUL_CONTRACT_FAN_TEMPLATE;
        } else {
           // $template_artist = MailTemplateProvider::FAILED_CONTRACT_ARTIST_TEMPLATE;
            $template_fan = MailTemplateProvider::FAILED_CONTRACT_FAN_TEMPLATE;
        }

        // mail to artists
        $bcc = [];
       // foreach ($artist_users as $au) {
       //     /** @var User $au */
        //     $bcc[$au->getEmail()] = $au->getPreferredLocale();
       //  }

        $subject_params = [];
        // $this->sendEmail($template_artist, 'subjects.concert.artist.known_outcome', $params, $subject_params, $bcc);

        // mail to fans
        if (!empty($fan_users)) {

            $bcc = [];
            foreach ($fan_users as $fu) {
                if($fu instanceof User) {
                    /** @var User $fu */
                    // should be user locale of course
                    $bcc[$fu->getEmail()] = $this->translator->getLocale();
                }
            }

            //$subject_params = ['%artist%' => $contract->getArtist()->getArtistname()];
            $this->sendEmail($template_fan, 'subjects.concert.fan.known_outcome', $params, $subject_params, $bcc);
        }

        //$this->notification_dispatcher->notifyKnownOutcomeContract($artist_users, $contract, true, $success);
        //$this->notification_dispatcher->notifyKnownOutcomeContract($fan_users, $contract, false, $success);
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

    public function sendArtistReminderContract($users, ContractArtist $contract)
    {
        $nb_days = (new \DateTime())->diff($contract->getDateEnd())->days;
        $places = $contract->getNbTicketsToSuccess();

        $recipients = [];
        foreach ($users as $user) {
            /** @var User $user */
            $recipients[$user->getEmail()] = $user->getPreferredLocale();
        }

        $params = ['contract' => $contract, 'days' => $nb_days, 'places' => $places];

        $subject_params = [];
        $this->sendEmail(MailTemplateProvider::REMINDER_CONTRACT_ARTIST_TEMPLATE, 'subjects.concert.artist.reminder', $params, $subject_params, $recipients);
        $this->notification_dispatcher->notifyReminderArtistContract($users, $contract, $nb_days, $places);
    }

    public function sendOrderRecap(Cart $cart)
    {
        // TODO should be another way of getting pdf path
        $attachments = ['votreCommande.pdf' => $this->kernel->getRootDir() . '/../web/' . $cart->getPdfPath()];

        $to = [$cart->getUser()->getEmail() => $cart->getUser()->getPreferredLocale()];
        $toName = [$cart->getUser()->getDisplayName()];
        $subject = 'subjects.order_recap';
        $params = [];
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::ORDER_RECAP_TEMPLATE, $subject, $params, $subject_params, [], $attachments, $to, $toName);
    }

    public function sendTicketsForPhysicalPerson(PhysicalPersonInterface $physicalPerson, ContractArtist $contractArtist, $path)
    {
        $attachments = ['um-ticket.pdf' => $this->kernel->getRootDir() . '/../web/' . $path];
        $params = ['contract' => $contractArtist];

        $toName = [$physicalPerson->getDisplayName() => $this->translator->getLocale()];
        $to = [$physicalPerson->getEmail()];

        $subject = 'subjects.concert.fan.viptickets';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::VIP_TICKETS_TEMPLATE, $subject, $params, $subject_params, [], $attachments, $to, $toName);
    }

    public function sendTicketsForContractFan(ContractFan $cf, ContractArtist $ca)
    {
        $params = [
            'contract' => $ca,
        ];

        $attachments = ['um-ticket.pdf' => $this->kernel->getRootDir() . '/../web/' . $cf->getTicketsPath()];

        $to = [$cf->getFan()->getEmail() => $cf->getFan()->getPreferredLocale()];
        $toName = [$cf->getFan()->getDisplayName()];

        $subject = 'subjects.concert.fan.tickets';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::TICKETS_TEMPLATE, $subject, $params, $subject_params, [], $attachments, $to, $toName);
    }

    public function sendRefundedPayment(Payment $payment)
    {
        $params = [
            'payment' => $payment,
        ];

        if($payment->isYB()) {
            return $this->sendRefundedYBPayment($payment);
        }

        $to = [$payment->getUser()->getEmail() => $payment->getUser()->getPreferredLocale()];
        $toName = [$payment->getUser()->getDisplayName()];

        $subject = 'subjects.refunded_payment';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::REFUNDED_PAYMENT_TEMPLATE, $subject, $params, $subject_params, [], [], $to, $toName);
    }

    public function sendRefundedContractFan(ContractFan $cf) {

        if($cf->getContractArtist()->isYB()) {
            $this->sendRefundedYBContractFan($cf, 'other');
            return;
        }

        $params = [
            'cf' => $cf,
        ];

        $to = [$cf->getUser()->getEmail() => $cf->getUser()->getPreferredLocale()];
        $toName = [$cf->getUser()->getDisplayName()];

        $subject = 'subjects.refunded_contract_fan';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::REFUNDED_CONTRACT_FAN_TEMPLATE, $subject, $params, $subject_params, [], [], $to, $toName);
    }

    public function sendArtistValidated(Artist $artist)
    {
        $params = [
            'artist' => $artist,
        ];

        $to = [];
        foreach ($artist->getOwners() as $owner) {
            /** @var User $owner */
            $to[$owner->getEmail()] = $owner->getPreferredLocale();
        }

        $toName = [];

        $subject = 'subjects.artist_validated';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::ARTIST_VALIDATED_TEMPLATE, $subject, $params, $subject_params, [], [], $to, $toName);
    }

    public function sendRankingEmail($stats, $object, $content)
    {
        $params = ['content' => $content];
        $subject_params = [];
        $to = [];
        foreach ($stats as $stat) {
            $user = $stat->getUser();
            $to[$user->getEmail()] = $user->getPreferredLocale();
        }
        $this->sendEmail(MailTemplateProvider::RANKING_EMAIL_USER_TEMPLATE, $object,
            $params, [], [], [], $to, [], self::REPLY_TO, self::REPLY_TO_NAME);
    }

    public function sendEmailRewardAttribution($stats, $content, $reward)
    {
        $params = ['content' => $content, 'reward' => $reward];
        $subject = "subjects.reward_attribution";
        $to = [];
        foreach ($stats as $stat) {
            $user = $stat->getUser();
            $to[$user->getEmail()] = $user->getPreferredLocale();
        }
        $this->sendEmail(MailTemplateProvider::REWARD_ATTRIBUTION_TEMPLATE, $subject,
            $params, [], [], [], $to, [], self::REPLY_TO, self::REPLY_TO_NAME);
    }

    public function sendEmailFromAdmin($emails, $subject, $content)
    {
        $params = ['content' => $content];
        $this->sendEmail(MailTemplateProvider::MAIL_FROM_ADMIN_TEMPLATE, $subject,
            $params, [], [], [], $emails, [], self::REPLY_TO, self::REPLY_TO_NAME);
    }

    public function sendSponsorshipInvitationEmail(SponsorshipInvitation $sponsorshipInvitation, $content)
    {
        $subject = "subjects.sponsorship_invitation";
        $local = $sponsorshipInvitation->getHostInvitation()->getPreferredLocale();
        if ($content == null || count(trim($content)) == 0) {
            $content = null;
        } else {
            $content_text = $this->translator->trans("sponsorship_invitation.invit_message_header", [], 'emails', $local);
            $content = $content_text . " \n " . $content;
        }
        $to = [$sponsorshipInvitation->getEmailInvitation() => $local];
        $params = ['content' => $content,
            'contractArtist' => $sponsorshipInvitation->getContractArtist(),
            'user' => $sponsorshipInvitation->getHostInvitation(),
            'token' => $sponsorshipInvitation->getTokenSponsorship()];
        $this->sendEmail(MailTemplateProvider::SPONSORSHIP_INVITATION_MAIL, $subject,
            $params, [], [], [], $to, [], self::REPLY_TO, self::REPLY_TO_NAME);
    }



    // ----------------------
    // ADMIN EMAILS
    // ----------------------

    public function sendAdminNewArtist(Artist $artist)
    {
        $params = ['artist' => $artist];
        $subject = 'Nouvel artiste inscrit sur Un-Mute';
        $subject_params = [];

        $this->sendAdminEmail(MailTemplateProvider::ADMIN_NEW_ARTIST, $subject, $params, $subject_params);
    }

    public function sendAdminContact(SuggestionBox $suggestionBox)
    {
        $params = ['suggestionBox' => $suggestionBox];

        $reply_to = $suggestionBox->getEmail() ?: self::REPLY_TO;
        $reply_to_name = $suggestionBox->getDisplayName() ?: '';

        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_CONTACT_FORM, 'Un-Mute / ' . $suggestionBox->getObject(), $params, $subject_params, [], $reply_to, $reply_to_name);
    }

    public function sendAdminVIPInscription(VIPInscription $inscription)
    {
        $params = ['inscription' => $inscription];
        $subject_params = [];
        $subject = "Nouvelle demande d'accréditation";

        $this->sendAdminEmail(MailTemplateProvider::ADMIN_VIP_INSCRIPTION_FORM, $subject, $params, $subject_params);
    }

    public function sendAdminVolunteerProposal(VolunteerProposal $inscription)
    {
        $params = ['inscription' => $inscription];
        $subject_params = [];
        $subject = 'Nouvelle proposition de bénévolat';

        $this->sendAdminEmail(MailTemplateProvider::ADMIN_VOLUNTEER_PROPOSAL_FORM, $subject, $params, $subject_params);
    }

    public function sendAdminTicketsSent(ContractArtist $contractArtist)
    {
        $params = ['contract' => $contractArtist];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_TICKETS_SENT, 'Tickets envoyés pour le concert de ' . $contractArtist->getArtist()->getArtistname(), $params, $subject_params);
    }

    public function sendAdminReminderContract(ContractArtist $contract, $nb_days)
    {
        $subject = "Rappel : un contrat doit être concrétisé";
        $params = ['contractArtist' => $contract, 'nbDays' => $nb_days];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_REMINDER_CONTRACT_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminPendingContract(ContractArtist $contract)
    {
        $subject = "La récolte de tickets d'un événement est arrivée à échéance";
        $params = ['contractArtist' => $contract];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_PENDING_CONTRACT_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminNewlySuccessfulContract(ContractArtist $contract)
    {
        $subject = "Un événement a atteint le seuil pour être concrétisé";
        $params = ['contractArtist' => $contract];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_NEWLY_SUCCESSFUL_CONTRACT_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminEnormousPayer(User $user)
    {
        $subject = "Payeur énorme spotted";
        $params = ['user' => $user];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_ENORMOUS_PAYER_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminStripeError(\Exception $e, User $user, Cart $cart)
    {
        $subject = "Erreur lors d'un paiement Stripe";
        $params = ['stripe_error' => $e, 'user' => $user, 'cart' => $cart];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_STRIPE_ERROR_TEMPLATE, $subject, $params, $subject_params);
    }

    public function sendAdminProposition(PropositionContractArtist $propositionContractArtist)
    {
        $subject = "Soumission de proposition";
        $params = ['contact_person' => $propositionContractArtist->getContactPerson(), 'event' => $propositionContractArtist];
        $subject_params = [];
        $this->sendAdminEmail(MailTemplateProvider::ADMIN_PROPOSITION_SUBMIT, $subject, $params, $subject_params);
    }



    // ------------------------ YB

    public function sendAdminYBContact(YBContact $contact) {
        $subject = 'Nouveau message sur Ticked-it!';
        $params = ['contact' => $contact];
        $subject_params = [];
        $reply_to = $contact->getEmail();
        $reply_to_name = $contact->getName();

        $this->sendAdminEmail(MailTemplateProvider::YB_ADMIN_CONTACT, $subject, $params, $subject_params, [], $reply_to, $reply_to_name);
    }

    public function sendYBContactCopy(YBContact $contact) {
        $subject = 'Formulaire de contact Ticked-it!';
        $params = ['contact' => $contact];
        $subject_params = [];
        $recipient = [$contact->getEmail() => $this->translator->getLocale()];
        $recipientName = $contact->getName();
        $reply_to = self::YB_REPLY_TO;
        $reply_to_name = self::YB_REPLY_TO_NAME;

        $this->sendEmail(MailTemplateProvider::YB_CONTACT_COPY, $subject, $params, $subject_params, [], [], $recipient, $recipientName, $reply_to, $reply_to_name);
    }

    public function sendYBOrderRecap(Cart $cart) {
        $subject = 'Votre commande sur Ticked-it!';
        $params = ['cart' => $cart];
        $subject_params = [];
        $recipient = [$cart->getEmail() => $this->translator->getLocale()];
        $recipientName = '';
        $reply_to = self::YB_REPLY_TO;
        $reply_to_name = self::YB_REPLY_TO_NAME;

        $this->sendEmail(MailTemplateProvider::YB_ORDER_RECAP, $subject, $params, $subject_params, [], [], $recipient, $recipientName, $reply_to, $reply_to_name);
    }

    public function sendYBTickets(ContractFan $cf, $newly_successful = false) {
        $subject = 'Vos tickets sont arrivés';
        $params = ['cf' => $cf, 'newly_successful' => $newly_successful];
        $subject_params = [];
        $recipient = [$cf->getEmail() => $this->translator->getLocale()];
        $recipientName = '';
        $reply_to = self::YB_REPLY_TO;
        $reply_to_name = self::YB_REPLY_TO_NAME;
        $attachments = ['ticked-it-ticket.pdf' => $this->kernel->getRootDir() . '/../web/' . $cf->getTicketsPath()];

        $this->sendEmail(MailTemplateProvider::YB_TICKETS, $subject, $params, $subject_params, [], $attachments, $recipient, $recipientName, $reply_to, $reply_to_name);
    }

    public function sendRefundedYBPayment(Payment $payment) {
        foreach($payment->getContractsFan() as $cf) {
            $this->sendRefundedYBContractFan($cf, 'other');
        }
    }

    public function sendRefundedYBContractFan(ContractFan $cf, $reason = 'crowdfunding') {
        $params = [
            'cf' => $cf,
            'campaign' => $campaign = $cf->getContractArtist(),
            'reason' => $reason,
        ];

        $to = [$cf->getEmail() => 'fr'];
        $toName = [$cf->getDisplayName()];

        $subject = 'Remboursement de votre commande';
        if($reason == 'crowdfunding')
            $subject .= ' - campagne "'. $campaign->getTitle() . '" annulée sur Ticked-it';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::YB_REFUNDED_CONTRACT_FAN_TEMPLATE, $subject, $params, $subject_params, [], [], $to, $toName);
    }

    public function sendYBReminderUpcomingEventBuyers(YBContractArtist $campaign) {
        $users = $campaign->getBuyers();
        $emails = array_unique(array_map(function(PhysicalPersonInterface $person) {
            return $person->getEmail();
        }, $users));

        $to = [];
        foreach($emails as $email) {
            $to[$email] = $this->translator->getLocale();
        }

        $params = ['campaign' => $campaign];
        $subject = 'subjects.yb.reminders.buyers.upcoming_event';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::YB_REMINDER_UPCOMING_EVENT_BUYERS, $subject, $params, $subject_params, $to);
    }

    public function sendYBReminderEventCreated(YBContractArtist $campaign) {
        $reminders = $campaign->getReminders();

        if(!in_array('organizer_campaign_created', $reminders)) {
            $organizers = $campaign->getHandlers();
            $emails = array_unique(array_map(function(PhysicalPersonInterface $person) {
                return $person->getEmail();
            }, $organizers));

            $to = self::ADMIN_TO;

            foreach($emails as $email) {
                $to[$email] = $this->translator->getLocale();
            }

            $params = ['campaign' => $campaign];
            $subject = 'subjects.yb.reminders.organizers.event_created';

            $subject_params = ['%event%' => $campaign->getTitle()];

            $this->sendEmail(MailTemplateProvider::YB_EVENT_CREATED, $subject, $params, $subject_params, $to);

            $campaign->addReminder('organizer_campaign_created');
            $this->em->persist($campaign);
            $this->em->flush();
        }


    }

    public function sendYBJoinOrganization($email, Organization $organization, User $user){
        $to = self::ADMIN_TO;
        $to[$email] = $this->translator->getLocale();
        $params = [
            'organization' => $organization,
            'member' => $user
        ];
        $subject = 'subjects.yb.reminders.join_organization';
        $subject_params = ['%organization%' => $organization->getName()];
        $this->sendEmail(MailTemplateProvider::YB_JOIN_ORGANIZATION, $subject, $params, $subject_params, $to);
    }

    public function sendYBNotifyOrganizationRequestCancel(OrganizationJoinRequest $request){
        $email = $request->getDemander()->getEmail();
        $to = self::ADMIN_TO;
        $to[$email] = $this->translator->getLocale();
        $params = [
            'organization' => $request->getOrganization(),
            'guest_email' => $request->getEmail(),
        ];
        $subject = 'subjects.yb.notifications.join_organization_cancelled';
        $subject_params = [
            '%organization%' => $request->getOrganization()->getName(),
            '%email_address%' => $request->getEmail(),
        ];
        $this->sendEmail(MailTemplateProvider::YB_NOTIFY_JOIN_ORGANIZATION_CANCEL, $subject, $params, $subject_params, $to);
    }

    public function sendYBTransactionalMessageWithCopy(YBTransactionalMessage $message) {
        $this->sendYBTransactionalMessage($message);
        try { $this->sendYBTransactionalMessageCopy($message); }
        catch(\Throwable $exception) {$this->logger->error("Echec lors de l'envoi de la copie d'un message transactionnel aux organisateurs : " . $exception->getMessage());}
    }

    public function sendYBTransactionalMessage(YBTransactionalMessage $message) {
        $campaign = $message->getCampaign();
        $buyers = $campaign->getBuyers();

        $buyers_emails = array_unique(array_map(function(PhysicalPersonInterface $person) {
            return $person->getEmail();
        }, $buyers));

        $to = [];

        foreach($buyers_emails as $email) {
            $to[$email] = $this->translator->getLocale();
        }

        $params = ['campaign' => $campaign, 'message' => $message];
        $subject = 'subjects.yb.transactional_message';

        $subject_params = ['%event%' => $campaign->getTitle()];

        $this->sendEmail(MailTemplateProvider::YB_TRANSACTIONAL_MESSAGE, $subject, $params, $subject_params, $to);
    }

    public function sendYBTransactionalMessageCopy(YBTransactionalMessage $message) {
        $campaign = $message->getCampaign();
        $organizers = $campaign->getHandlers();

        $organizers_emails = array_unique(array_map(function(PhysicalPersonInterface $person) {
            return $person->getEmail();
        }, $organizers));

        $to = self::ADMIN_TO;

        foreach($organizers_emails as $email) {
            $to[$email] = $this->translator->getLocale();
        }

        $params = ['campaign' => $campaign, 'message' => $message];
        $subject = 'subjects.yb.transactional_message_copy';

        $subject_params = ['%event%' => $campaign->getTitle()];

        $this->sendEmail(MailTemplateProvider::YB_TRANSACTIONAL_MESSAGE_COPY, $subject, $params, $subject_params, $to);
    }
    


    // ------------------------ X

    public function sendAdminXContact(XContact $contact) {
        $subject = 'Nouveau message sur Chapots!';
        $params = ['contact' => $contact];
        $subject_params = [];
        $reply_to = $contact->getEmail();
        $reply_to_name = $contact->getName();

        $this->sendAdminEmail(MailTemplateProvider::X_ADMIN_CONTACT, $subject, $params, $subject_params, [], $reply_to, $reply_to_name);
    }

    public function sendAdminNewProject(Project $project)
    {
        $params = ['project' => $project];
        $subject = 'Nouveau projet créé sur Chapots';
        $subject_params = [];

        $this->sendAdminEmail(MailTemplateProvider::X_ADMIN_NEW_PROJECT, $subject, $params, $subject_params);
    }

    public function sendProjectValidated(Project $project)
    {
        $params = [
            'project' => $project,
        ];

        $to = [];
        foreach ($project->getHandlers() as $handler) {
            /** @var User $handler */
            $to[$handler->getEmail()] = $handler->getPreferredLocale();
        }

        $toName = [];

        $subject = 'Votre project sur Chapots a été vérifié';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::X_PROJECT_VALIDATED, $subject, $params, $subject_params, [], [], $to, $toName);
    }

    public function sendProjectRefused(Project $project, $reason)
    {
        $params = [
            'project' => $project,
            'reason' => $reason,
        ];

        $to = [];
        foreach ($project->getHandlers() as $handler) {
            /** @var User $handler */
            $to[$handler->getEmail()] = $handler->getPreferredLocale();
        }

        $toName = [];

        $subject = 'Votre project sur Chapots a été refusé';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::X_PROJECT_REFUSED, $subject, $params, $subject_params, [], [], $to, $toName);
    }


    
    public function sendAdminNewProduct(Product $product)
    {
        $params = ['product' => $product];
        $subject = 'Mise en vente d\'un article sur Chapots';
        $subject_params = [];

        $this->sendAdminEmail(MailTemplateProvider::X_ADMIN_NEW_PRODUCT, $subject, $params, $subject_params);
    }

    public function sendProductValidated(Product $product)
    {
        $params = [
            'product' => $product,
        ];

        $to = [];
        foreach ($product->getProject()->getHandlers() as $handler) {
            /** @var User $handler */
            $to[$handler->getEmail()] = $handler->getPreferredLocale();
        }

        $toName = [];

        $subject = 'La mise en vente de votre article sur Chapots a été vérifiée';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::X_PRODUCT_VALIDATED, $subject, $params, $subject_params, [], [], $to, $toName);
    }

    public function sendProductRefused(Product $product, $reason)
    {
        $params = [
            'product' => $product,
            'reason' => $reason,
        ];

        $to = [];
        foreach ($product->getProject()->getHandlers() as $handler) {
            /** @var User $handler */
            $to[$handler->getEmail()] = $handler->getPreferredLocale();
        }

        $toName = [];

        $subject = 'La mise en vente de votre article sur Chapots a été refusée';
        $subject_params = [];

        $this->sendEmail(MailTemplateProvider::X_PRODUCT_REFUSED, $subject, $params, $subject_params, [], [], $to, $toName);
    }

}
