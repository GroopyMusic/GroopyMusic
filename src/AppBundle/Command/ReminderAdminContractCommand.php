<?php

namespace AppBundle\Command;

use AppBundle\Entity\ContractArtist;
use AppBundle\Services\MailTemplateProvider;
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
        $nb_mails = 0;

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mailer = $this->getContainer()->get('azine_email.default.template_twig_swift_mailer');

        $successfulContracts = $em->getRepository('AppBundle:ContractArtist')->findSuccessful();

        foreach($successfulContracts as $contract) {
            /**
             * @var ContractArtist $contract
             */
            $reminders = $contract->getRemindersAdmin();
            if($reminders < 8) {
                $nb_days = self::NB_DAYS_AFTER_SUCCESS[$reminders];

                if($contract->getReality() == null && (new \DateTime())->diff($contract->getDateEnd())->days >= $nb_days) {
                    // Send reminder
                    $from = $this->getContainer()->getParameter('email_from_address');
                    $fromName = $this->getContainer()->getParameter('email_from_name');

                    $to = 'gonzyer@gmail.com';
                    $toName = 'admin';

                    $params = ['contractArtist' => $contract, 'nbDays' => $nb_days];

                    $mailer->sendEmail($failedRecipients, "Sujet", $from, $fromName, $to, $toName, '', '',
                        array(), '', array(), '', $params, MailTemplateProvider::REMINDER_CONTRACT_ADMIN_TEMPLATE);
                    $nb_mails++;

                    $contract->setRemindersAdmin($contract->getRemindersAdmin() + 1);

                    $em->persist($contract);
                }
            }
        }
        $em->flush();
        $output->writeln('Done ; ' . $nb_mails . ' mails sent.');
    }
}
