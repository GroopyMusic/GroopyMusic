<?php

namespace AppBundle\Command;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\FestivalDay;
use AppBundle\Entity\Purchase;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RecalculateContractsStatsCommand extends ContainerAwareCommand{

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
            $contractArtists = $em->getRepository('AppBundle:BaseContractArtist')->findAll();

            foreach($contractArtists as $contract) {
                $cfs = $contract->getContractsFanPaid();
                $contract->setCounterpartsSold(floor(array_sum(array_map(function(ContractFan $cf) {
                    return $cf->getThresholdIncrease();
                }, $cfs))));

                if($contract instanceof ContractArtist) {
                    foreach($contract->getFestivaldays() as $festivalday) {
                        /**
                         * @var FestivalDay $festivalday */
                        $sum = 0;
                        foreach($cfs as $cf) {
                            /** @var ContractFan $cf */
                            foreach($cf->getPurchases() as $purchase) {
                                /** @var Purchase $purchase */
                                $cp = $purchase->getCounterpart();
                                if($cp->getFestivaldays()->contains($festivalday)) {
                                    $sum += $purchase->getThresholdIncrease();
                                }
                            }
                        }
                        $festivalday->setTicketsSold(floor($sum));
                        $em->persist($festivalday);
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