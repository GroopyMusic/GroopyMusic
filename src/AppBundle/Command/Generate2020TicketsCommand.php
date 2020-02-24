<?php

namespace AppBundle\Command;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Purchase;
use AppBundle\Services\TicketingManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Generate2020TicketsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:generate2020tickets');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        try {
            /** @var ContractArtist $contract */
            $contract = $em->getRepository('AppBundle:ContractArtist')->findVisibleIncludingPreValidation()[0];

            $j = 0;
            foreach ($contract->getContractsFanPaid() as $cf) {
                /** @var ContractFan $cf */
                if(!$cf->getcounterpartsSent() && $j < 5) {
                    foreach ($cf->getPurchases() as $purchase) {
                        /** @var Purchase $purchase */
                        if (!$purchase->getTicketsSent() && !$purchase->isCancelled() && $purchase->getConfirmed()) {
                            $em->persist($purchase);
                            $this->getContainer()->get(TicketingManager::class)->sendTicketsForPurchase($purchase);
                            $purchase->setTicketsSent(true);
                        }
                    }
                    $cf->setcounterpartsSent(true);
                }
            }
            $em->flush();
            $output->writeln("Commande de génération de tickets effectuée avec succès.");
        }
        catch(\Throwable $exception) {
            $output->writeln("La commande de validation a rencontré une erreur : " . $exception->getMessage() . " " . $exception->getTraceAsString());
        }
    }
}
