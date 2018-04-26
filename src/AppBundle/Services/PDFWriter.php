<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Spipu\Html2Pdf\Html2Pdf;
use Symfony\Bundle\FrameworkBundle\Templating\Helper\AssetsHelper;
use Symfony\Component\Asset\Packages;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Router;
use Symfony\Component\Routing\RouterInterface;
use Twig\Environment;

class PDFWriter
{
    const ORDER_TEMPLATE = 'order.html.twig';
    const TICKETS_TEMPLATE = 'tickets.html.twig';

    private $twig;
    /** @var RouterInterface Router */
    private $router;
    private $packages;
    private $logger;

    public function __construct(Environment $twig, RouterInterface $router, Packages $packages, LoggerInterface $logger)
    {
        $this->twig = $twig;
        $this->router = $router;
        $this->packages = $packages;
        $this->logger = $logger;
    }

    public function write($template, $path, $params = [], $dest = 'F') {
        $html = $this->twig->render('AppBundle:PDF:' . $template, $params);
        $html2pdf = new Html2Pdf();
        /*$html2pdf->setDefaultFont('montserrat');

        try {
            // todo more robust way of adding font in PDF ; if font isn't find, HTML2PDF simply dies...
            $html2pdf->addFont('montserrat', '', 'pdf/font/montserrat.php');
            // TODO doesn't work
            $html2pdf->setDefaultFont('montserrat');
        } catch(\Exception $e) {
            $this->logger->warning('Montserrat font could not be added in PDF : ' . $e->getMessage());
        }*/

        $html2pdf->writeHTML($html);
        $html2pdf->output($path, $dest);
    }

    public function writeOrder(ContractFan $cf) {
        $cf->generateBarCode();
        $this->write(self::ORDER_TEMPLATE, $cf->getPdfPath(), ['cf' => $cf, 'user_rewards' => $cf->getUserRewards()]);
    }

    public function writeTickets($path, $tickets) {
        $this->write(self::TICKETS_TEMPLATE, $path, ['tickets' => $tickets]);
    }

    public function writeTicketPreview(ContractFan $cf) {
        $this->write(self::TICKETS_TEMPLATE, 'ticket_preview.pdf', ['tickets' => $cf->getTickets()], 'D');
    }
}