<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReminderArtistContractCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('reminders:contract:artist')
            ->setDescription('Reminds an artist that one of its contract is almost over')
            ->setHelp('Reminds an artist that one of its contract is almost over')

            ->addArgument('days', InputArgument::REQUIRED, 'The number of days to the concert to remind')

        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = intval($input->getArgument('days'));

        $output->writeln([
            'Starting the command',
            '============',
        ]);

        $result = $this->sendMailsForXDays($days);

        $output->writeln('Done ; ' . $result['notifs'] . ' notifications and ' . $result['mails'] . ' e-mails sent.');
    }


    private function sendMailsForXDays($days) {

        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $mailer = $container->get('azine_email.default.template_twig_swift_mailer');
        $notifier = $container->get('email.notifier_service');

        $currentContracts = $em->getRepository('AppBundle:ContractArtist')->findCurrents();
        $currentDate = new \DateTime();

        $result = ['notifs' => 0, 'mails' => 0];

        foreach($currentContracts as $contract) {
            $reminder = false;

            if((($contract->getReminders() < 1 && $days == 30) || ($contract->getReminders() < 2 && $days == 15))
                && $currentDate->diff($contract->getDateEnd())->days <= $days ) {
                $reminder = true;
            }

            if($reminder) {
                $artist_users = $contract->getArtist()->getArtistsUser();
                $users = array();

                foreach($artist_users as $au) {
                    $user = $au->getUser();
                    $users[] = $user->getEmail();

                    // Notification creation
                    $title = $days . " days until mdrz !";
                    $content = "Wouhouuu";
                    $recipientId = $user->getId();

                    $notifier->addNotificationMessage($recipientId, $title, $content);
                    $result['notifs']++;
                }

                $from = "no-reply@un-mute.be";
                $fromName = "Un-Mute";

                $bcc = "gonzyer@gmail.com";
                $bccName = "Webmaster";

                $replyTo = "gonzyer@gmail.com";
                $replyToName = "Webmaster";

                $params = ['contract' => $contract, 'days' => $days, 'artist' => $contract->getArtist()->getArtistName()];

                $mailer->sendEmail($failedRecipients, "Sujet", $from, $fromName, $users, '', '', '',
                    $bcc, $bccName, $replyTo, $replyToName, $params, MailTemplateProvider::REMINDER_CONTRACT_ARTIST_TEMPLATE);

                $result['mails']++;

                $contract->setReminders($contract->getReminders() + 1);
                $em->persist($contract);
            }
        }

        $em->flush();
        return $result;
    }
}
