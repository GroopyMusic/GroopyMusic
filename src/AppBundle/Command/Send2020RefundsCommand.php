<?php

namespace AppBundle\Command;

use AppBundle\Entity\ArtistPerformance;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\FestivalDay;
use AppBundle\Entity\LineUp;
use AppBundle\Entity\Purchase;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\PaymentManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Send2020RefundsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:send-2020-refunds')
            ->setDescription('Hello PhpStorm');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var PaymentManager $paymentManager */
        $paymentManager = $this->getContainer()->get(PaymentManager::class);
        $paymentManager->initStripe();

        /** @var ContractArtist $contract */
        $contract = $em->getRepository('AppBundle:ContractArtist')->findVisibleIncludingPreValidation()[0];

        if($contract->allLineUpsCanceled()) {
            foreach ($contract->getContractsFanPaid() as $cf) {
                /** @var ContractFan $cf */
                // il faut rembourser tout
                if (!$cf->isRefunded()) {
                    $paymentManager->refundStripeAndUMContractFan($cf);
                    $cf->setRefunded(true);
                }
            }
        }

        else {
            foreach($contract->getPurchasesPaid() as $purchase) {
                /** @var Purchase $purchase */
                if ($purchase->getToRefund() && !$purchase->getRefunded()) {
                    $em->persist($purchase);

                    $counterPart = $purchase->getCounterpart();
                    $artist = $purchase->getArtist();

                    if ($artist == null) {
                        // Ticket combi, sans artiste
                        if ($counterPart->isCombo()) {
                            if (!$contract->atLeastOneLineUpPerDayConfirmed()) {
                                $paymentManager->refundPurchaseDifference($purchase);
                                $purchase->setRefunded(true);
                            }
                        } // Ticket journalier, sans artiste
                        else {
                            if (!$counterPart->getFestivaldays()->first()->atLeastOneLineUpConfirmed()) {
                                $paymentManager->refundPurchase($purchase);
                                $purchase->setRefunded(true);
                            }
                        }
                    } else {
                        if ($contract->isCancelledArtist($artist)) {
                            $paymentManager->refundPurchase($purchase);
                        } // Ticket combi, avec artiste
                        elseif ($counterPart->isCombo()) {
                            // autre jour annulé -> ticket partiel
                            if (!$contract->atLeastOneLineupPerDayConfirmed()) {
                                $paymentManager->refundPurchaseDifference($purchase);
                                $purchase->setRefunded(true);
                            } // ticket 100 % confirmé
                        }
                    }
                }
            }
        }

        $em->flush();
        $output->writeln("Commande de REMBOURSEMENTS effectuée avec succès.");
    }
}
