<?php

namespace XBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use XBundle\Entity\XCart;

/**
 * XPayment
 *
 * @ORM\Table(name="x_payment")
 * @ORM\Entity(repositoryClass="XBundle\Repository\XPaymentRepository")
 */
class XPayment
{

    public function __toString()
    {
        return 'x_paiment';
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
     *@var XCart
     * @ORM\OneToOne(targetEntity="XBundle\Entity\XCart", inversedBy="payment", cascade={"persist"})
     * @ORM\JoinColumn(nullable=true)
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
     * @ORM\Column(name="refunded", type="boolean")
     */
    private $refunded;

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
     * Set refunded
     *
     * @param boolean $refunded
     *
     * @return XPayment
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
     * @param $cart
     *
     * @return XPayment
     */
    public function setCart($cart = null)
    {
        $this->cart = $cart;

        return $this;
    }

    /**
     * Get cart
     *
     * @return XCart
     */
    public function getCart()
    {
        return $this->cart;
    }
}
