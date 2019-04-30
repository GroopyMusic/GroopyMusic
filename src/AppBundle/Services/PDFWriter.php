<?php

namespace AppBundle\Services;

use AppBundle\Entity\Cart;
use AppBundle\Entity\ContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\CounterPart;
use AppBundle\Entity\User;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\YBInvoice;
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
    const YB_TICKETS_TEMPLATE = 'yb_tickets.html.twig';
    const YB_INVOICE_SOLD_TEMPLATE = 'yb_invoice_sold.html.twig';

    const X_TICKETS_TEMPLATE = 'x_tickets.html.twig';

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

    public function writeOrder(Cart $cart) {
        foreach($cart->getContracts() as $cf) {
            $cf->generateBarCode();
        }
        $cart->generateBarCode();
        $pdfpath = $cart->getPdfPath();

        $this->write(self::ORDER_TEMPLATE, $pdfpath, ['cart' => $cart]);//, 'user_rewards' => $cart->getUserRewards()]);
    }

    public function writeTickets($path, $cf, $tickets, $agenda = []) {
        if(isset($tickets[0])) {
            // We know all tickets are for same event
            $this->write(self::TICKETS_TEMPLATE, $path, ['tickets' => $tickets, 'agenda' => $agenda, 'cf' => $cf]);
        }
    }

    public function writeYBTickets($path, $tickets, $agenda = []) {
        if(!empty($tickets)) {
            // We know all tickets are for same event
            $this->write(self::YB_TICKETS_TEMPLATE, $path, ['tickets' => $tickets, 'agenda' => $agenda]);
        }
    }

    public function writeTicketPreview(ContractFan $cf, $agenda = []) {
        $tickets = $cf->getTickets();
        $cf = $tickets[0]->getContractFan();
        if(!empty($tickets)) {
            $this->write(self::TICKETS_TEMPLATE, 'ticket_preview.pdf', ['tickets' => $tickets, 'agenda' => $agenda, 'cf' => $cf], 'D');
        }
    }


    public function writeSoldInvoice(YBInvoice $invoice = null, $ticketData, YBContractArtist $campaign, $cfs) {
        $this->write(self::YB_INVOICE_SOLD_TEMPLATE, 'invoice.pdf', ['invoice' => $invoice,
            'ticketData' => $ticketData,
            'campaign' => $campaign,
            'cfs' => $cfs,
        ], 'D');
    }


    // ------------------------ X

    public function writeXTickets($path, $tickets) {
        if(!empty($tickets)) {
            $html = $this->twig->render('XBundle:PDF:' . self::X_TICKETS_TEMPLATE, ['tickets' => $tickets]);
            $html2pdf = new Html2Pdf();
            $html2pdf->writeHTML($html);
            $html2pdf->output($path, 'F');
        }
    }
}