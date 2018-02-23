<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContractFan;
use Spipu\Html2Pdf\Html2Pdf;
use Twig\Environment;

class PDFWriter
{
    const ORDER_TEMPLATE = 'order.html.twig';

    private $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    public function write($template, $path, $params = []) {
        $html = $this->twig->render('AppBundle:PDF:' . $template, $params);
        $html2pdf = new Html2Pdf();
        $html2pdf->writeHTML($html);
        $html2pdf->output($path, 'F');
    }

    public function writeOrder(ContractFan $cf) {
        $cf->generateBarCode();
        $this->write(self::ORDER_TEMPLATE, $cf->getPdfPath(), ['cf' => $cf]);
    }
}