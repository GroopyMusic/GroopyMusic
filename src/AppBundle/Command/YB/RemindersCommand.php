<?php

namespace AppBundle\Command\YB;

use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Services\MailDispatcher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemindersCommand extends ContainerAwareCommand
{
    const
        REMINDER_BUYER_UPCOMING_EVENT_1 = 'buyer_upcoming_event_1';


    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('yb:reminders')
            ->setDescription('Send various reminders to Ticked-it stakeholders')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mailer = $this->getContainer()->get(MailDispatcher::class);

        $campaigns = $em->getRepository('AppBundle:YB\YBContractArtist')->getCurrentYBCampaigns();

        foreach($campaigns as $campaign) {
            /** @var YBContractArtist $campaign */
            $reminders = $campaign->getReminders();

            // -------------- Upcoming event 1
            if(!in_array(self::REMINDER_BUYER_UPCOMING_EVENT_1, $reminders)) {
                if($campaign->isEvent() && !$campaign->isPassed() && !$campaign->getFailed() && $campaign->getDateEvent()->diff(new \DateTime())->days < 7) {
                    $mailer->sendYBReminderUpcomingEventBuyers($campaign);

                    $campaign->addReminder(self::REMINDER_BUYER_UPCOMING_EVENT_1);

                    $output->writeln("Rappel " . self::REMINDER_BUYER_UPCOMING_EVENT_1 . " envoyé pour l'événement " . $campaign->getTitle());
                }
            }


        }

        $em->flush();
    }
}
