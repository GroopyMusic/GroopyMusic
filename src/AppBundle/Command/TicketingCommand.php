<?php

namespace AppBundle\Command;

use AppBundle\Entity\ContractArtist;
use AppBundle\Services\MailDispatcher;
use AppBundle\Services\MailTemplateProvider;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TicketingCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('tickets:send')
            ->setDescription('Send tickets X days before concert')
            ->addArgument('days', InputArgument::REQUIRED, 'X')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $days = intval($input->getArgument('days'));

        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mailer = $this->getContainer()->get(MailDispatcher::class);

        $successfulContracts = $em->getRepository('AppBundle:ContractArtist')->findSuccessful();

        foreach($successfulContracts as $sc) {
            /** @var ContractArtist $sc */
            $reality = $sc->getReality();

            if((new \DateTime())->diff($reality->getDate())->days <= $days) {
                $contractsFan = $sc->getContractsFan();

                foreach($contractsFan as $cf) {
                    if(!$cf->getTicketSend()) {

                        $cf->generateBarCode();

                        // TODO should be per person
                        $ticket_html = $this->getContainer()->get('twig')->render('AppBundle:PDF:ticket.html.twig', array('contractFan' => $cf, 'contractArtist' => $sc));
                        $mailer->sendTicket($ticket_html, $cf, $sc);

                        $cf->setTicketSent(true);
                        $em->persist($cf);
                    }
                }
            }
        }

        $em->flush();
    }
}
