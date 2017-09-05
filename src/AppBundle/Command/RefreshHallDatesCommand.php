<?php

namespace AppBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshHallDatesCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('halls:refresh:dates')
            ->setDescription('Refresh dates based on admin-defined rules');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        $halls = $em->getRepository('AppBundle:Hall')->findAll();

        foreach($halls as $hall) {
            $output->writeln('Processing hall : ' . $hall);

            $hall->refreshDates();
            $em->persist($hall);
        }

        $em->flush();

        $output->writeln('Done !');
    }
}
