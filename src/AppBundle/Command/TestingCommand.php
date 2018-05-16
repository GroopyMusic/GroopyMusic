<?php
/**
 * Created by PhpStorm.
 * User: Jean-François Cochar
 * Date: 16/05/2018
 * Time: 10:31
 */

namespace AppBundle\Command;


use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('app:run_test');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('-------------------------------');
        $output->writeln('RUN UNIT TEST');
        $output->writeln('-------------------------------');
        $output->writeln('___  _  _  ___  _  __     __ | _  _  ___    _     _        ___  _  __');
        $output->writeln(' |  |_ |_   |  |_ |__|   |    |_ |_   |    |  \\  / \\  |  |  |  |_ |__|');
        $output->writeln(' |  |_  _|  |  |_ |  \\   |__  |_  _|  |    |__/  \\_/  |__|  |  |_ |  \\');

        exec('php vendor/symfony/phpunit-bridge/bin/simple-phpunit',$test_output);
        $output->writeln($test_output);

        $output->writeln('-------------------------------');
        $output->writeln('UNIT TEST DONE');
        $output->writeln('-------------------------------');
    }
}