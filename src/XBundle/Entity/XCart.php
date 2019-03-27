<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use XBundle\Entity\Product;
use XBundle\Entity\Project;
use XBundle\Entity\XOrder;
use XBundle\Entity\XPayment;

/**
 * XCart
 *
 * @ORM\Table(name="x_cart")
 * @ORM\Entity(repositoryClass="XBundle\Repository\XCartRepository")
 */
class XCart
{

    public function __construct()
    {
        $this->date_creation = new \DateTime();
        $this->confirmed = false;
        $this->paid = false;
        $this->finalized = false;
    }

    public function generateBarCode()
    {
        if (empty($this->barcode_text)) {
            $str = 'x';
            $str .= 'c' . $this->id . uniqid();
            $this->barcode_text = $str;
        }
        return $this->barcode_text;
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
     * @var \DateTime
     * @ORM\Column(name="date_creation", type="datetime")
     */
    private $date_creation;

    /**
     * @var XOrder
     * @ORM\OneToOne(targetEntity="XBundle\Entity\XOrder")
     */
    //private $xOrder;

    /**
     * @var XPayment
     * @ORM\OneToOne(targetEntity="XBundle\Entity\XPayment")
     */
    //private $xPayment;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Project", cascade={"persist"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $project;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\Product", cascade={"persist"})
     */
    private $product;

    /**
     * @var float
     *
     * @ORM\Column(name="donation_amount", type="float", nullable=true)
     */
    private $donationAmount;

    /**
     * @var integer
     *
     * @ORM\Column(name="prod_quantity", type="integer", nullable=true)
     */
    private $prodQuantity;

    /**
     * @var bool
     *
     * @ORM\Column(name="confirmed", type="boolean")
     */
    private $confirmed;

    /**
     * @var bool
     *
     * @ORM\Column(name="paid", type="boolean")
     */
    private $paid;

    /**
     * @var bool
     * 
     * @ORM\Column(name="finalized", type="boolean")
     */
    private $finalized;

    /**
     * @var string
     * @ORM\Column(name="barcode_text", type="string", length=255, nullable=true)
     */
    private $barcode_text;


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
     * Set dateCreation
     *
     * @param \DateTime $dateCreation
     *
     * @return Cart
     */
    public function setDateCreation($dateCreation)
    {
        $this->date_creation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->date_creation;
    }

    /**
     * Set confirmed
     *
     * @param boolean $confirmed
     *
     * @return XCart
     */
    public function setConfirmed($confirmed)
    {
        $this->confirmed = $confirmed;

        return $this;
    }

    /**
     * Get confirmed
     *
     * @return bool
     */
    public function getConfirmed()
    {
        return $this->confirmed;
    }

    /**
     * Set paid
     *
     * @param boolean $paid
     *
     * @return XCart
     */
    public function setPaid($paid)
    {
        $this->paid = $paid;

        return $this;
    }

    /**
     * Get paid
     *
     * @return bool
     */
    public function getPaid()
    {
        return $this->paid;
    }

    /**
     * Set finalized
     *
     * @param boolean $finalized
     *
     * @return XCart
     */
    public function setFinalized($finalized)
    {
        $this->finalized = $finalized;

        return $this;
    }

    /**
     * Get finalized
     *
     * @return boolean
     */
    public function getFinalized()
    {
        return $this->finalized;
    }

    /**
     * Set donationAmount
     *
     * @param float $donationAmount
     *
     * @return XCart
     */
    public function setDonationAmount($donationAmount)
    {
        $this->donationAmount = $donationAmount;

        return $this;
    }

    /**
     * Get donationAmount
     *
     * @return float
     */
    public function getDonationAmount()
    {
        return $this->donationAmount;
    }

    /**
     * Set xOrder
     *
     * @param $xOrder
     *
     * @return XCart
     */
    /*public function setXOrder($xOrder = null)
    {
        $this->xOrder = $xOrder;

        return $this;
    }*/

    /**
     * Get xOrder
     *
     * @return XOrder
     */
    /*public function getXOrder()
    {
        return $this->xOrder;
    }*/


    /**
     * Set xPayment
     *
     * @param $xPayment
     *
     * @return XCart
     */
    /*public function setXPayment($xPayment = null)
    {
        $this->xPayment = $xPayment;

        return $this;
    }*/

    /**
     * Get xPayment
     *
     * @return XPayment
     */
    /*public function getXPayment()
    {
        return $this->xPayment;
    }*/


    /**
     * Set barcodeText
     *
     * @param string $barcodeText
     *
     * @return XCart
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
     * Set prodQuantity
     *
     * @param integer $prodQuantity
     *
     * @return XCart
     */
    public function setProdQuantity($prodQuantity)
    {
        $this->prodQuantity = $prodQuantity;

        return $this;
    }

    /**
     * Get prodQuantity
     *
     * @return integer
     */
    public function getProdQuantity()
    {
        return $this->prodQuantity;
    }



    /**
     * Set product
     *
     * @param Product $product
     *
     * @return XCart
     */
    public function setProduct($product = null)
    {
        $this->product = $product;

        return $this;
    }

    /**
     * Get product
     *
     * @return Product
     */
    public function getProduct()
    {
        return $this->product;
    }


    /**
     * Set project
     *
     * @param Project $project
     *
     * @return XCart
     */
    public function setProject(Project $project)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return Project
     */
    public function getProject()
    {
        return $this->project;
    }
}
