<?php

namespace AppBundle\Command;

use AppBundle\Entity\ContractArtist;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\MailTemplateProvider;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ReminderAdminContractCommand extends ContainerAwareCommand
{
    const NB_DAYS_AFTER_SUCCESS = [
        0 => 10,
        1 => 20,
        2 => 30,
        3 => 40,
        4 => 45,
        5 => 48,
        6 => 59,
        7 => 60,
    ];

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('reminders:crowdfunding:admin')
            ->setDescription('Automatic reminders sent to admin at several moments')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mailer = $this->getContainer()->get(MailDispatcher::class);

        $nb_mails = $this->sendPendingReminders($em, $mailer);
        $output->writeln($nb_mails . ' mails sent for PENDING contracts.');

        $nb_mails = $this->sendNewlySuccessfulReminders($em, $mailer);
        $output->writeln($nb_mails . ' mails sent for NEWLY SUCCESSFUL contracts.');

        $nb_mails = $this->sendRealityReminders($em, $mailer);
        $output->writeln($nb_mails . ' mails sent for SUCCESSFUL contracts which need their reality.');
    }

    private function sendPendingReminders(EntityManagerInterface $em, MailDispatcher $mailer) {
        $pendingContracts = $em->getRepository('AppBundle:ContractArtist')->findPending();

        $mails_sent = 0;

        foreach($pendingContracts as $contract) {
            /** @var ContractArtist $contract  */
            if($contract->getLastReminderAdmin() == null
                || ((new \DateTime())->diff($contract->getLastReminderAdmin())->days >= 1)) {
                $mailer->sendAdminPendingContract($contract);
                $mails_sent++;
                $contract->setLastReminderAdmin(new \DateTime());
                $em->persist($contract);
            }
        }
        $em->flush();

        return $mails_sent;
    }

    private function sendNewlySuccessfulReminders(EntityManagerInterface $em, MailDispatcher $mailer) {
        $successContracts = $em->getRepository('AppBundle:ContractArtist')->findNewlySuccessful();

        $mails_sent = 0;

        foreach($successContracts as $contract) {
            /** @var ContractArtist $contract  */

            if($contract->getLastReminderAdmin() == null
                || ((new \DateTime())->diff($contract->getLastReminderAdmin())->days >= 1)) {
                $mailer->sendAdminNewlySuccessfulContract($contract);
                $mails_sent++;
                $contract->setLastReminderAdmin(new \DateTime()); // to prevent "pending agression" of admins
                $em->persist($contract);
            }
        }
        $em->flush();

        return $mails_sent;
    }

    private function sendRealityReminders(EntityManagerInterface $em, MailDispatcher $mailer) {
        $successfulContracts = $em->getRepository('AppBundle:ContractArtist')->findSuccessful();

        $mails_sent = 0;

        foreach($successfulContracts as $contract) {
            /** @var ContractArtist $contract */
            $reminders = $contract->getRemindersAdmin();
            if($reminders < 8) {
                $nb_days = self::NB_DAYS_AFTER_SUCCESS[$reminders];

                if($contract->getReality() == null && (new \DateTime())->diff($contract->getDateEnd())->days >= $nb_days) {
                    // Send reminder
                    $mailer->sendAdminReminderContract($contract, $nb_days);
                    $mails_sent++;
                    $contract->setRemindersAdmin($contract->getRemindersAdmin() + 1);
                    $em->persist($contract);
                }
            }
        }
        $em->flush();

        return $mails_sent;
    }
}
