<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * XPayment
 *
 * @ORM\Table(name="x_payment")
 * @ORM\Entity(repositoryClass="XBundle\Repository\XPaymentRepository")
 */
class XPayment
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity="XBundle\Entity\XCart")
     * @ORM\JoinColumn(nullable=false)
     */
    private $cart;

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
     * @var bool
     *
     * @ORM\Column(name="refund", type="boolean")
     */
    private $refund;

    /**
     * @var float
     *
     * @ORM\Column(name="amount", type="float")
     */
    private $amount;


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
     * @return XPayment
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
     * @return XPayment
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
     * Set refund
     *
     * @param boolean $refund
     *
     * @return XPayment
     */
    public function setRefund($refund)
    {
        $this->refund = $refund;

        return $this;
    }

    /**
     * Get refund
     *
     * @return bool
     */
    public function getRefund()
    {
        return $this->refund;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return XPayment
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
     * @param \XBundle\Entity\XCart $cart
     *
     * @return XPayment
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
}
