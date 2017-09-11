<?php

namespace AppBundle\Command;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\MailTemplateProvider;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


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
        $this->sendMailsWhenOngoingCart($days, $output);

        $output->writeln('Done.');
    }

    private function sendMailsWhenOngoingCart($days, $output) {

        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $mailer = $container->get(MailDispatcher::class);

        $contracts = $em->getRepository('AppBundle:ContractArtist')->findCurrents();

        $currentDate = new \DateTime();

        foreach($contracts as $contract) {
            /** @var ContractArtist $contract */
            if(!$contract->getCartReminderSent() && $currentDate->diff($contract->getDateEnd())->days <= $days) {

                $carts = $em->getRepository('AppBundle:Cart')->findOngoingForContract($contract);

                $users = array_map(function (Cart $elem) {
                    return $elem->getUser();
                }, $carts);

                $mailer->sendOngoingCart($users, $contract);
                $contract->setCartReminderSent(true);
            }
        }
        $em->flush();
    }
}
