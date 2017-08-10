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

        $artistReminderService = $this->getContainer()->get('app.reminder_contract_artist');

        $output->writeln([
            'Calling the service',
            '============',
        ]);

        $result = $artistReminderService->sendMailsForXDays($days);

        $output->writeln('Done ; ' . $result['notifs'] . ' notifications and ' . $result['mails'] . ' e-mails sent.');
    }
}
