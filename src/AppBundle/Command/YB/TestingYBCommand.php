<?php

namespace AppBundle\Command\YB;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestingYBCommand extends ContainerAwareCommand {

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:run_test_yb');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('___  _  _  ___  _  __     __ | _  _  ___    _     _        ___  _  __');
        $output->writeln(' |  |_ |_   |  |_ |__|   |    |_ |_   |    |  \\  / \\  |  |  |  |_ |__|');
        $output->writeln(' |  |_  _|  |  |_ |  \\   |__  |_  _|  |    |__/  \\_/  |__|  |  |_ |  \\');
        $output->writeln('-------------------------------');
        $output->writeln('RUN UNIT TEST ENTITY');
        $output->writeln('-------------------------------');
        exec('php vendor/symfony/phpunit-bridge/bin/simple-phpunit tests/AppBundle/Entity', $unit_test_output);
        $output->writeln($unit_test_output);
        $output->writeln('-------------------------------');
        $output->writeln('UNIT TEST DONE');
        $output->writeln('-------------------------------');
    }

}