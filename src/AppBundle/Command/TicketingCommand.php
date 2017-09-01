<?php

namespace AppBundle\Command;

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
        $mailer = $this->getContainer()->get('azine_email_template_twig_swift_mailer');

        $successfulContracts = $em->getRepository('AppBundle:ContractArtist')->findSuccessful();

        foreach($successfulContracts as $sc) {
            $reality = $sc->getReality();

            if((new \DateTime())->diff($reality->getDate())->days <= $days) {
                $contractsFan = $sc->getContractsFan();

                foreach($contractsFan as $cf) {
                    if(!$cf->getTicketSend()) {

                        $cf->generateBarCode();

                        $html2pdf = new Html2Pdf();
                        $html2pdf->writeHTML($this->renderView('AppBundle:PDF:ticket.html.twig', array('contractFan' => $cf, 'contractArtist' => $sc)));
                        $html2pdf->Output('pdf/tickets/'.$cf->getBarCodeText().'.pdf', 'F');

                        $attachments = ['votreContrat.pdf' => $this->get('kernel')->getRootDir() . '\..\web\pdf\tickets\\' . $cf->getBarCodeText().'.pdf'];

                        $from = $this->getContainer()->getParameter('email_from_address');
                        $fromName = $this->getContainer()->getParameter('email_from_name');

                        $to = $cf->getFan()->getEmail();
                        $toName = $cf->getFan()->getDisplayName();
                        $subject = "subject";

                        $params = [];

                        $mailer->sendEmail($failedRecipients, $subject, $from, $fromName, $to, $toName, array(), '',
                            array(), '', array(), '', $params, MailTemplateProvider::TICKET_TEMPLATE, $attachments, 'fr');

                        $cf->setTicketSent(true);
                        $em->persist($cf);
                    }
                }
            }
        }

        $em->flush();
    }
}
