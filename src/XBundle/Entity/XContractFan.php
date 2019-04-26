<?php

namespace XBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use XBundle\Entity\XPurchase;

/**
 * XContractFan
 *
 * @ORM\Table(name="x_contract_fan")
 * @ORM\Entity(repositoryClass="XBundle\Repository\XContractFanRepository")
 */
class XContractFan
{

    const X_TICKETS_DIRECTORY = 'x/tickets/';

    public function __construct(Project $p)
    {
        $this->project = $p;
        $this->purchases = new ArrayCollection();

        foreach($p->getProducts() as $product) {
            if($product->getValidated() && $product->getDeletedAt() == null) {
                $purchase = new XPurchase();
                $purchase->setProduct($product);
                $this->addPurchase($purchase);
            }
        }

        $this->amount = 0;
        $this->date = new \DateTime();
        $this->refunded = false;
        $this->isDonation = false;
        $this->ticketsSent = false;
        $this->tickets = new ArrayCollection();
    }

    public function __toString()
    {
        $str = "";

        for ($i = 0; $i < $this->purchases->count(); $i++) {
            $str .= $this->purchases->get($i);
            if ($i < $this->purchases->count()-1) {
                $str .= "\n";
            }
        }

        return $str;
    }

    public function initAmount() {
        $this->amount = array_sum(array_map(function(XPurchase $purchase) {
            return $purchase->getAmount();
        }, $this->purchases->toArray()));
    }

    public function getPaid() {
        return $this->cart->getPaid();
    }

    /** @return PhysicalPersonInterface */
    public function getPhysicalPerson() {
        return $this->getCart()->getOrder();
    }

    public function getDisplayName() {
        if($this->getPhysicalPerson() == null) {
            return 'anonyme';
        }
        return $this->getPhysicalPerson()->getDisplayName();
    }

    public function getEmail() {
        if($this->getPhysicalPerson() == null) {
            return 'anonyme' ;
        }
        return $this->getPhysicalPerson()->getEmail();
    }

    public function getProductsQuantity()
    {
        return array_sum(array_map(function (XPurchase $purchase) {
            return $purchase->getQuantity();
        }, $this->purchases->toArray()));
    }

    public function getPayment() {
        return $this->cart->getPayment();
    }

    public function generateBarCode()
    {
        if (empty($this->barcodeText))
            $this->barcodeText = 'cf' . $this->id . uniqid();
    }

    public function getTicketsFileName()
    {
        return $this->getBarcodeText() . '-tickets.pdf';
    }

    public function getTicketsPath()
    {
        return self::X_TICKETS_DIRECTORY . $this->getTicketsFileName();
    }

    private $ticketsPurchases = null;
    public function getTicketsPurchases() {
        if($this->ticketsPurchases == null) {
            $this->ticketsPurchases = array_filter($this->purchases->toArray(), function(XPurchase $p) {
                return $p->getProduct()->isTicket();
            });
        }
        return $this->ticketsPurchases;
    }


    private $purchasesForProduct = null;
    public function getPurchasesForProduct(Product $product) {
        if($this->purchasesForProduct == null) {
            foreach ($this->purchases as $purchase) {
                if ($purchase->getProduct() == $product) {
                    $this->purchasesForProduct[] = $purchase;
                }
            }
        }
        return $this->purchasesForProduct;
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
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\XCart", inversedBy="contracts")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cart;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Project", inversedBy="contributions", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\OneToMany(targetEntity="XBundle\Entity\XPurchase", mappedBy="contractFan", cascade={"all"})
     */
    private $purchases;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var \DateTime
     * 
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var bool
     *
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;

    /**
     * @var string
     *
     * @ORM\Column(name="barcode_text", type="string", length=255,  nullable=true)
     */
    private $barcodeText;

    /**
     * @var boolean
     * 
     * @ORM\Column(name="is_donation", type="boolean")
     */
    private $isDonation;

    /**
     * @var bool
     *
     * @ORM\Column(name="tickets_sent", type="boolean")
     */
    private $ticketsSent;

    /**
     * @ORM\OneToMany(targetEntity="XBundle\Entity\XTicket", mappedBy="contractFan", cascade={"all"})
     */
    private $tickets;


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
     * Set amount
     *
     * @param float $amount
     *
     * @return XContractFan
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set date
     *
     * @param \DateTime $date
     *
     * @return XContractFan
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
     * @return XContractFan
     */
    public function setRefunded($refunded)
    {
        $this->refunded = $refunded;

        return $this;
    }

    /**
     * Get refunded
     *
     * @return bool
     */
    public function getRefunded()
    {
        return $this->refunded;
    }

    /**
     * Set barcodeText
     *
     * @param string $barcodeText
     *
     * @return XContractFan
     */
    public function setBarcodeText($barcodeText)
    {
        $this->barcodeText = $barcodeText;

        return $this;
    }

    /**
     * Get barcodeText
     *
     * @return string
     */
    public function getBarcodeText()
    {
        return $this->barcodeText;
    }

    /**
     * Set cart
     *
     * @param \XBundle\Entity\XCart $cart
     *
     * @return XContractFan
     */
    public function setCart(\XBundle\Entity\XCart $cart)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return \XBundle\Entity\XCart
     */
    public function getCart()
    {
        return $this->cart;
    }

    /**
     * Set project
     *
     * @param \XBundle\Entity\Project $project
     *
     * @return XContractFan
     */
    public function setProject(\XBundle\Entity\Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \XBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Add purchase
     *
     * @param \XBundle\Entity\XPurchase $purchase
     *
     * @return XContractFan
     */
    public function addPurchase(\XBundle\Entity\XPurchase $purchase)
    {
        $this->purchases[] = $purchase;

        return $this;
    }

    /**
     * Remove purchase
     *
     * @param \XBundle\Entity\XPurchase $purchase
     */
    public function removePurchase(\XBundle\Entity\XPurchase $purchase)
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
     * Set isDonation
     *
     * @param boolean $isDonation
     *
     * @return XContractFan
     */
    public function setIsDonation($isDonation)
    {
        $this->isDonation = $isDonation;

        return $this;
    }

    /**
     * Get isDonation
     *
     * @return boolean
     */
    public function getIsDonation()
    {
        return $this->isDonation;
    }

    /**
     * Add ticket
     *
     * @param \XBundle\Entity\XTicket $ticket
     *
     * @return XContractFan
     */
    public function addTicket(\XBundle\Entity\XTicket $ticket)
    {
        $this->tickets[] = $ticket;

        return $this;
    }

    /**
     * Remove ticket
     *
     * @param \XBundle\Entity\XTicket $ticket
     */
    public function removeTicket(\XBundle\Entity\XTicket $ticket)
    {
        $this->tickets->removeElement($ticket);
    }

    /**
     * Get tickets
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * Set ticketsSent
     *
     * @param boolean $ticketsSent
     *
     * @return XContractFan
     */
    public function setTicketsSent($ticketsSent)
    {
        $this->ticketsSent = $ticketsSent;

        return $this;
    }

    /**
     * Get ticketsSent
     *
     * @return boolean
     */
    public function getTicketsSent()
    {
        return $this->ticketsSent;
    }
}
