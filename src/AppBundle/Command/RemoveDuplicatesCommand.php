<?php

namespace AppBundle\Command;

use AppBundle\Entity\Ticket;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveDuplicatesCommand extends ContainerAwareCommand{

    /**
     * {@inheritdoc}
     */
    protected function configure(){
        $this
            ->setName('app:remove-duplicates')
            ->setDescription('Removes duplicate entries from DB such as tickets')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');

        try {
            $tickets = $em->getRepository('AppBundle:Ticket')->findAll(); // TODO getDuplicates()

            $removedTickets = [];
            $encounteredBarCodes = [];
            foreach($tickets as $ticket) {
                /**
                 * @var Ticket $ticket
                 */
                $bc = $ticket->getBarcodeText();
                if(!in_array($bc, $encounteredBarCodes)) {
                   $encounteredBarCodes[] = $bc;
                }
                else {
                    $removedTickets[] = $ticket;
                }
            }

            foreach($removedTickets as $removedTicket) {
                $em->remove($removedTicket);
            }

            $em->flush();
            $output->writeln("Commande de suppression de doublons effectuée avec succès : " . count($removedTickets) . ' tickets supprimés.');
        }
        catch(\Throwable $exception) {
            $output->writeln("La commande de suppression de doublons a rencontré une erreur : " . $exception->getMessage() . ' ' . $exception->getTraceAsString());
        }
    }

}