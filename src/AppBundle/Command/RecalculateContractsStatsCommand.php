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
            $contractArtists = $em->getRepository('AppBundle:ContractArtist')->findVisible();

            foreach($contractArtists as $contract) {
                $em->persist($contract);
                /** @var ContractArtist $contract */
                $contract->setCounterpartsSold(0);

                $aps = [];
                foreach($contract->getArtistPerformances() as $ap) {
                    $em->persist($ap);
                    /** @var ArtistPerformance $ap */
                    $ap->setTicketsSold(0);
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
                }

                foreach($contract->getContractsFanPaid() as $cf) {
                    /** @var ContractFan $cf */

                    foreach($cf->getPurchases() as $purchase) {
                        /** @var Purchase $purchase */
                        $cp = $purchase->getCounterpart();
                        $festivalDays = $cp->getFestivaldays();
                        $ti = $purchase->getThresholdIncrease();
                        $mi = $purchase->getMoneyIncrease();
                        $nbFestivalDays = count($festivalDays);
                        foreach($festivalDays as $festivalDay) {
                            /** @var FestivalDay $festivalDay */
                            $fdIncrease = $ti/$nbFestivalDays;
                            $festivalDay->addTicketsSold($fdIncrease);
                            $contract->addCounterpartsSold($fdIncrease);
                            $lineUps = $festivalDay->getLineups();
                            if($purchase->getFirstArtist() != null) {
                                $aps[$purchase->getFirstArtist()->getId()]->addTicketsSold($fdIncrease);
                                $aps[$purchase->getFirstArtist()->getId()]->getLineUp()->addTicketsSold($fdIncrease);
                                $aps[$purchase->getFirstArtist()->getId()]->getLineUp()->addMoneyPoints($mi);
                            }
                            else {
                                $nbLineUps = count($lineUps);
                                foreach ($lineUps as $lineUp) {
                                    /** @var LineUp $lineUp */
                                    foreach($lineUp->getArtistPerformances() as $perf) {
                                        $aps[$perf->getArtist()->getId()]->addMoneyPoints($mi);
                                    }
                                    $luIncrease = $fdIncrease/$nbLineUps;
                                    $lineUp->addTicketsSold($luIncrease);
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
            $output->writeln("La commande de recalcul a rencontré une erreur : " . $exception->getMessage());
        }


    }

}