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

    public function __construct(Project $p)
    {
        $this->project = $p;
        $this->purchases = new ArrayCollection();

        foreach($p->getProducts() as $product) {
            if($product->getValidated()) {
                $purchase = new XPurchase();
                $purchase->setProduct($product);
                $this->addPurchase($purchase);
            }
        }

        $this->amount = 0;
        $this->date = new \DateTime();
        $this->refunded = false;
    }

    public function __toString()
    {
        return 'x_contract_fan';
    }

    public function initAmount() {
        $this->amount = array_sum(array_map(function(XPurchase $purchase) {
            return $purchase->getAmount();
        }, $this->purchases->toArray()));
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
}
