<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

use AppBundle\Entity\ContractFan;
use AppBundle\Entity\ContractArtist;

/**
 * Payment
 *
 * @ORM\Table(name="payment")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PaymentRepository")
 */
class Payment
{
    const VOTES_TO_REFUND = 2;

    public function __toString()
    {
        $str = 'Paiement de ' . $this->getUser() . ' de ' . $this->getAmount();
        if($this->refunded) {
            $str .= ' - REMBOURSE';
        }
        return $str;
    }

    public function __construct() {
        $this->asking_refund = new ArrayCollection();
    }

    public function getContractArtists() {
        return join(',', array_unique(array_map(function(ContractFan $cf) {
            return $cf->getContractArtist();
        }, $this->getContractsFan())));
    }
    
    public function getDisplayName() {
        if($this->cart != null) {
            return $this->cart->getDisplayName();
        }
        else {
            return $this->contractFan->getDisplayName(); 
        }
    }

    public function getContractsFan() {
        if($this->cart != null) {
            return $this->cart->getContracts()->toArray();
        }
        return [$this->contractFan];
    }

    public function isRefundReady() {
        return count($this->asking_refund) >= self::VOTES_TO_REFUND;
    }

    public function isAskedRefundBy(User $user) {
        return $this->asking_refund->contains($user);
    }

    public function isAskedRefundByOne() {
        return count($this->asking_refund) >= 1;
    }

    public function isOneStepFromBeingRefunded() {
        return self::VOTES_TO_REFUND - count($this->asking_refund) == 1;
    }

    public function getCounterPartsQuantity() {
        return array_sum(array_map(function(ContractFan $contractFan) {
            return $contractFan->getCounterPartsQuantity();
        }, $this->getContractsFan()));
    }

    public function getCounterPartsQuantityOrganic() {
        return array_sum(array_map(function(ContractFan $contractFan) {
            return $contractFan->getCounterPartsQuantityOrganic();
        }, $this->getContractsFan()));
    }

    public function getCounterPartsQuantityPromotional() {
        return array_sum(array_map(function(ContractFan $contractFan) {
            return $contractFan->getCounterPartsQuantityPromotional();
        }, $this->getContractsFan()));
    }

    private $purchases = null;

    public function getPurchases() {
        if($this->purchases != null) {
            return $this->purchases;
        }
        $this->purchases = [];
        foreach($this->getContractsFan() as $cf) {
            foreach($cf->getPurchases() as $purchase) {
                $this->purchases[] = $purchase;
            }
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
     * @var \DateTime
     *
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="charge_id", type="string", length=255)
     */
    private $chargeId;

    /**
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", inversedBy="payments")
     * @ORM\JoinColumn(nullable=true)
     */
    private $user;

    /**
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;

    /**
     * @var ContractFan
     * @deprecated
     * @ORM\OneToOne(targetEntity="ContractFan", inversedBy="payment", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $contractFan;

    /**
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="User")
     */
    private $asking_refund;

    /**
     * @var Cart
     * @ORM\OneToOne(targetEntity="Cart", inversedBy="payment", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
     */
    private $cart;

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
     * Set date
     *
     * @param \DateTime $date
     *
     * @return Payment
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
     * Set chargeId
     *
     * @param string $chargeId
     *
     * @return Payment
     */
    public function setChargeId($chargeId)
    {
        $this->chargeId = $chargeId;

        return $this;
    }

    /**
     * Get chargeId
     *
     * @return string
     */
    public function getChargeId()
    {
        return $this->chargeId;
    }

    /**
     * Set user
     *
     * @param \AppBundle\Entity\User $user
     *
     * @return Payment
     */
    public function setUser(\AppBundle\Entity\User $user = null)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Get user
     *
     * @return \AppBundle\Entity\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Set refunded
     *
     * @param boolean $refunded
     *
     * @return Payment
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

    /**
     * Set contractFan
     *
     * @param \AppBundle\Entity\ContractFan $contractFan
     *
     * @return Payment
     */
    public function setContractFan(\AppBundle\Entity\ContractFan $contractFan = null)
    {
        $this->contractFan = $contractFan;

        return $this;
    }

    /**
     * Get contractFan
     *
     * @return \AppBundle\Entity\ContractFan
     */
    public function getContractFan()
    {
        return $this->contractFan;
    }

    /**
     * Add askingRefund
     *
     * @param \AppBundle\Entity\User $askingRefund
     *
     * @return Payment
     */
    public function addAskingRefund(\AppBundle\Entity\User $askingRefund)
    {
        $this->asking_refund[] = $askingRefund;

        return $this;
    }

    /**
     * Remove askingRefund
     *
     * @param \AppBundle\Entity\User $askingRefund
     */
    public function removeAskingRefund(\AppBundle\Entity\User $askingRefund)
    {
        $this->asking_refund->removeElement($askingRefund);
    }

    /**
     * Get askingRefund
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAskingRefund()
    {
        return $this->asking_refund;
    }

    /**
     * @param mixed $asking_refund
     */
    public function setAskingRefund($asking_refund)
    {
        $this->asking_refund = $asking_refund;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return Payment
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
     * Set cart
     *
     * @param \AppBundle\Entity\Cart $cart
     *
     * @return Payment
     */
    public function setCart(\AppBundle\Entity\Cart $cart = null)
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
}
