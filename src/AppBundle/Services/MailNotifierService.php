<?php

namespace AppBundle\Services;

use Azine\EmailBundle\Services\AzineNotifierService;
use Azine\EmailBundle\Entity\RecipientInterface;

class MailNotifierService extends AzineNotifierService
{
    /**
     * (non-PHPdoc)
     * @see Azine\EmailBundle\Services.AzineNotifierService::getRecipientVarsForNotificationsEmail()
     */
    protected function getRecipientVarsForNotificationsEmail(RecipientInterface $recipient)
    {
        $recipientParams = parent::getRecipientVarsForNotificationsEmail($recipient);

        // $recipientParams = array_merge($this->getSomeMoreRecipientParamsForTheNotificationEmail($recipient), $recipientParams);
        return $recipientParams;
    }

    /**
     * (non-PHPdoc)
     * @see Azine\EmailBundle\Services.AzineNotifierService::getRecipientSpecificNotificationsSubject()
     */
    public function getRecipientSpecificNotificationsSubject($contentItems, RecipientInterface $recipient)
    {
        return parent::getRecipientSpecificNotificationsSubject($contentItems, $recipient);
    }

    /**
     * (non-PHPdoc)
     * @see Azine\EmailBundle\Services.AzineNotifierService::getNonRecipientSpecificNewsletterContentItems()
     */
    protected function getNonRecipientSpecificNewsletterContentItems()
    {
        $contentItems = array();

        $templateParams = array('title' => 'foo bar');
        //$templateParams = array_merge($this->getSomeMoreParamsForTheNewsletter(), $templateParams);
        $contentItems[] = array('AcmeBundle:foo:barSameForAllRecipientsTemplate' => $templateParams);

        return $contentItems;
    }

    /**
     * (non-PHPdoc)
     * @see Azine\EmailBundle\Services.AzineNotifierService::getRecipientSpecificNewsletterSubject()
     */
    public function getRecipientSpecificNewsletterSubject(array $generalContentItems, array $recipientContentItems, array $params, RecipientInterface $recipient, $locale)
    {
        return parent::getRecipientSpecificNewsletterSubject($generalContentItems, $recipientContentItems, $params, $recipient, $locale);
    }

    /**
     * (non-PHPdoc)
     * @see Azine\EmailBundle\Services.AzineNotifierService::getRecipientSpecificNewsletterContentItems()
     */
    protected function getRecipientSpecificNewsletterContentItems(RecipientInterface $recipient)
    {
        $contentItems = array();

        $recipientSpecificTemplateParams = array('title' => 'foo bar');
        //$recipientSpecificTemplateParams = array_merge($this->getSomeMoreParamsForTheNewsletterForThisRecipient($recipient), $recipientSpecificTemplateParams);
        $contentItems[] = array('AcmeBundle:foo:barDifferentForEachRecipientTemplate' => $recipientSpecificTemplateParams);

        return $contentItems;
    }

}