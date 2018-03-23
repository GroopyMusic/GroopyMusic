<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 20/03/2018
 * Time: 15:09
 */

namespace AppBundle\Command;


use AppBundle\Services\NotificationDispatcher;
use AppBundle\Services\RankingService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RankingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('statistics:compute')
            ->setDescription('Compute statistics of all users');

    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rankingService = $this->getContainer()->get(RankingService::class);
        $notificationDispatcher = $this->getContainer()->get(NotificationDispatcher::class);
        $output->writeln('-------------------------------');
        $output->writeln('Starting COMPUTE STAT command');
        $output->writeln('-------------------------------');
        try {
            $rankingService->computeAllStatistic();
        } catch (\Exception $ex) {
            $notificationDispatcher->notifyAdminErrorStatisticComputation($ex->getMessage());
            $output->writeln('-------------------------------');
            $output->writeln('ERROR');
            $output->writeln('-------------------------------');
            return;
        }
        $output->writeln('-------------------------------');
        $output->writeln('DONE');
        $output->writeln('-------------------------------');

    }

}