<?php

namespace XBundle\Command;

use AppBundle\Services\MailDispatcher;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;


/**
 * Class NotifyProjectToConfirmCommand
 * @package XBundle\Command
 */
class NotifyProjectToConfirmCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('x:notify-project-to-confirm')
            ->setDescription('Notify project handlers to confirm or refund the project')
            ->setHelp('This command allows to notify the project handlers to confirm or refund the project when the end date project has expired and the threshold has not been reached');
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine.orm.entity_manager');
        $mailDispatcher = $this->getContainer()->get(MailDispatcher::class);

        try {
            $projects = $em->getRepository('XBundle:Project')->findPendingProjects();

            foreach($projects as $project) {
                if (!$project->getNotifSent()) {
                    $mailDispatcher->sendProjectToConfirm($project);
                    $project->setNotifSent(true);
                }
            }
            $em->flush();
            $output->writeln("La commande pour notifier les gestionnaires du projet de le confirmer ou non effectée avec succès.");
        } catch (\Throwable $exception) {
            $output->writeln("La commande pour notifier les gestionnaires du projet de le confirmer ou non a rencontré une erreur : " . $exception->getMessage());
        }
        
        
    }


}