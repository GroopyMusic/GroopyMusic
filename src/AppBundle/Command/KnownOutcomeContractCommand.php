<?php

namespace AppBundle\Command;

use AppBundle\Entity\ContractArtist;
use AppBundle\Services\MailDispatcher;
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
        $this->sendMailsWhenKnownOutcome($success);

        $output->writeln('Done');
    }

    private function sendMailsWhenKnownOutcome($success) {

        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $mailer = $container->get(MailDispatcher::class);

        if($success) {
            $contracts = $em->getRepository('AppBundle:ContractArtist')->findNewlySuccessful();
        } else {
            $contracts = $em->getRepository('AppBundle:ContractArtist')->findNewlyFailed();
        }

        foreach($contracts as $contract) {
            /* @var ContractArtist $contract */

            $artist_users = $contract->getArtistProfiles();
            $fan_users = $contract->getFanProfiles();
            $fan_contracts = $contract->getContractsFan();

            $mailer->sendKnownOutcomeContract($contract, $success, $artist_users, $fan_users);

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
    }
}
