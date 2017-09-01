<?php

namespace AppBundle\Command;

use AppBundle\Entity\ContractArtist;
use AppBundle\Services\MailTemplateProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// TODO add notifications

class KnownOutcomeContractCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('infos:contracts:known-outcome')
            ->setDescription('Send e-mails to inform people if their contracts are failures or successes, once that outcome is known')
            ->addArgument('success', InputArgument::REQUIRED, 'String success if looking for successful contracts, string failure otherwise')
            ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln([
            'Starting the command',
            '============',
        ]);

        $success = $input->getArgument('success') == 'success';
        $result = $this->sendMailsWhenKnownOutcome($success);

        $output->writeln('Done ; ' . $result . ' e-mails sent');
    }

    private function sendMailsWhenKnownOutcome($success) {

        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $mailer = $container->get('azine_email_template_twig_swift_mailer');

        $nb_emails_sent = 0;

        if($success) {
            $contracts = $em->getRepository('AppBundle:ContractArtist')->findNewlySuccessful();
            $template_artist = MailTemplateProvider::SUCCESSFUL_CONTRACT_ARTIST_TEMPLATE;
            $template_fan = MailTemplateProvider::SUCCESSFUL_CONTRACT_FAN_TEMPLATE;
        } else {
            $contracts = $em->getRepository('AppBundle:ContractArtist')->findNewlyFailed();
            $template_artist = MailTemplateProvider::FAILED_CONTRACT_ARTIST_TEMPLATE;
            $template_fan = MailTemplateProvider::FAILED_CONTRACT_FAN_TEMPLATE;
        }

        foreach($contracts as $contract) {
            /* @var ContractArtist $contract */

            $artist_users = $contract->getArtistProfiles();
            $fan_users = $contract->getFanProfiles();
            $fan_contracts = $contract->getContractsFan();

            $from = "no-reply@un-mute.be";
            $fromName = "Un-Mute";

            $params = ['contract' => $contract, 'artist' => $contract->getArtist()->getArtistName()];

            // mail 1
            $bcc = array_map(function($elem) {
                return $elem->getEmail();
            }, $artist_users);

            $mailer->sendEmail($failedRecipients, "Sujet", $from, $fromName, array(), '', array(), '',
                $bcc, '', array(), '', $params, $template_artist);
            $nb_emails_sent++;

            // mail 2
            if(!empty($fan_users)) {
                $bcc = array_unique(array_map(function($elem) {
                    return $elem->getEmail();
                }, $fan_users));

                $mailer->sendEmail($failedRecipients, "Sujet", $from, $fromName, array(), '', array(), '',
                    $bcc, '', array(), '', $params, $template_fan);
                $nb_emails_sent++;
            }

            if($success) {
                foreach($fan_contracts as $fc) {
                    $fc->getFan()->addCredits($fc->getAmount());
                }
                $contract->setSuccessful(true);
            } else {
                $contract->setFailed(true);
            }
        }

        $em->flush();

        return $nb_emails_sent;
    }
}
