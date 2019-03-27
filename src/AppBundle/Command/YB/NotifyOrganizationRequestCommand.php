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
class NotifyOrganizationRequestCommand extends ContainerAwareCommand{

    /**
     * {@inheritdoc}
     */
    protected function configure(){
        $this
            ->setName('yb:organization-request-notify')
            ->setDescription('Notify a user that the person he/she invited 
            to join his/her organization has not answered his/her call and therefore the invitation is cancelled')
            ->setHelp('This command verifies if there are pending request that are been hanging for at least 1 month.
            If that is the case, the request is cancelled and a mail is send to notify the demander.')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output){
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mailer = $this->getContainer()->get(MailDispatcher::class);
        $pendingRequests = $em->getRepository('AppBundle:YB\OrganizationJoinRequest')->findAll();
        $cptOutlastRequest = 0;
        if (count($pendingRequests) !== 0){
            foreach ($pendingRequests as $request){
                if ((new \DateTime()) >= $request->getDate()->add(new \DateInterval('P1M'))){
                    $em->remove($request);
                    $mailer->sendYBNotifyOrganizationRequestCancel($request);
                    $message = 'La demande de '.$request->getDemander()->getDisplayName().' destinée à '.$request->getEmail().' pour rejoindre l\'organisation '.$request->getOrganization()->getName().' a été annulée !';
                    $output->writeln($message);
                    $cptOutlastRequest++;
                }
            }
        }
        if ($cptOutlastRequest === 0){
            $output->writeln("Aucune demande n'a dépassé le délai de 1 mois");
        }
        $em->flush();
    }

}