<?php

namespace AppBundle\Command;

use AppBundle\Entity\ContractArtist;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\MailNotifierService;
use AppBundle\Services\NotificationDispatcher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReminderArtistContractCommand extends ContainerAwareCommand
{
    const NB_DAYS_FIRST_REMINDER = 20;
    const NB_DAYS_SECOND_REMINDER = 10;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('reminders:crowdfunding:artist')
            ->setDescription('Reminds an artist that one of its contract is almost on deadline')
            ->setHelp('Reminds an artist that one of its contract is almost on deadline')
            ->addArgument('x', InputArgument::REQUIRED, 'The reminder #x')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $x = intval($input->getArgument('x'));
        $days = $x == 1 ? self::NB_DAYS_FIRST_REMINDER : self::NB_DAYS_SECOND_REMINDER;

        $output->writeln([
            'Starting the command',
            '============',
        ]);

        $nb_mails = $this->sendMailsForXDays($days);
        $output->writeln($nb_mails . ' mails sent');

        $output->writeln('Done.');
    }


    private function sendMailsForXDays($days) {

        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $mailer = $container->get(MailDispatcher::class);

        $currentContracts = $em->getRepository('AppBundle:ContractArtist')->findNotSuccessfulYet();
        $currentDate = new \DateTime();

        $nb_mails = 0;

        foreach($currentContracts as $contract) {
            /** @var ContractArtist $contract */

            if((($contract->getRemindersArtist() < 1 && $days == self::NB_DAYS_FIRST_REMINDER)
                    || ($contract->getRemindersArtist() < 2 && $days == self::NB_DAYS_SECOND_REMINDER))
                && $contract->getDateEnd() > $currentDate && $currentDate->diff($contract->getDateEnd())->days <= $days ) {

                $artist_users = $contract->getArtistProfiles();
                $mailer->sendArtistReminderContract($artist_users, $contract);
                $nb_mails++;

                $contract->setRemindersArtist($contract->getRemindersArtist() + 1);
                $em->persist($contract);
            }

        }

        $em->flush();

        return $nb_mails;
    }
}
