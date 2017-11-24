<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ContractFan
 *
 * @ORM\Table(name="contract_fan")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ContractFanRepository")
 */
class ContractFan
{
    const ORDERS_DIRECTORY = 'pdf/orders/';

    public function __toString()
    {
        return 'contrat fan #' . $this->id;
    }

    public function __construct(ContractArtist $ca)
    {
        $this->contractArtist = $ca;
        $this->purchases = new \Doctrine\Common\Collections\ArrayCollection();

        foreach($ca->getStep()->getCounterParts() as $cp) {
            $purchase = new Purchase();
            $purchase->setCounterpart($cp);
            $this->addPurchase($purchase);
        }

        $this->ticket_sent = false;
        $this->date = new \DateTime();
        $this->refunded = false;
    }

    public function generateBarCode() {
        $this->barcode_text = 'cf'.$this->id . uniqid();
    }

    public function getOrderFileName() {
        return $this->getBarcodeText() . '.pdf';
    }

    public function getPdfPath() {
        return self::ORDERS_DIRECTORY . $this->getOrderFileName();
    }

    public function getAmount() {
        return array_sum(array_map(function(Purchase $purchase) {
            return $purchase->getAmount();
        }, $this->purchases->toArray()));
    }

    public function getPaid() {
        return $this->cart->getPaid();
    }

    public function getCounterPartsQuantity() {
        return array_sum(array_map(function(Purchase $purchase) {
            return $purchase->getQuantity();
        }, $this->purchases->toArray()));
    }

    public function getUser() {
        return $this->getCart()->getUser();
    }

    public function getFan() {
        return $this->getUser();
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
     * @var Cart
     *
     * @ORM\ManyToOne(targetEntity="Cart", inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cart;

    /**
     * @ORM\ManyToOne(targetEntity="BaseContractArtist", inversedBy="contractsFan")
     * @ORM\JoinColumn(nullable=false)
     */
    private $contractArtist;

    /**
     * @ORM\OneToMany(targetEntity="Purchase", mappedBy="contractFan", cascade={"all"})
     */
    private $purchases;

    /**
     * @ORM\Column(name="ticket_sent", type="boolean")
     */
    private $ticket_sent;

    /**
     * @ORM\Column(name="barcode_text", type="string", length=255, nullable=true)
     */
    private $barcode_text;

    /**
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;

    /**
     * @ORM\OneToOne(targetEntity="Payment", mappedBy="contractFan")
     */
    private $payment;

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set contractArtist
     *
     * @param \AppBundle\Entity\ContractArtist $contractArtist
     *
     * @return ContractFan
     */
    public function setContractArtist(\AppBundle\Entity\ContractArtist $contractArtist)
    {
        $this->contractArtist = $contractArtist;

        return $this;
    }

    /**
     * Get contractArtist
     *
     * @return \AppBundle\Entity\ContractArtist
     */
    public function getContractArtist()
    {
        return $this->contractArtist;
    }

    /**
     * Add purchase
     *
     * @param \AppBundle\Entity\Purchase $purchase
     *
     * @return ContractFan
     */
    public function addPurchase(\AppBundle\Entity\Purchase $purchase)
    {
        $this->purchases[] = $purchase;
        $purchase->setContractFan($this);

        return $this;
    }

    /**
     * Remove purchase
     *
     * @param \AppBundle\Entity\Purchase $purchase
     */
    public function removePurchase(\AppBundle\Entity\Purchase $purchase)
    {
        $this->purchases->removeElement($purchase);
    }

    /**
     * Get purchases
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getPurchases()
    {
        return $this->purchases;
    }

    /**
     * Set cart
     *
     * @param \AppBundle\Entity\Cart $cart
     *
     * @return ContractFan
     */
    public function setCart(\AppBundle\Entity\Cart $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return \AppBundle\Entity\Cart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set ticketSent
     *
     * @param boolean $ticketSent
     *
     * @return ContractFan
     */
    public function setTicketSent($ticketSent)
    {
        $this->ticket_sent = $ticketSent;

        return $this;
    }

    /**
     * Get ticketSent
     *
     * @return boolean
     */
    public function getTicketSent()
    {
        return $this->ticket_sent;
    }

    /**
     * Set barcodeText
     *
     * @param string $barcodeText
     *
     * @return ContractFan
     */
    public function setBarcodeText($barcodeText)
    {
        $this->barcode_text = $barcodeText;

        return $this;
    }

    /**
     * Get barcodeText
     *
     * @return string
     */
    public function getBarcodeText()
    {
        return $this->barcode_text;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return ContractFan
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     *
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set refunded
     *
     * @param boolean $refunded
     *
     * @return ContractFan
     */
    public function setRefunded($refunded)
    {
        $this->refunded = $refunded;

        return $this;
    }

    /**
     * Get refunded
     *
     * @return boolean
     */
    public function getRefunded()
    {
        return $this->refunded;
    }
}
