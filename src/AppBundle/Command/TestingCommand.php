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
        $output->writeln('___  _  _  ___  _  __     __ | _  _  ___    _     _        ___  _  __');
        $output->writeln(' |  |_ |_   |  |_ |__|   |    |_ |_   |    |  \\  / \\  |  |  |  |_ |__|');
        $output->writeln(' |  |_  _|  |  |_ |  \\   |__  |_  _|  |    |__/  \\_/  |__|  |  |_ |  \\');
        /*$output->writeln('-------------------------------');
        $output->writeln('RUN UNIT TEST SERVICE');
        $output->writeln('-------------------------------');
        exec('php vendor/symfony/phpunit-bridge/bin/simple-phpunit tests/AppBundle/Services', $unit_test_output);
        $output->writeln($unit_test_output);
        $output->writeln('-------------------------------');
        $output->writeln('UNIT TEST DONE');
        $output->writeln('-------------------------------');*/
        $output->writeln('-------------------------------');
        $output->writeln('RUN FUNCTIONAL TEST CONTROLLER');
        $output->writeln('-------------------------------');
        exec('php vendor/symfony/phpunit-bridge/bin/simple-phpunit tests/AppBundle/Controller/UserControllerTest', $functional_test_output);
        $output->writeln($functional_test_output);
        $output->writeln('-------------------------------');
        $output->writeln('FUNCTIONAL TEST DONE');
        $output->writeln('-------------------------------');
    }
}