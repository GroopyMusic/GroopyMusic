<?php

namespace AppBundle\Command;

use AppBundle\Services\MailDispatcher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TooManyPaymentsCommand extends ContainerAwareCommand
{
    const DAYS_INTERVAL = 30;
    const MANY_PAYMENTS_AMOUNT = 500;

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('infos:many-payments')
            ->setDescription('Sends an e-mail to admins when too many payments are received from the same user.');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $users = $em->getRepository('AppBundle:User')->findWithPaymentLastXDays(self::DAYS_INTERVAL);

        foreach($users as $user) {
            if($user->getAmountPaidLastXDays() > self::MANY_PAYMENTS_AMOUNT) {
                $this->getContainer()->get(MailDispatcher::class)->sendAdminEnormousPayer($user);
            }
        }
    }
}
