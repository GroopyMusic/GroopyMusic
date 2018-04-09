<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 09/04/2018
 * Time: 13:28
 */

namespace AppBundle\Command;


use AppBundle\Services\RewardSpendingService;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RewardCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('rewards:chdl')
            ->setDescription('check deadlines of each user_rewards');

    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rewardSpendingService = $this->getContainer()->get(RewardSpendingService::class);
        $output->writeln('-------------------------------');
        $output->writeln('Starting CHECK DEADLINES command');
        $output->writeln('-------------------------------');
        try {
            $rewardSpendingService->checkDeadlines();
        } catch (\Exception $ex) {
            $output->writeln('-------------------------------');
            $output->writeln('ERROR');
            $output->writeln($ex->getMessage());
            $output->writeln('-------------------------------');
            return;
        }
        $output->writeln('-------------------------------');
        $output->writeln('DONE');
        $output->writeln('-------------------------------');

    }
}