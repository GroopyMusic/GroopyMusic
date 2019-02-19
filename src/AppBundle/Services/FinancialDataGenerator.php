<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\YB\YBCommission;
use AppBundle\Entity\YB\YBContractArtist;

class FinancialDataGenerator{
    /** @var $campaign YBContractArtist */
    private $campaign;
    /** @var $ticketData array */
    private $ticketData;
    /** @var $ticketList array */
    private $ticketList;

    public function __construct(YBContractArtist $campaign)
    {
        $this->campaign = $campaign;
        $this->ticketData = array();
        $this->ticketList = array();
        $this->buildCampaignData();
    }

    public function getTicketData()
    {
        return $this->ticketData;
    }

    public function getTicketList(){
        return $this->ticketList;
    }

    private function buildCampaignData(){
        $cfs = array_reverse($this->campaign->getContractsFanPaid());

        //if($this->campaign->getTicketsSent()) {

            foreach($cfs as $cf) {
                /** @var ContractFan $cf */

                $purchases = $cf->getPurchases();
                /** @var Purchase $purchase */
                foreach ($purchases as $purchase){
                    $this->processPurchase($purchase);
                }
                foreach ($cf->getTickets() as $ticket) {

                    /** @var Ticket $ticket */
                    $this->ticketList[] = array(
                        $ticket->getBarcodeText(),
                        $ticket->getContractFan()->getId(),
                        $ticket->getContractFan()->getCart()->getBarcodeText(),
                        $ticket->getName(),
                        $ticket->getPrice(),
                        $ticket->getCounterPart()->__toString(),
                    );

                }

            }
        //}
    }

    /**
     * @param $purchase Purchase
     */
    private function processPurchase($purchase){
        $qty = $purchase->getQuantity();

        $counterPart = $purchase->getCounterpart();
        $counterPartId = $counterPart->getId();

        /* if data is not yet built */
        if (!isset($this->ticketData[$counterPartId])){
            $this->ticketData[$counterPartId] = $this->dataFromPurchase($purchase);
        }

        $this->ticketData[$counterPartId]['qty'] += $qty;
    }

    /**
     * @param $purchase Purchase
     * @return array
     *
     */
    private function dataFromPurchase($purchase){
        $purchaseUnitPrice = $purchase->getUnitaryPrice();
        $commission = $this->getRelevantCommission($purchaseUnitPrice);

        /* IMPORTANT */
        $purchaseUnitPriceNoVAT = $this->calculateNoVATPrice($purchaseUnitPrice);
        $purchaseUnitPriceNoCom = $this->calculateNoCommissionPrice($purchaseUnitPriceNoVAT, $commission);
        $commissionValue = $purchaseUnitPriceNoVAT - $purchaseUnitPriceNoCom;

        $counterPart = $purchase->getCounterpart();
        $counterPartId = $counterPart->getId();

        return array(
            'unitPrice' => $purchaseUnitPrice,
            'unitPriceRaw' => $purchaseUnitPriceNoVAT,
            'unitPriceNoCom' => $purchaseUnitPriceNoCom,
            'commission' => $commissionValue,
            'name' => $counterPart,
            'qty' => 0
        );
    }

    /**
     * Loop through all commissions to find and keep the relevant one
     *
     * Unit price is above the commission threshold
     * Commission threshold is the highest valid value found
     *     If 2 commission thresholds are equal, the last one is kept
     *
     * @param $price
     * @return YBCommission|null
     */
    private function getRelevantCommission($price){
        $commissions = $this->campaign->getCommissions();
        /** @var YBCommission $currentCom */
        $currentCom = null;
        /** @var YBCommission $com */
        foreach ($commissions as $com){
            $comThreshold = $com->getMinimumThreshold();
            if ($price >= $comThreshold
                && ($currentCom == null || $comThreshold >= $currentCom->getMinimumThreshold())){
                $currentCom = $com;
            }
        }

        return $currentCom;
    }

    private function calculateNoVATPrice($price){
        return $price/(1+$this->campaign->getVat());
    }

    /**
     * @param $price
     * @param $commission YBCommission
     */
    private function calculateNoCommissionPrice($price, $commission){
        return $price/(1+$commission->getPercentageAmount())
            - $commission->getFixedAmount();
    }
}