<?php

namespace AppBundle\Entity\YB;

use AppBundle\Entity\ContractFan;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Knp\DoctrineBehaviors\Model as ORMBehaviors;

/**
 * ContractArtist
 *
 * @ORM\Table(name="yb_invoice")
 * @ORM\Entity
 */
class YBInvoice
{
    use ORMBehaviors\SoftDeletable\SoftDeletable {
        delete as protected softdelete;
    }

    public function __construct()
    {
        $this->date_generated = new \DateTime();
        $this->user_validated = false;
        $this->contracts_fan = new ArrayCollection();
    }

    public function delete()
    {
        if(!$this->isDeleted()) {
            $this->softdelete();
            foreach($this->getContractsFan() as $cf) {
                $this->removeContractFan($cf);
            }
        }
    }

    private $purchases = [];
    public function getPurchases() {
        if(count($this->purchases) == 0) {
            $purchases = [];
            foreach($this->contracts_fan as $cf) {
                foreach($cf->getPurchases() as $purchase)
                $purchases[] = $purchase;
            }
            $this->purchases = $purchases;
        }
        return $this->purchases;
    }

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var YBContractArtist
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\YB\YBContractArtist", inversedBy="invoices")
     */
    private $campaign;

    /**
     * @var bool
     * @ORM\Column(name="user_validated", type="boolean")
     */
    private $user_validated;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_generated", type="datetime")
     */
    private $date_generated;

    /**
     * @var \DateTime
     * @ORM\Column(name="date_validated", type="datetime", nullable=true)
     */
    private $date_validated;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="AppBundle\Entity\ContractFan", cascade={"all"}, mappedBy="invoice")
     */
    private $contracts_fan;

    /**
     * @var int|null
     * @ORM\Column(name="sequence_number", type="integer", nullable=true)
     */
    private $sequence_number;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return YBContractArtist
     */
    public function getCampaign()
    {
        return $this->campaign;
    }

    /**
     * @var YBContractArtist $campaign
     */
    public function setCampaign($campaign){
        $this->campaign = $campaign;
    }

    /**
     * @return bool
     */
    public function isUserValidated()
    {
        return $this->user_validated;
    }

    public function validate(){
        //Note: the Sequence Number must be generated BEFORE the invoice gets set as validated
        $this->generateSequenceNumber();
        $this->user_validated = true;
        $this->date_validated = new \DateTime();
        return $this;
    }

    /**
     * @return \DateTime
     */
    public function getDateGenerated()
    {
        return $this->date_generated;
    }

    /**
     * @return \DateTime
     */
    public function getDateValidated()
    {
        return $this->date_validated;
    }

    /**
     * @return ArrayCollection
     */
    public function getContractsFan(){
        return $this->contracts_fan;
    }

    public function addContractFan(ContractFan $contract_fan) {
        $this->contracts_fan->add($contract_fan);
        $contract_fan->setInvoice($this);
    }

    public function removeContractFan(ContractFan $contract_fan) {
        if($this->contracts_fan->contains($contract_fan)) {
            $this->contracts_fan->remove($contract_fan);
            $contract_fan->setInvoice(null);
        }

    }

    public function getSequenceNumber(){
        return $this->sequence_number;
    }

    /**
     * Generate a sequence number to be used in invoice identifiers
     * IMPORTANT: call this function BEFORE setting the Invoice as user validated
     * This is because the function also checks the state of the current invoice
     */
    private function generateSequenceNumber(){
        $invoices = $this->campaign->getInvoices();
        $count = 0;
        foreach ($invoices as $invoice){
            if ($invoice->user_validated){
                $count++;
            }
        }
        $this->sequence_number = $count+1;
    }

    public function getInvoiceIdentifier(){
        if (!$this->user_validated){
            return null;
        }
        $invoiceYear = $this->date_validated->format("Y");
        $clientId = $this->campaign->getId();
        $paddedSeqNumber = str_pad("".$this->sequence_number, 5, "0", STR_PAD_LEFT);
        return "$invoiceYear-$clientId-$paddedSeqNumber";
    }
}
