<?php

namespace AppBundle\Command\YB;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanPreviewTicketDirCommand extends ContainerAwareCommand {

    protected function configure(){
        $this
            ->setName('yb:clean-preview-tickets-directory')
            ->setDescription('Clean the directory that contains all the pdf generated while previewing tickets')
            ->setHelp("Thic command remove all the file from the directory 'web/yb/preview-tickets/'")
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $dirName = '/web/yb/preview-tickets/';
        $base_url = $this->getContainer()->get('kernel')->getProjectDir();
        $full_url = $base_url . $dirName;
        try {
            $dir = new \DirectoryIterator($full_url);
            $output->writeln('SUPPRESSION DES PDF DE PREVISUALISATION ...');
            foreach ($dir as $file) {
                if (!$file->isDot()) {
                    unlink($full_url . $file);
                }
            }
            $output->writeln('SUPPRESSION TERMINEE ...');
        } catch (\UnexpectedValueException $e){
            $output->writeln('Error : '.$e->getMessage());
        }
    }
}