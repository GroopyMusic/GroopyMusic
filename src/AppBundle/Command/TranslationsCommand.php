<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TranslationsCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:translations')
            ->addOption('all')
            ->addOption('routes', 'r')
            ->addOption('app');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $all = $input->getOption('all');
        $routes = $input->getOption('routes');
        $app = $input->getOption('app');

        $application = $this->getApplication();

        $commands = ['routes' => false, 'app' => false];

        if($all) {
            $output->writeln('ALL extracts requested');
            $output->writeln('-------------------------------');
            $commands = array_map(function() {return true;}, $commands);
        }

        else {
            if($routes) {
                $output->writeln('ROUTES extract requested...');
                $output->writeln('-------------------------------');
                $commands['routes'] = true;
            }
            if($app) {
                $output->writeln('APP extract requested...');
                $output->writeln('-------------------------------');
                $commands['app'] = true;
            }
            else {
                $output->writeln('No extract requested...');
            }
        }
        if($commands['routes']) {
            $output->writeln('-------------------------------');
            $output->writeln('Extracting ROUTES translations...');
            $output->writeln('-------------------------------');
            $command = $application->find('translation:extract');
            $arguments = array(
                '--config' => 'routes',
                'locales' => ['fr'],
            );

            $command->run(new ArrayInput($arguments), $output);
        }

        if($commands['app']) {
            $output->writeln('-------------------------------');
            $output->writeln('Extracting APP translations...');
            $output->writeln('-------------------------------');
            $command = $application->find('translation:extract');
            $arguments = array(
                '--config' => 'app',
                'locales' => ['fr'],
            );

            $command->run(new ArrayInput($arguments), $output);
        }

        $output->writeln('');
        $output->writeln('Gounzy-Extraction done !');
    }
}
