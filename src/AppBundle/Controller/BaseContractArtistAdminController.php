<?php

namespace AppBundle\Controller;

use AppBundle\Entity\BaseContractArtist;
use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Purchase;
use AppBundle\Services\StringHelper;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseContractArtistAdminController extends BaseAdminController
{
    public function paymentsAction(Request $request, UserInterface $user, StringHelper $strHelper)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var BaseContractArtist $contract */
        $contract = $this->admin->getSubject();

        // ask the service for a Excel5
        $phpExcelObject = $this->get('phpexcel')->createPHPExcelObject();

        $phpExcelObject->getProperties()->setCreator("Ticked-it.be")
            ->setLastModifiedBy("Ticked-it robot")
            ->setTitle("Détail d'un événement")
            ->setSubject("Commandes")
            ->setDescription("Commandes pour " . $contract)
            ->setKeywords("commandes");

        $cfs = $contract->getContractsFanPaid();

        if (count($cfs) > 0) {
            $colonnes = array(
                '# commande', // A
                'Date', // B
                'Stripe', // C
                'Tickets', // D
                'Montant unitaire TTC', // E
                'Montant total TTC', // F
                'Quantité (organique)', // G
                'Quantité (promos)', // H
                'Quantité (total)', // I
            );

            $lettre = "A";

            foreach ($colonnes as $colonne) {
                $phpExcelObject->setActiveSheetIndex(0)->setCellValue($lettre . '1', $colonne);
                $lettre++;
            }

            $chiffre = 2;

            foreach ($cfs as $cf) {
                /** @var ContractFan $cf */
                $purchaseNbr = count($cf->getPurchases());
                $chiffreMax = $chiffre + ($purchaseNbr - 1);
                $phpExcelObject->getActiveSheet()->mergeCells('A'.$chiffre.':A'.$chiffreMax);
                $phpExcelObject->getActiveSheet()->mergeCells('B'.$chiffre.':B'.$chiffreMax);
                $phpExcelObject->getActiveSheet()->mergeCells('C'.$chiffre.':C'.$chiffreMax);

                $phpExcelObject->getActiveSheet()->setCellValue('A' . $chiffre, $cf->getId());
                $phpExcelObject->getActiveSheet()->setCellValue('B' . $chiffre, \PHPExcel_Shared_Date::PHPToExcel($cf->getDate()->getTimeStamp()));
                $phpExcelObject->getActiveSheet()->setCellValue('C' . $chiffre, $cf->getPayment()->getChargeId());

                $phpExcelObject->getActiveSheet()
                    ->getStyle('B' . $chiffre)
                    ->getNumberFormat()
                    ->setFormatCode(\PHPExcel_Style_NumberFormat::FORMAT_DATE_DATETIME);

                foreach($cf->getPurchases() as $purchase) {
                    /**
                     * @var Purchase $purchase
                     */
                    $phpExcelObject->getActiveSheet()->setCellValue('D' . $chiffre, $purchase->getCounterPart()->__toString());
                    $phpExcelObject->getActiveSheet()->setCellValue('E' . $chiffre, $purchase->getUnitaryPrice());
                    $phpExcelObject->getActiveSheet()->setCellValue('F' . $chiffre, $purchase->getAmount());
                    $phpExcelObject->getActiveSheet()->setCellValue('G' . $chiffre, $purchase->getQuantityOrganic());
                    $phpExcelObject->getActiveSheet()->setCellValue('H' . $chiffre, $purchase->getQuantityPromotional());
                    $phpExcelObject->getActiveSheet()->setCellValue('I' . $chiffre, $purchase->getQuantity());

                    $chiffre++;
                }
            }
            if($chiffre > 2) {
                $phpExcelObject->getActiveSheet()
                    ->setCellValue(
                        'E'.$chiffre,
                        'TOTAL'
                    );
                $phpExcelObject->getActiveSheet()
                    ->setCellValue(
                        'F'.$chiffre,
                        '=SUM(F2:F'.$chiffreMax.')'
                    );
                $phpExcelObject->getActiveSheet()
                    ->setCellValue(
                        'G'.$chiffre,
                        '=SUM(G2:G'.$chiffreMax.')'
                    );
                $phpExcelObject->getActiveSheet()
                    ->setCellValue(
                        'H'.$chiffre,
                        '=SUM(H2:H'.$chiffreMax.')'
                    );
                $phpExcelObject->getActiveSheet()
                    ->setCellValue(
                        'I'.$chiffre,
                        '=SUM(I2:I'.$chiffreMax.')'
                    );
            }

            $phpExcelObject->getActiveSheet()->getColumnDimension('B')->setWidth(20);
            $phpExcelObject->getActiveSheet()->getColumnDimension('C')->setWidth(40);
            $phpExcelObject->getActiveSheet()->getColumnDimension('D')->setWidth(40);

            $styleArray = array(
                'borders' => array(
                    'outline' => array(
                        'style' => \PHPExcel_Style_Border::BORDER_THIN,
                        'color' => array('argb' => '000000'),
                    ),
                ),
                'fill' => array(
                    'type' => \PHPExcel_Style_Fill::FILL_SOLID,
                    'color' => array('rgb' => 'fbd060')
                )

            );

            $phpExcelObject->getActiveSheet()->getStyle('A1:I1')->applyFromArray($styleArray);

        }
        // create the writer
        $writer = $this->get('phpexcel')->createWriter($phpExcelObject, 'Excel5');
        // create the response
        $response = $this->get('phpexcel')->createStreamedResponse($writer);
        // adding headers
        $dispositionHeader = $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $strHelper->slugify($contract->getTitle()) . '.xls'
        );
        $response->headers->set('Content-Type', 'text/vnd.ms-excel; charset=utf-8');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Cache-Control', 'maxage=1');
        $response->headers->set('Content-Disposition', $dispositionHeader);

        return $response;
    }
}