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
        /** @var MailDispatcher $mailDispatcher */
        $mailDispatcher = $this->getContainer()->get(MailDispatcher::class);
        /** @var ContractArtist $contract */
        $contract = $em->getRepository('AppBundle:ContractArtist')->findVisibleIncludingPreValidation()[0];

    $i = 0;

        if(true) {
            foreach($contract->getContractsFanPaid() as $cf) {
                foreach ($cf->getPurchases() as $purchase) {
                    if($purchase->getConfirmed()) {
                        $mailDispatcher->send2020Corona($purchase); 

                        $purchase->setConfirmed(false);
                        $em->persist($purchase);
                    }
                }
            }
        }
        else {
            foreach($contract->getContractsFanPaid() as $cf) {
                if ($i < 10) {
                    /** @var ContractFan $cf */

                    //if($contract->allLineUpsCanceled()) {
                    //    if(!$cf->isRefunded()) {
                    //        $mailDispatcher->sendRefundedContractFan($cf);
                    //    }
                    //}

                    //else {
                    foreach ($cf->getPurchases() as $purchase) {

                        if (!$purchase->getToRefund() && !$purchase->getConfirmed() && !$purchase->getRefunded()) {
                            //$i++;
                            $em->persist($purchase);
                            /** @var Purchase $purchase */
                            $counterPart = $purchase->getCounterpart();
                            $combi = $counterPart->isCombo();
                            if ($combi) {
                                $purchase->setToRefund(true);
                            }
                            $artist = $purchase->getArtist();

                            if ($artist == null) {
                                // Ticket combi, sans artiste
                                // if ($combi) {
                                //$yellow = !$contract->allLineUpsSuccessful();
                                //if ($contract->atLeastOneLineUpPerDayConfirmed()) {
                                $mailDispatcher->sendConfirmedPurchase($purchase, $combi);
                                $purchase->setConfirmed(true);
                                //} else {
                                //    $mailDispatcher->sendHalfConfirmedPurchase($purchase, $yellow);
                                //    $purchase->setConfirmed(true)->setToRefund(true);
                                //}
                                // } // Ticket journalier, sans artiste
                                //else {
                                //if ($counterPart->getFestivaldays()->first()->atLeastOneLineUpConfirmed()) {
                                //    $yellow = !$counterPart->getFestivaldays()->first()->allLineUpsConfirmed();
                                //    $mailDispatcher->sendConfirmedPurchase($purchase, $combi);
                                //    $purchase->setConfirmed(true);
                                //} else {
                                //    $mailDispatcher->sendRefundedPurchase($purchase, false);
                                //    $purchase->setToRefund(true);
                                //}
                                // }
                            } else {
                                // échec
                                if ($contract->isCancelledArtist($artist)) {
                                    //$cancellable = !$contract->getDayOfPerformance($artist)->allLineUpsCancelled();
                                    $mailDispatcher->sendRefundedPurchase($purchase, $combi);
                                    $purchase->setToRefund(true);
                                } else {// Ticket combi, avec artiste
                                    //elseif ($combi) {
                                    // autre jour annulé -> ticket partiel
                                    //if (!$contract->atLeastOneLineupPerDayConfirmed()) {
                                    //    $mailDispatcher->sendHalfConfirmedPurchase($purchase);
                                    //    $purchase->setConfirmed(true)->setToRefund(true);
                                    //} // ticket 100 % confirmé
                                    //else {
                                    $mailDispatcher->sendConfirmedPurchase($purchase, $combi);
                                    $purchase->setConfirmed(true);
                                }
                                //}
                                // } // Ticket journalier, avec artiste
                                // else {
                                //    $mailDispatcher->sendConfirmedPurchase($purchase);
                                //    $purchase->setConfirmed(true);
                                // }
                            }
                        }
                    }
                //}
                }
            }
        }


        $em->flush();
        $output->writeln("Commande de validation effectuée avec succès.");
    }
}