<?php

namespace XBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
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
        $this->dateCreation = new \DateTime();
        $this->confirmed = false;
        $this->paid = false;
        $this->finalized = false;
        $this->contracts = new \Doctrine\Common\Collections\ArrayCollection();
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

    // Unmapped
    private $amount = null;
    public function getAmount()  {
        if($this->amount == null) {
            $this->amount = array_sum(array_map(function($contract) {
                /** @var XContractFan $contract */
                return $contract->getAmount();
            }, $this->contracts->toArray()));
        }
        return $this->amount;
    }

    public function isRefunded() {
        return $this->payment == null ? false : $this->payment->getRefunded();
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
    private $dateCreation;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="XBundle\Entity\XContractFan", mappedBy="cart", cascade={"all"})
     */
    private $contracts;

    /**
     * @var XOrder
     * @ORM\OneToOne(targetEntity="XBundle\Entity\XOrder", mappedBy="cart")
     */
    private $order;

    /**
     * @var XPayment
     * @ORM\OneToOne(targetEntity="XBundle\Entity\XPayment", mappedBy="cart")
     */
    private $payment;

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
        $this->dateCreation = $dateCreation;

        return $this;
    }

    /**
     * Get dateCreation
     *
     * @return \DateTime
     */
    public function getDateCreation()
    {
        return $this->dateCreation;
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
     * Add contract
     *
     * @param \XBundle\Entity\XContractFan $contract
     *
     * @return XCart
     */
    public function addContract(\XBundle\Entity\XContractFan $contract)
    {
        $this->contracts[] = $contract;

        return $this;
    }

    /**
     * Remove contract
     *
     * @param \XBundle\Entity\XContractFan $contract
     */
    public function removeContract(\XBundle\Entity\XContractFan $contract)
    {
        $this->contracts->removeElement($contract);
    }

    /**
     * Get contracts
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getContracts()
    {
        return $this->contracts;
    }

    /**
     * Set order
     *
     * @param \XBundle\Entity\XOrder $order
     *
     * @return XCart
     */
    public function setOrder(\XBundle\Entity\XOrder $order = null)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Get order
     *
     * @return \XBundle\Entity\XOrder
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Set payment
     *
     * @param \XBundle\Entity\XPayment $payment
     *
     * @return XCart
     */
    public function setPayment(\XBundle\Entity\XPayment $payment = null)
    {
        $this->payment = $payment;

        return $this;
    }

    /**
     * Get payment
     *
     * @return \XBundle\Entity\XPayment
     */
    public function getPayment()
    {
        return $this->payment;
    }
}
