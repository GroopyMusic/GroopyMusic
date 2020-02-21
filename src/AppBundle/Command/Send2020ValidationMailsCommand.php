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

class Send2020ValidationMailsCommand extends ContainerAwareCommand {

    /**
     * {@inheritdoc}
     */
    protected function configure(){
        $this
            ->setName('app:send-2020-validation-mails')
            ->setDescription('')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        /** @var PaymentManager $paymentManager */
        $paymentManager = $this->getContainer()->get(PaymentManager::class);
        /** @var MailDispatcher $mailDispatcher */
        $mailDispatcher = $this->getContainer()->get(MailDispatcher::class);

        try {
            /** @var ContractArtist $contract */
            $contract = $em->getRepository('AppBundle:ContractArtist')->findVisibleIncludingPreValidation()[0];


                foreach($contract->getContractsFanPaid() as $cf) {
                    /** @var ContractFan $cf */

                    if($contract->allLineUpsCanceled()) {
                        // il faut rembourser tout
                        if(!$cf->isRefunded()) {
                            //$paymentManager->refundStripeAndUMContractFan($cf);
                            $mailDispatcher->sendRefundedContractFan($cf);
                            $cf->setRefunded(true);
                        }
                    }

                    else {
                        foreach($cf->getPurchases() as $purchase) {
                            if (!$purchase->getRefunded() && !$purchase->getConfirmed()) {
                                $em->persist($purchase);
                                /** @var Purchase $purchase */
                                $counterPart = $purchase->getCounterpart();
                                $artist = $purchase->getArtist();

                                if ($artist == null) {
                                    // Ticket combi, sans artiste
                                    if ($counterPart->isCombo()) {
                                        $yellow = !$contract->allLineUpsSuccessful();
                                        if ($contract->atLeastOneLineUpPerDayConfirmed()) {
                                            $mailDispatcher->sendConfirmedPurchase($purchase, $yellow);
                                            $purchase->setConfirmed(true);
                                        } else {
                                            //$paymentManager->refundPurchaseDifference($purchase);
                                            $mailDispatcher->sendHalfConfirmedPurchase($purchase, $yellow);
                                            $purchase->setConfirmed(true);
                                        }
                                    } // Ticket journalier, sans artiste
                                    else {
                                        if ($counterPart->getFestivaldays()->first()->atLeastOneLineUpConfirmed()) {
                                            $yellow = !$counterPart->getFestivaldays()->first()->allLineUpsConfirmed();
                                            $mailDispatcher->sendConfirmedPurchase($purchase, $yellow);
                                            $purchase->setConfirmed(true);
                                        } else {
                                            //$paymentManager->refundPurchase($purchase);
                                            $mailDispatcher->sendRefundedPurchase($purchase);
                                            $purchase->setRefunded(true);
                                        }
                                    }
                                } else {
                                    if ($contract->isCancelledArtist($artist)) {
                                        //$paymentManager->refundPurchase($purchase);
                                        $mailDispatcher->sendRefundedPurchase($purchase);
                                        $purchase->setRefunded(true);
                                    } // Ticket combi, avec artiste
                                    elseif ($counterPart->isCombo()) {
                                        // autre jour annulé -> ticket partiel
                                        if (!$contract->atLeastOneLineupPerDayConfirmed()) {
                                            //$paymentManager->refundPurchaseDifference($purchase);
                                            $mailDispatcher->sendHalfConfirmedPurchase($purchase);
                                            $purchase->setConfirmed(true);
                                        } // ticket 100 % confirmé
                                        else {
                                            $mailDispatcher->sendConfirmedPurchase($purchase);
                                            $purchase->setConfirmed(true);
                                        }
                                    } // Ticket journalier, avec artiste
                                    else {
                                        $mailDispatcher->sendConfirmedPurchase($purchase);
                                        $purchase->setConfirmed(true);
                                    }
                                }
                            }
                        }
                    }
                }

            $em->flush();
            $output->writeln("Commande de validation effectuée avec succès.");
        }
        catch(\Throwable $exception) {
            $output->writeln("La commande de validation a rencontré une erreur : " . $exception->getTraceAsString());
        }
    }
}