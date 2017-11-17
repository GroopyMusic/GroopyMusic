<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class InitCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:init');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $application = $this->getApplication();

        $output->writeln('-------------------------------');
        $output->writeln('Running MIGRATIONS command');
        $output->writeln('-------------------------------');
        $command = $application->find('doctrine:migrations:migrate');
        $arguments = array(
            '--no-interaction' => true,
            '--allow-no-migration' => true,
        );

        $command->run(new ArrayInput($arguments), $output);

        $output->writeln('-------------------------------');
        $output->writeln('Running ASSETS:INSTALL command');
        $output->writeln('-------------------------------');
        $command = $application->find('assets:install');
        $arguments = array(
            '--symlink' => true,
        );

        $command->run(new ArrayInput($arguments), $output);

        $output->writeln('-------------------------------');
        $output->writeln('Running ASSETIC:DUMP command');
        $output->writeln('-------------------------------');
        $command = $application->find('assetic:dump');
        $arguments = array(
            '--env' => 'dev',
        );

        $command->run(new ArrayInput($arguments), $output);

        $output->writeln('-------------------------------');
        $output->writeln('DONE');
        $output->writeln('-------------------------------');
    }
}
