<?php

namespace AppBundle\Services;

use Azine\EmailBundle\Services\AzineTemplateProvider;
use Azine\EmailBundle\Services\TemplateProviderInterface;

/**
 * This Service provides the templates and template-variables to be used for emails
 * This class is only an example. Implement your own!
 * @codeCoverageIgnore
 * @author Dominik Businger
 */
class MailTemplateProvider extends AzineTemplateProvider implements TemplateProviderInterface
{

    // TODO mettre à jour les trucs à garder pour webview (voir + bas)

    const REMINDER_CONTRACT_ARTIST_TEMPLATE = 'AppBundle:Mail/Artist:reminder_contract_artist.txt.twig';
    const FAILED_CONTRACT_ARTIST_TEMPLATE = 'AppBundle:Mail/Artist:failed_contract_artist.txt.twig';
    const SUCCESSFUL_CONTRACT_ARTIST_TEMPLATE = 'AppBundle:Mail/Artist:successful_contract_artist.txt.twig';
    const ARTIST_VALIDATED_TEMPLATE = 'AppBundle:Mail/Artist:artist_validated.txt.twig';

    const FAILED_CONTRACT_FAN_TEMPLATE = 'AppBundle:Mail/Fan:failed_contract_fan.txt.twig';
    const SUCCESSFUL_CONTRACT_FAN_TEMPLATE = 'AppBundle:Mail/Fan:successful_contract_fan.txt.twig';
    const ONGOING_CART_TEMPLATE = 'AppBundle:Mail/Fan:ongoing_cart.txt.twig';
    const TICKET_TEMPLATE = 'AppBundle:Mail/Fan:ticket.txt.twig';
    const ORDER_RECAP_TEMPLATE = 'AppBundle:Mail/Fan:order.txt.twig';
    const TICKETS_TEMPLATE = 'AppBundle:Mail/Fan:tickets.txt.twig';
    const REFUNDED_PAYMENT_TEMPLATE = 'AppBundle:Mail/Fan:refunded_payment.txt.twig';
    const VIP_TICKETS_TEMPLATE = 'AppBundle:Mail/VIPInscription:vip_tickets.txt.twig';

    const CHANGE_EMAIL_CONFIRMATION_TEMPLATE = 'AppBundle:Mail/User:change_email_confirmation.txt.twig';

    const OWNERSHIPREQUEST_MEMBER_TEMPLATE = 'AppBundle:Mail/OwnershipRequest:ownershiprequest_member.txt.twig';
    const OWNERSHIPREQUEST_NONMEMBER_TEMPLATE = 'AppBundle:Mail/OwnershipRequest:ownershiprequest_nonmember.txt.twig';

    const SUGGESTIONBOXCOPY_TEMPLATE = 'AppBundle:Mail/SuggestionBox:copy.txt.twig';
    const VIPINSCRIPTIONCOPY_TEMPLATE = 'AppBundle:Mail/VIPInscription:copy.txt.twig';

    const RANKING_EMAIL_USER_TEMPLATE = "AppBundle:Mail/User:ranking_email.txt.twig";
    const REWARD_ATTRIBUTION_TEMPLATE = "AppBundle:Mail/User:reward_attribution.txt.twig";
    const MAIL_FROM_ADMIN_TEMPLATE = "AppBundle:Mail/User:mail_from_admin.txt.twig";

    // Admin mails templates
    // TODO uniform names
    const ADMIN_TEST_TEMPLATE = 'AppBundle:Mail/Admin:test.txt.twig';
    const ADMIN_REMINDER_CONTRACT_TEMPLATE = 'AppBundle:Mail/Admin:reminder_contract.txt.twig';
    const ADMIN_PENDING_CONTRACT_TEMPLATE = 'AppBundle:Mail/Admin:pending_contract.txt.twig';
    const ADMIN_NEWLY_SUCCESSFUL_CONTRACT_TEMPLATE = 'AppBundle:Mail/Admin:newly_successful_contract.txt.twig';
    const ADMIN_ENORMOUS_PAYER_TEMPLATE = 'AppBundle:Mail/Admin:enormous_payer.txt.twig';
    const ADMIN_STRIPE_ERROR_TEMPLATE = 'AppBundle:Mail/Admin:stripe_error.txt.twig';
    const ADMIN_TICKETS_SENT = 'AppBundle:Mail/Admin:tickets_sent.txt.twig';
    const ADMIN_CONTACT_FORM = 'AppBundle:Mail/SuggestionBox:admin.txt.twig';
    const ADMIN_PROPOSITION_SUBMIT = 'AppBundle:Mail/Admin:proposition_submit.txt.twig';
    const ADMIN_VIP_INSCRIPTION_FORM = 'AppBundle:Mail/VIPInscription:admin.txt.twig';
    const ADMIN_NEW_ARTIST = 'AppBundle:Mail/Admin:new_artist.txt.twig';

    /**
     * @see Azine\EmailBundle\Services\AzineTemplateProvider::getParamArrayFor()
     * @param string $template
     * @return array
     */
    protected function getParamArrayFor($template)
    {
        // get the style-params from the parent (if you like)
        $newVars = parent::getParamArrayFor($template);

        // add template specific stuff
        // if ($template == self::NOTIFICATIONS_TEMPLATE) {
        //     $newVars['%someUrl%'] = "http://example.com"; 				//$this->router->generate("your_route", $routeParamArray, UrlGeneratorInterface::ABSOLUTE_URL);
        //    $newVars['%someOtherUrl%'] = "http://example.com/other";	//$this->router->generate("your_route", $routeParamArray, UrlGeneratorInterface::ABSOLUTE_URL);
        //}

        // override some generic stuff needed for all templates
        $newVars["h2Style"] = "style='padding:0; margin:0; font:bold 24px Arial; color:red; text-decoration:none;'";

        // add an image that should be embedded into the html-email.
        $newVars['logo'] = $this->getTemplateImageDir() . "logo.png";
        // after the image has been added here, it will be base64-encoded so it can be embedded into a html-snippet
        // see self::getSnippetArrayFor()

        return $newVars;
    }

    /**
     * @see Azine\EmailBundle\Services\AzineTemplateProvider::getSnippetArrayFor()
     * @param string $template
     * @param array $vars
     * @param string $emailLocale
     * @return array
     * @throws \Exception
     */
    protected function getSnippetArrayFor($template, array $vars, $emailLocale)
    {
        // add a code snippet to reference the random image you added in the getParamArrayFor() method.
        // in the mean time it has been base64-encoded and attached as mime-part to your email.
        try {
            $vars['sampleSnippetWithImage'] = "<img src='" . $vars['logo'] . "'>";
        } catch (\Exception $e) {
        }

        // with this html-snippet you can display the "someRandomImage.png" from your
        // template-folder like this in your twig-template:   .... {{ sampleSnippetWithImage }} ...
        // TODO doesn't work :(

        return parent::getSnippetArrayFor($template, $vars, $emailLocale);
    }

    /**
     * @see Azine\EmailBundle\Services\AzineTemplateProvider::addCustomHeaders()
     * @param string $template
     * @param \Swift_Message $message
     * @param array $params
     * @return array
     */
    public function addCustomHeaders($template, \Swift_Message $message, array $params)
    {
        // see http://documentation.mailgun.com/user_manual.html#attaching-data-to-messages
        // for an idea what could be added here.
        //$headerSet = $message->getHeaders();
        //$headerSet->addTextHeader($name, $value);
    }

    /**
     * @see Azine\EmailBundle\Services\AzineTemplateProvider::getCampaignParamsFor()
     * @param $templateId
     * @param array $params
     * @return array
     */
    public function getCampaignParamsFor($templateId, array $params = null)
    {
        $campaignParams = array();
        //if ($templateId == "AcmeFooBundle:bar:mail.template") {
        //      $campaignParams[$this->tracking_params_campaign_name] = "foo-bar-campaign";
        //      $campaignParams[$this->tracking_params_campaign_term] = "keyword";
        //} else {
        // get some other params
        $campaignParams = parent::getCampaignParamsFor($templateId, $params);
        //}
        return $campaignParams;
    }

    /**
     * Override this function to define which emails you want to make the web-view available and for which not.
     * @see Azine\EmailBundle\Services\AzineTemplateProvider::getTemplatesToStoreForWebView()
     * @return array
     */
    public function getTemplatesToStoreForWebView()
    {
        $include = parent::getTemplatesToStoreForWebView();
        $include = array_merge($include, [
            self::REMINDER_CONTRACT_ARTIST_TEMPLATE,
            self::FAILED_CONTRACT_ARTIST_TEMPLATE,
            self::SUCCESSFUL_CONTRACT_ARTIST_TEMPLATE,
            self::FAILED_CONTRACT_FAN_TEMPLATE,
            self::SUCCESSFUL_CONTRACT_FAN_TEMPLATE,
            self::ONGOING_CART_TEMPLATE,
            self::TICKET_TEMPLATE,
            self::ORDER_RECAP_TEMPLATE,
            self::TICKETS_TEMPLATE,
            self::VIP_TICKETS_TEMPLATE,
            self::REFUNDED_PAYMENT_TEMPLATE,
            self::ARTIST_VALIDATED_TEMPLATE,

            self::CHANGE_EMAIL_CONFIRMATION_TEMPLATE,

            self::OWNERSHIPREQUEST_MEMBER_TEMPLATE,
            self::OWNERSHIPREQUEST_NONMEMBER_TEMPLATE,

            self::SUGGESTIONBOXCOPY_TEMPLATE,
            self::VIPINSCRIPTIONCOPY_TEMPLATE,

        ]);
        return $include;
    }

}
