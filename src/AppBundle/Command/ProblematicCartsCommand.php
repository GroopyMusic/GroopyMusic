<?php

namespace AppBundle\Command;

use AppBundle\Services\NotificationDispatcher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProblematicCartsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('infos:carts:problematic')
            ->setDescription('Remove impossible elements from carts and notify user');
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

        $this->notifyWhenProblematicCart();

        $output->writeln('Done.');
    }

    private function notifyWhenProblematicCart() {
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $notifier = $container->get(NotificationDispatcher::class);

        $carts = $em->getRepository('AppBundle:Cart')->findWithUsersAndContracts();

        foreach($carts as $cart) {

            $changeDone = false;

            $contracts = $cart->getContracts();

            foreach($contracts as $contract) {
                $removed = false;
                $contract_artist = $contract->getContractArtist();

                // TODO stop duplicating this code from Cart::isProblematic()
                foreach($contract->getPurchases() as $purchase) {
                    if($contract_artist->cantAddPurchase($purchase->getQuantity(), $purchase->getCounterPart())) {
                        $contract_artist->removeContractsFan($contract);
                        $cart->removeContract($contract);
                        $em->remove($contract);
                        $removed = true;
                        $changeDone = true;
                    }
                }

                if(!$removed && $contract_artist->getSuccessful() || $contract_artist->getFailed()) {
                    $contract_artist->removeContractsFan($contract);
                    $cart->removeContract($contract);
                    $em->remove($contract);
                    $changeDone = true;
                }
            }

            if($changeDone) {
                // Notification creation
                $notifier->notifyProblematicCart($cart);
            }
        }
        $em->flush();

    }
}
