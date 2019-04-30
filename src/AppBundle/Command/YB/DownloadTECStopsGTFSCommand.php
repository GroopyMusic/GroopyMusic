<?php

namespace AppBundle\Command\YB;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DownloadTECStopsGTFSCommand extends ContainerAwareCommand{

    protected function configure(){
        $this
            ->setName('yb:download-tec-stops-gtfs')
            ->setDescription('Update the list of TEC stops by downloading the GTFS from OpenDataWallonia')
            ->setHelp('This command download the GTFS file that gathers all the TEC (bus/tramway) stops in Belgium.')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output){
        $root_url = '/Applications/MAMP/htdocs/GroopyMusic/web/';
        $url = 'http://opendata.tec-wl.be/Current%20GTFS/TEC-GTFS.zip';
        $output->writeln('TELECHARGEMENT DU ZIP ...');
        $content = file_get_contents($url);
        if ($content === false){
            $output->writeln("Error : lecture de l'url " . $url . " impossible...");
        } else {
            file_put_contents($root_url . 'yb/file-from-tec.zip', $content);
            $output->writeln('ZIP TELECHARGE ...');
            $zip = new \ZipArchive();
            $res = $zip->open($root_url . 'yb/file-from-tec.zip');
            if ($res === true){
                $output->writeln('EXTRACTION DU ZIP ...');
                $zip->extractTo($root_url . 'yb/file-from-tec/');
                $output->writeln('FICHIERS TXT EXTRAITS ...');
                $zip->close();
                try {
                    $dir = new \DirectoryIterator($root_url . 'yb/file-from-tec');
                    $output->writeln('SUPPRESSION DES FICHIERS INUTILES ...');
                    foreach ($dir as $file) {
                        if (!$file->isDot() && $file->getFilename() !== 'stops.txt') {
                            unlink($root_url . 'yb/file-from-tec/' . $file);
                        }
                    }
                    $output->writeln('SUPPRESSION TERMINEE ...');
                    $output->writeln('Le fichier stops.txt a été mis à jour !');
                } catch (\UnexpectedValueException $e){
                    $output->writeln('Error : '.$e->getMessage());
                }
            } else {
                $output->writeln('Erreur lors de l\'ouverture du fichier : ' . $res);
            }
        }
    }

}