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
        $str = 'Paiement de ' . $this->getUser() . ' de ' . $this->getAmount() . ' € pour l\'événement "' . $this->getContractArtist() . '" (' . $this->contractFan . ')';
        if($this->refunded) {
            $str .= ' - REMBOURSE';
        }
        return $str;
    }

    public function __construct() {
        $this->asking_refund = new ArrayCollection();
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
        return $this->contractFan->getCounterPartsQuantity();
    }

    public function getCounterPartsQuantityOrganic() {
        return $this->contractFan->getCounterPartsQuantityOrganic();
    }

    public function getCounterPartsQuantityPromotional() {
        return $this->contractFan->getCounterPartsQuantityPromotional();
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
     */
    private $user;

    /**
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;

    /**
     * @var ContractFan
     * @ORM\OneToOne(targetEntity="ContractFan", inversedBy="payment", cascade={"persist"})
     */
    private $contractFan;

    /**
     * @var ContractArtist
     * @ORM\ManyToOne(targetEntity="ContractArtist", inversedBy="payments")
     */
    private $contractArtist;

    /**
     * @ORM\Column(name="amount", type="decimal")
     */
    private $amount;

    /**
     * @var ArrayCollection
     * @ORM\ManyToMany(targetEntity="User")
     */
    private $asking_refund;

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

        $this->getContractFan()->setRefunded($refunded);

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
     * Set amount
     *
     * @param string $amount
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
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set contractArtist
     *
     * @param \AppBundle\Entity\ContractArtist $contractArtist
     *
     * @return Payment
     */
    public function setContractArtist(\AppBundle\Entity\ContractArtist $contractArtist = null)
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
}
