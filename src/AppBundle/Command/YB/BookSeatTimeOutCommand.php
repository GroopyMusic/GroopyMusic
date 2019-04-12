<?php

namespace AppBundle\Command\YB;


use AppBundle\Services\MailDispatcher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class NotifyOrganizationRequestCommand
 * @package AppBundle\Command\YB
 *
 * Command to be executed every day !!!!
 *
 * It fetches the pending request send for people to join organizations
 * The request that have been hanging for at least 1 month are automatically deleted
 * The demander (user that invited someone) receives a mail that notifies him/her of the deletion
 */
class BookSeatTimeOutCommand extends ContainerAwareCommand{

    /**
     * {@inheritdoc}
     */
    protected function configure(){
        $this
            ->setName('yb:book-seat-timeout')
            ->setDescription('If a booking session has timeout, all the pre-booked seat are made available again')
            ->setHelp('This command verifies if there are timed out booking session. Once a user start booking seats, he has 15min to complete the booking otherwise the seats are made available again.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $timedOutSession = $em->getRepository('AppBundle:YB\Booking')->getTimedoutReservations();
        $cptTimedoutSession = 0;
        if (count($timedOutSession) !== 0){
            foreach ($timedOutSession as $reservation){
                $em->remove($reservation);
                $output->writeln('La réservation numéro '.$reservation->getId().' a été annulée.');
                $cptTimedoutSession++;
            }
        }
        if ($cptTimedoutSession === 0){
            $output->writeln("Aucune demande n'a dépassé le délai de 1 mois");
        }
        $em->flush();
    }

}