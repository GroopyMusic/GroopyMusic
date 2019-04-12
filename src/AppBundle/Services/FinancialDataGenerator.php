<?php

namespace AppBundle\Services;

use AppBundle\Entity\ContractFan;
use AppBundle\Entity\Purchase;
use AppBundle\Entity\Ticket;
use AppBundle\Entity\YB\YBCommission;
use AppBundle\Entity\YB\YBContractArtist;
use AppBundle\Entity\YB\YBInvoice;

class FinancialDataGenerator{
    const STRIPE_FIXED_AMOUNT = 0.25;
    const STRIPE_PERCENTAGE = 1.4/100;
    /** @var $campaign YBContractArtist */
    private $campaign;
    /** @var $ticketData array */
    private $ticketData;

    public function __construct(YBContractArtist $campaign)
    {
        $this->campaign = $campaign;
        $this->ticketData = array();
        //$this->buildCampaignData();
    }

    public function getTicketData()
    {
        return $this->ticketData;
    }

    public function buildInvoicelessCampaignData(){
        $cfs = array_reverse($this->campaign->getContractsFanPaid());
        $this->ticketData = array();

        foreach($cfs as $cf) {
            /** @var ContractFan $cf */

            $purchases = $cf->getPurchases();
            /** @var Purchase $purchase */
            foreach ($purchases as $purchase){
                if ($purchase->getInvoice() == null){
                    $this->processPurchase($purchase);
                }

            }

        }
    }

    /**
     * @var $invoice YBInvoice
     */
    public function buildFromInvoice($invoice){
        $purchases = $invoice->getPurchases();
        $this->ticketData = array();
        foreach ($purchases as $purchase){
            $this->processPurchase($purchase);
        }
        //Sort by ticket type
        ksort($this->ticketData);
    }

    /**
     * @param $purchase Purchase
     */
    private function processPurchase($purchase){
        $qty = $purchase->getQuantity();

        $counterPart = $purchase->getCounterpart();
        $ticketRow = $counterPart->getId();
        if ($purchase->getFreePriceValue() != null){
            $ticketRow = "{$ticketRow}-{$purchase->getFreePriceValue()}";
        }
        /* if common ticket data is not yet built, based on counterpart IDs.
        This needs to be done only once per ticket type */
        if (!isset($this->ticketData[$ticketRow])){
            $this->ticketData[$ticketRow] = $this->dataFromPurchase($purchase);
        }

        /* Add ticket quantity to total */
        $this->ticketData[$ticketRow]['qty'] += $qty;
    }

    /**
     * Builds an array containing the informations needed for the invoice
     * @param $purchase Purchase
     * @return array
     *
     */
    private function dataFromPurchase($purchase){
        $purchaseUnitPrice = $purchase->getUnitaryPrice();

        /* IMPORTANT */
        $purchaseUnitPriceNoVAT = $this->calculateNoVATPrice($purchaseUnitPrice);
        $purchaseUnitPriceNoCom = $this->calculateNoCommissionPrice($purchaseUnitPriceNoVAT);
        $commissionValue = $purchaseUnitPriceNoVAT - $purchaseUnitPriceNoCom;
        $commissionRaw = 0.79 * $commissionValue;
        $commissionVAT = $commissionValue - $commissionRaw;

        $counterPart = "" . $purchase->getCounterpart();
        if ($purchase->getFreePriceValue() != null){
            $counterPart .= " ({$purchase->getFreePriceValue()} €)";
        }

        return array(
            'unitPrice' => $purchaseUnitPrice,
            'unitPriceRaw' => $purchaseUnitPriceNoVAT,
            'unitPriceNoCom' => $purchaseUnitPriceNoCom,
            'commission' => $commissionValue,
            'commissionRaw' => $commissionRaw,
            'commissionVAT' => $commissionVAT,
            'name' => $counterPart,
            'qty' => 0
        );
    }

    /**
     * Loop through all commissions to find and keep the relevant one
     *
     * Conditions for relevance:
     * Unit price is above the commission threshold
     * Commission threshold is the highest valid value found
     *     If 2 commission thresholds are equal, the last one is kept
     *
     * If no commission bracket can be found, null is returned instead
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

    /**
     * Removes the VAT from the given price, based on the campaign's provided VAT
     * @param $price float|double
     * @return float|double
     */
    private function calculateNoVATPrice($price){
        return $price/(1+$this->campaign->getVat());
    }

    /**
     * Removes the commission value from the given price, based on the campaign's commission brackets
     * Note: This method is separate from the VAT calculation,
     * if you want to remove the commission on the no-VAT price, please use calculateNoVATPrice first
     * @param $price float|double
     * @param $commission YBCommission Optional: force a specific commission bracket to be used
     * @return float|double
     */
    private function calculateNoCommissionPrice($price, $commission = null){
        if ($commission == null){
            $commission = $this->getRelevantCommission($price);
        }
        return $price/(1+$commission->getPercentageAmount())
            - $commission->getFixedAmount();
    }

    private function calculateStripeCommission($price){
        return $price * self::STRIPE_PERCENTAGE + self::STRIPE_FIXED_AMOUNT;
    }
}