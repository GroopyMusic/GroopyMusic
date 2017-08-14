<?php

namespace AppBundle\Command;

use AppBundle\Services\MailTemplateProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

// TODO add notifications

class OngoingCartsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('infos:carts:ongoing')
            ->setDescription('Send e-mails to inform fans if their carts are still ongoing for a crowdfunding that will end in X days')
            ->addArgument('days', InputArgument::REQUIRED, 'Number of days prior to the crowdfunding end');
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

        $days = intval($input->getArgument('days'));
        $result = $this->sendMailsWhenOngoingCart($days, $output);

        $output->writeln('Done ; ' . $result . ' e-mails sent');

    }

    private function sendMailsWhenOngoingCart($days, $output) {

        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $mailer = $container->get('azine_email_template_twig_swift_mailer');

        $contracts = $em->getRepository('AppBundle:ContractArtist')->findCurrents();

        $currentDate = new \DateTime();

        $nb_emails_sent = 0;

        foreach($contracts as $contract) {

            if(!$contract->getCartReminderSent() && $currentDate->diff($contract->getDateEnd())->days <= $days) {

                $carts = $em->getRepository('AppBundle:Cart')->findOngoingForContract($contract);

                $bcc = array_unique(array_map(function ($elem) {
                    return $elem->getUser()->getEmail();
                }, $carts));

                $from = "no-reply@un-mute.be";
                $fromName = "Un-Mute";

                $params = ['contract' => $contract, 'artist' => $contract->getArtist()->getArtistName()];

                $mailer->sendEmail($failedRecipients, "Sujet", $from, $fromName, array(), '', array(), '',
                    $bcc, '', array(), '', $params, MailTemplateProvider::ONGOING_CART_TEMPLATE);
                $nb_emails_sent++;

                $contract->setCartReminderSent(true);
            }
        }
        $em->flush();

        return $nb_emails_sent;
    }
}
