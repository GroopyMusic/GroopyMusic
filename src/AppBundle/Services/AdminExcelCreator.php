<?php

namespace AppBundle\Services;

use AppBundle\Entity\Ticket;
use AppBundle\Entity\YB\YBContractArtist;
use PHPExcel_Writer_Excel5;

class AdminExcelCreator
{
    const NUMBERS_IN_LETTER = 26;

    /**
     * @var \PHPExcel internalPHPExcelObject
     */
    private $internalPHPExcelObject;

    private $currentExcelRow;
    /**
     * AdminExcelCreator constructor.
     * @param \PHPExcel $excelObject
     * @throws \PHPExcel_Exception
     */
    public function __construct(\PHPExcel $excelObject)
    {
        $excelObject->getProperties()->setCreator("Ticked-it.be")
            ->setLastModifiedBy("Ticked-it robot")
            ->setTitle("Tickets")
            ->setSubject("Tickets")
            ->setDescription("Tickets")
            ->setKeywords("tickets, commissions");
        $excelObject->setActiveSheetIndex(0)
            ->setCellValue('A1', 'Hello')
            ->setCellValue('B2', 'world!');
        $excelObject->getActiveSheet()->setTitle('Tickets');

        $this->internalPHPExcelObject = $excelObject;
        $this->currentExcelRow = 1;
    }

    public function renderExcel(){
        return $this->internalPHPExcelObject;
    }

    /**
     * @param YBContractArtist $contract
     * @throws \PHPExcel_Exception
     */
    public function addContract($contract){
        $this->internalPHPExcelObject->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow(0, $this->currentExcelRow, "Ticket")
            ->setCellValueByColumnAndRow(1, $this->currentExcelRow, "Prix unitaire")
            ->setCellValueByColumnAndRow(2, $this->currentExcelRow, "Commission");
        $this->currentExcelRow++;
        foreach($contract->getContractsFanPaid() as $ticket){
            $this->addTicket($ticket);
        }
        $this->currentExcelRow++; //Empty line between 2 campaigns
        //$campaign->
    }

    /**
     * @param Ticket $ticket
     * @throws \PHPExcel_Exception
     */
    public function addTicket($ticket){
        $this->internalPHPExcelObject->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow(0, $this->currentExcelRow, $ticket->getName())
            ->setCellValueByColumnAndRow(1, $this->currentExcelRow, $ticket->getPrice())
            ->setCellValueByColumnAndRow(2, $this->currentExcelRow, 0);
        $this->currentExcelRow++;
    }
    /**
     * @param int $number
     * @return string
     */
//    private function numToString($number){
//        $str = "";
//        while ($number > 0){
//            $modNum = $number % self::NUMBERS_IN_LETTER;
//            /** @noinspection PhpWrongStringConcatenationInspection */
//            $modChar = 'A' + $modNum; //Note: the + here is intentional
//            $str .= $modChar;
//            $number = ($number - $modNum) / self::NUMBERS_IN_LETTER;
//        }
//        return $str; //TODO: find function to flip $str for cases where it is more than 1 char long
//    }

    /*private function writeToCell($x, $y, $content){
        $this->internalPHPExcelObject->setActiveSheetIndex(0)
            ->setCellValueByColumnAndRow($x, $y, $content);
    }*/
}