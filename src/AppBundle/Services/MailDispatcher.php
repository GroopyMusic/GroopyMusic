<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use Azine\EmailBundle\Services\AzineTwigSwiftMailer;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class MailDispatcher
{
    const FROM = "no-reply@un-mute.be";
    const FROM_NAME = "Un-Mute";

    private $mailer;
    private $translator;

    public function __construct(AzineTwigSwiftMailer $mailer, Translator $translator)
    {
        $this->mailer = $mailer;
        $this->translator = $translator;
    }

    public function sendEmailChangeConfirmation(User $user) {
        $template = MailTemplateProvider::CHANGE_EMAIL_CONFIRMATION_TEMPLATE;

        $params = ['user' => $user];

        $this->mailer->sendEmail($failedRecipients, "Changement d'e-mail", self::FROM, self::FROM_NAME,
            $user->getAskedEmail(), $user->getDisplayName(), [], '',
            [], '', [], '', $params, $template);
    }

}