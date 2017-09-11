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
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('reminders:crowdfunding:artist')
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

        $this->sendMailsForXDays($days);

        $output->writeln('Done.');
    }


    private function sendMailsForXDays($days) {

        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');

        $mailer = $container->get(MailDispatcher::class);

        $currentContracts = $em->getRepository('AppBundle:ContractArtist')->findCurrents();
        $currentDate = new \DateTime();

        foreach($currentContracts as $contract) {
            /** @var ContractArtist $contract */
            $reminder = false;

            if((($contract->getRemindersArtist() < 1 && $days == 30) || ($contract->getRemindersArtist() < 2 && $days == 15))
                && $currentDate->diff($contract->getDateEnd())->days <= $days ) {
                $reminder = true;
            }

            if($reminder) {
                $artist_users = $contract->getArtist()->getArtistsUser();

                $mailer->sendArtistReminderContract($artist_users->toArray(), $contract, $days);

                $contract->setRemindersArtist($contract->getRemindersArtist() + 1);
                $em->persist($contract);
            }
        }

        $em->flush();
    }
}
