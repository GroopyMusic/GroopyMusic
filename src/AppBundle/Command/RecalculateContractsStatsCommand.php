<?php

namespace AppBundle\Command;

use AppBundle\Entity\ArtistPerformance;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\FestivalDay;
use AppBundle\Entity\LineUp;
use AppBundle\Entity\Purchase;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecalculateContractsStatsCommand extends ContainerAwareCommand {

    /**
     * {@inheritdoc}
     */
    protected function configure(){
        $this
            ->setName('app:recalculate-contracts-stats')
            ->setDescription('Updates the static, math-calculated fields of Contract Artists (such as nb of counterparts sold)')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        try {
            $contractArtists = $em->getRepository('AppBundle:ContractArtist')->findVisibleIncludingPreValidation();

            foreach($contractArtists as $contract) {
                $em->persist($contract);
                /** @var ContractArtist $contract */
                $dateVal = $contract->getDateSuccess();

                $contract->setCounterpartsSold(0);

                $aps = [];
                foreach($contract->getArtistPerformances() as $ap) {
                    $em->persist($ap);
                    /** @var ArtistPerformance $ap */
                    $ap->setTicketsSold(0);
                    $ap->setTicketsSoldPostVal(0);
                    $ap->setMoneyPoints(0);
                    $aps[$ap->getArtist()->getId()] = $ap;
                }
                foreach($contract->getFestivaldays() as $fd) {
                    $em->persist($fd);
                    $fd->setTicketsSold(0);
                }
                foreach($contract->getLineUps() as $lu) {
                    $em->persist($lu);
                    $lu->setTicketsSold(0);
                    $lu->setTicketsSoldPostVal(0);
                }

                foreach($contract->getCounterParts() as $cp) {
                    $em->persist($cp);
                    $cp->setNbSold(0);
                }

                foreach($contract->getContractsFanPaid() as $cf) {
                    /** @var ContractFan $cf */
                    $date = $cf->getDate();
                    $postval = $dateVal != null && $date >= $dateVal;

                    foreach($cf->getPurchases() as $purchase) {
                        /** @var Purchase $purchase */

                        if(!($postval && $purchase->getRefunded())) {
                            $cp = $purchase->getCounterpart();
                            //$output->writeln($cp->__toString() . ' x' . $purchase->getQuantity());
                            $cp->addNbSold($purchase->getQuantity());
                            $festivalDays = $cp->getFestivaldays();
                            $ti = $purchase->getThresholdIncrease();
                            $mi = $purchase->getMoneyIncrease();

                            $nbLineUps = 0;
                            // todo merge two next loops
                            foreach($festivalDays as $fd) {
                                foreach($fd->getLineUps() as $lineup) {
                                    if(!$lineup->isSoldOut()) {
                                        if(!$postval || $lineup->isSuccessful()) {
                                            $nbLineUps++;
                                        }
                                    }
                                }
                            }

                            $ti_lu = $nbLineUps == 0 ? 0 : $ti / $nbLineUps;

                           // $output->writeln($ti_lu);

                            foreach ($festivalDays as $festivalDay) {
                                /** @var FestivalDay $festivalDay */
                                $lineUps = $festivalDay->getLineups();
                                $lineUpsFiltered = array_filter($lineUps->toArray(), function (LineUp $lineUp) {
                                    return !$lineUp->isSoldOut();
                                });
                                if ($postval) {
                                    $lineUpsFiltered = array_filter($lineUpsFiltered, function (LineUp $lineUp) {
                                        return !$lineUp->isFailed();
                                    });
                                }

                                $fdIncrease = $ti_lu * count($lineUpsFiltered);
                                $festivalDay->addTicketsSold($fdIncrease);
                                $contract->addCounterpartsSold($fdIncrease);

                                if ($purchase->getFirstArtist() != null) {
                                    $aps[$purchase->getFirstArtist()->getId()]->addTicketsSold($fdIncrease);
                                    $aps[$purchase->getFirstArtist()->getId()]->getLineUp()->addTicketsSold($fdIncrease);
                                    if ($postval) {
                                        $aps[$purchase->getFirstArtist()->getId()]->addTicketsSoldPostVal($fdIncrease);
                                        $aps[$purchase->getFirstArtist()->getId()]->getLineUp()->addTicketsSoldPostVal($fdIncrease);
                                    }
                                    $aps[$purchase->getFirstArtist()->getId()]->addMoneyPoints($mi);
                                } else {
                                    foreach ($lineUpsFiltered as $lineUp) {
                                        /** @var LineUp $lineUp */
                                        // foreach($lineUp->getArtistPerformances() as $perf) {
                                        //    $aps[$perf->getArtist()->getId()]->addMoneyPoints($mi);
                                        //}
                                        $luIncrease = $ti_lu;
                                        $lineUp->addTicketsSold($luIncrease);
                                        if ($postval) {
                                            $lineUp->addTicketsSoldPostVal($luIncrease);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $em->flush();
            $output->writeln("Commande de recalcul effectuée avec succès.");
        }
        catch(\Throwable $exception) {
            $output->writeln("La commande de recalcul a rencontré une erreur : " . $exception->getMessage() . "\n" . $exception->getTraceAsString());
        }


    }

}