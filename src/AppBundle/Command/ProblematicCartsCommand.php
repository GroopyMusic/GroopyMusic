<?php

namespace AppBundle\Command;

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

        $result = $this->notifyWhenProblematicCart();

        $output->writeln('Done ; ' . $result . ' notifications sent');
    }

    private function notifyWhenProblematicCart() {
        $container = $this->getContainer();
        $em = $container->get('doctrine.orm.entity_manager');
        $notifier = $container->get('email.notifier_service');

        $carts = $em->getRepository('AppBundle:Cart')->findWithUsersAndContracts();

        $nb_notifs = 0;

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
                $title = "Panier modifié";
                $content = "Wouhouuu";
                $recipientId = $cart->getUser()->getId();

                $notifier->addNotificationMessage($recipientId, $title, $content);
                $nb_notifs++;
            }
        }
        $em->flush();

        return $nb_notifs;
    }
}
